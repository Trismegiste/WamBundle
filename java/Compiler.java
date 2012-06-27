/******************************************************************************
 * Warren's Abstract Machine  -  Implementation by Stefan Buettcher
 *
 * developed:   December 2001 until February 2002
 *
 * Compiler.java contains the base class Compiler, which both QueryCompiler and
 * PrologCompiler have been derived from. Additionally, it contains a class
 * KeyValue, which is used for implementing mappings from Prolog variable names
 * ("X", "A13", "Name", ...) to WAM variable names ("Y1", "Y2", ...).
 ******************************************************************************/

import java.util.*;

//class KeyValue helps implementing mappings "A=B" (key/value pairs)
class KeyValue {
  public String key;
  public String stringValue;
  public int intValue;

  // create a new pair with key k and String value v
  public KeyValue(String k, String v) {
    key = k;
    stringValue = v;
    intValue = -12345;
  } // end of KeyValue.KeyValue(String, String)

  // create a new pair with key k and int value v
  public KeyValue(String k, int v) {
    key = k;
    intValue = v;
    stringValue = "";
  } // end of KeyValue.KeyValue(String, int)

  // in order to display the mapping on the screen (for debug purposes only)
  public String toString() {
    if (stringValue.length() == 0)
      return "[" + key + "=" + intValue + "]";
    else
      return "[" + key + "=" + stringValue + "]";
  } // end of KeyValue.toString()

} // end of class KeyValue

public abstract class Compiler {
  WAM owner;
  String errorString;
  String varPrefix;
  Vector substitutionList;
  private String lastVar;
  private int bodyCalls;

  boolean isPredicate(String s) {
    return (isConstant(s) && (!isNumber(s)));
  } // end of Compiler.isPredicate()

  boolean isVariable(String s) {
    if (s.compareTo("_") == 0)
      return true;
    char c = s.charAt(0);
    if ((c >= 'A') && (c <= 'Z')) {
      for (int i = 1; i < s.length(); i++) {
        c = s.charAt(i);
        if (((c < 'a') || (c > 'z')) && ((c < 'A') || (c > 'Z')) && ((c < '0') || (c > '9')) && (c != '_')) {
          errorString = ("\"" + s + "\" is no valid variable.");
          return false;
        }
      }
      return true;
    }
    else
      return false;
  } // end of Compiler.isVariable()

  boolean isConstant(String s) {
    char c = s.charAt(0);
    if ((c >= 'a') && (c <= 'z')) {
      for (int i = 1; i < s.length(); i++) {
        c = s.charAt(i);
        if (((c < 'a') || (c > 'z')) && ((c < 'A') || (c > 'Z')) && ((c < '0') || (c > '9')) && (c != '_')) {
          errorString = ("\"" + s + "\" is no valid constant or predicate.");
          return false;
        }
      }
      return true;
    }
    if ((c == '\'') && (s.charAt(s.length() - 1) == '\''))
      return true;
    if ((s.compareTo(";") == 0) || (s.compareTo(".") == 0) || (s.compareTo("+") == 0) || (s.compareTo("#") == 0))
      return true;
    return isNumber(s);
  } // end of Compiler.isConstant(String)

  boolean isNumber(String s) {
    try {
      Float i = new Float(s);
      return true;
    } catch (Exception e) {
      return false;
    }
  } // end of Compiler.isNumber(String)

  boolean predicate(Vector prog, CompilerStructure struc) {
    if (prog.size() == 0) return false;
    String q0 = (String)prog.elementAt(0);
    if (isPredicate(q0)) {
      struc.type = struc.PREDICATE;
      struc.value = q0;
      prog.removeElementAt(0);
      return true;
    }
    return false;
  } // end of Compiler.predicate(Vector, CompilerStructure)

  boolean constant(Vector prog, CompilerStructure struc) {
    if (prog.size() == 0) return false;
    String q0 = (String)prog.elementAt(0);
    if (isConstant(q0)) {
      struc.type = struc.CONSTANT;
      if (q0.charAt(0) == '\'')
        struc.value = q0.substring(1, q0.length() - 1);
      else
        struc.value = q0;
      prog.removeElementAt(0);
      return true;
    }
    Vector oldProg = (Vector)prog.clone();
    if ((token(prog, "[")) && (token(prog, "]"))) {
      struc.type = struc.CONSTANT;
      struc.value = "[]";
      return true;
    }
    prog.clear();
    prog.addAll(oldProg);
    return false;
  } // end of Compiler.constant(Vector, CompilerStructure)

  boolean variable(Vector prog, CompilerStructure struc) {
    if (prog.size() == 0) return false;
    String q0 = (String)prog.elementAt(0);
    if (isVariable(q0)) {
      struc.type = struc.VARIABLE;
      struc.value = q0;
      prog.removeElementAt(0);
      return true;
    }
    return false;
  } // end of Compiler.variable(Vector, CompilerStructure)

  boolean structure(Vector prog, CompilerStructure struc) {
    if (prog.size() == 0) return false;
    Vector oldProg = (Vector)prog.clone();
    struc.head = new CompilerStructure();
    struc.tail = new CompilerStructure();
    struc.type = struc.STRUCTURE;
    if ((predicate(prog, struc.head)) && (token(prog, "(")) && (list(prog, struc.tail)) && (token(prog, ")"))) {
      struc.head.type = struc.CONSTANT;
      return true;
    }
    prog.clear();
    prog.addAll(oldProg);
    if ((variable(prog, struc.head)) && (token(prog, "(")) && (list(prog, struc.tail)) && (token(prog, ")")))
      return true;
    prog.clear();
    prog.addAll(oldProg);
    return false;
  } // end of Compiler.structure(Vector, CompilerStructure)

  boolean element(Vector prog, CompilerStructure struc) {
    if (prog.size() == 0) return false;
    Vector oldProg = (Vector)prog.clone();
    if (structure(prog, struc))
      return true;
    if (variable(prog, struc))
      return true;
    if (constant(prog, struc))
      return true;
    if ((token(prog, "[")) && (list(prog, struc)) && (token(prog, "]")))
      return true;
    prog.clear();
    prog.addAll(oldProg);
    return false;
  } // end of Compiler.element(Vector, CompilerStructure)

  boolean isNextToken(Vector prog, String tok) {
    if (prog.size() == 0) return false;
    if (tok.compareTo((String)prog.elementAt(0)) == 0)
      return true;
    return false;
  } // end of Compiler.isNextToken(Vector, String)

  boolean token(Vector prog, String tok) {
    if (prog.size() == 0) return false;
    if (tok.compareTo((String)prog.elementAt(0)) == 0) {
      prog.removeElementAt(0);
      return true;
    }
    return false;
  } // end of Compiler.token(Vector, String)

  boolean atom(Vector prog, CompilerStructure struc) {
    if (constant(prog, struc))
      return true;
    if (variable(prog, struc))
      return true;
    return false;
  } // end of Compiler.atom(Vector, CompilerStructure)

  boolean expression(Vector prog, CompilerStructure struc) {
    Vector oldProg = (Vector)prog.clone();
    struc.type = struc.EXPRESSION;
    struc.head = new CompilerStructure();
    struc.tail = new CompilerStructure();
    int cnt = 1;
    String tok = "";
    do {
      switch (cnt) {
        case 1: tok = "+"; break;
        case 2: tok = "-"; break;
        case 3: tok = "*"; break;
        case 4: tok = "/"; break;
        case 5: tok = "%"; break;
      }
      if ((atom(prog, struc.head)) && (token(prog, tok)) && (atom(prog, struc.tail))) {
        struc.value = tok;
        return true;
      }
      prog.clear();
      prog.addAll(oldProg);
    } while (++cnt <= 4);
    errorString = "Invalid expression on right side of assignment.";
    return false;
  } // end of Compiler.expression(Vector, CompilerStructure)

  boolean condition(Vector prog, CompilerStructure struc) {
    if (prog == null) return false;
    Vector oldProg = (Vector)prog.clone();
    struc.head = new CompilerStructure();
    struc.tail = new CompilerStructure();
    // first type of a condition is a comparison
    if (atom(prog, struc.head)) {
      struc.type = struc.COMPARISON;
      if (isNextToken(prog, ">")) {
        token(prog, ">");
        if (isNextToken(prog, "=")) {
          if ((token(prog, "=")) && (atom(prog, struc.tail))) {
            struc.value = ">=";
            return true;
          }
        }
        else if (atom(prog, struc.tail)) {
          struc.value = ">";
          return true;
        }
      }
      else if (isNextToken(prog, "<")) {
        token(prog, "<");
        if (isNextToken(prog, "=")) {
          if ((token(prog, "=")) && (atom(prog, struc.tail))) {
            struc.value = "<=";
            return true;
          }
        }
        else if (atom(prog, struc.tail)) {
          struc.value = "<";
          return true;
        }
      }
      else if (isNextToken(prog, "!")) {
        token(prog, "!");
        if ((token(prog, "=")) && (atom(prog, struc.tail))) {
          struc.value = "!=";
          return true;
        }
      }
      else if (isNextToken(prog, "\\")) {
        token(prog, "\\");
        if ((token(prog, "=")) && (atom(prog, struc.tail))) {
          struc.value = "!=";
          return true;
        }
      }
    } // end of comparison checks
    prog.clear();
    prog.addAll(oldProg);
    if ((element(prog, struc.head)) && (token(prog, "=")) && (element(prog, struc.tail))) {
      struc.type = struc.UNIFICATION;
      return true;
    }
    prog.clear();
    prog.addAll(oldProg);
    if ((variable(prog, struc.head)) && (token(prog, "is")) && (expression(prog, struc.tail))) {
      struc.type = struc.ASSIGNMENT;
      return true;
    }
    prog.clear();
    prog.addAll(oldProg);
    if ((token(prog, "not")) && (predicate(prog, struc.head))) {
      struc.type = struc.NOT_CALL;
      if (isNextToken(prog, "(")) {
        token(prog, "(");
        if ((list(prog, struc.tail)) && (token(prog, ")")))
          return true;
      }
      else {
        struc.tail = null;
        return true;
      }
    }
    prog.clear();
    prog.addAll(oldProg);
    if (predicate(prog, struc.head)) {
      struc.type = struc.CALL;
      if (isNextToken(prog, "(")) {
        token(prog, "(");
        if ((list(prog, struc.tail)) && (token(prog, ")")))
          return true;
      }
      else {
        struc.tail = null;
        return true;
      }
    }
    prog.clear();
    prog.addAll(oldProg);
    if (isNextToken(prog, "!")) {
      token(prog, "!");
      struc.type = struc.CUT;
      return true;
    }
    return false;
  } // end of Compiler.condition(Vector, CompilerStructure)

  boolean body(Vector prog, CompilerStructure struc) {
    Vector oldProg = (Vector)prog.clone();
    struc.type = struc.BODY;
    struc.head = new CompilerStructure();
    struc.tail = new CompilerStructure();
    if (condition(prog, struc.head)) {
      if (isNextToken(prog, ",")) {
        token(prog, ",");
        if (body(prog, struc.tail)) return true;
      }
      else {
        struc.tail = null;
        return true;
      }
    }
    prog.clear();
    prog.addAll(oldProg);
    return false;
  } // end of Compiler.body(Vector, CompilerStructure)

  boolean clause(Vector prog, CompilerStructure struc) {
    Vector oldProg = (Vector)prog.clone();
    struc.type = struc.CLAUSE;
    struc.head = new CompilerStructure();
    struc.tail = new CompilerStructure();
    if (head(prog, struc.head)) {
      if (isNextToken(prog, ":")) {
        token(prog, ":");
        if ((token(prog, "-")) && (body(prog, struc.tail)) && (token(prog, ".")))
          return true;
      }
      else if (isNextToken(prog, ".")) {
        token(prog, ".");
        struc.tail = null;
        return true;
      }
      else
        errorString = "Missing \".\" at end of clause.";
    }
    prog.clear();
    prog.addAll(oldProg);
    return false;
  } // end of Compiler.clause(Vector, CompilerStructure)

  boolean program(Vector prog, CompilerStructure struc) {
    Vector oldProg = (Vector)prog.clone();
    struc.type = struc.PROGRAM;
    struc.head = new CompilerStructure();
    struc.tail = new CompilerStructure();
    if (clause(prog, struc.head)) {
      if (program(prog, struc.tail))
        return true;
      struc.tail = null;
      return true;
    }
    return false;
  } // end of Compiler.program(Vector, CompilerStructure)

  boolean head(Vector prog, CompilerStructure struc) {
    Vector oldProg = (Vector)prog.clone();
    struc.type = struc.HEAD;
    struc.head = new CompilerStructure();
    struc.tail = new CompilerStructure();
    if (predicate(prog, struc.head)) {
      if (isNextToken(prog, "(")) {
        token(prog, "(");
        if ((list(prog, struc.tail)) && (token(prog, ")"))) return true;
      }
      else {
        struc.tail = null;
        return true;
      }
    }
    return false;
  } // end of Compiler.head(Vector, CompilerStructure)

  boolean list(Vector prog, CompilerStructure struc) {
    Vector oldProg = (Vector)prog.clone();
    struc.type = struc.LIST;
    struc.head = new CompilerStructure();
    struc.tail = new CompilerStructure();
    if (element(prog, struc.head)) {
      if (isNextToken(prog, "|")) {
        token(prog, "|");
        if (element(prog, struc.tail)) return true;
      }
      else if (isNextToken(prog, ",")) {
        token(prog, ",");
        if (list(prog, struc.tail)) return true;
      }
      else {
        struc.tail = null;
        return true;
      }
    }
    prog.clear();
    prog.addAll(oldProg);
    return false;
  } // end of Compiler.list(Vector, CompilerStructure)

  Vector stringToList(String text) {
    int i;
    Vector result = new Vector();
    String dummy = "";
    for (i = 0; i < text.length(); i++) {
      char pos = text.charAt(i);
      if (pos == '\'') {
        if (dummy.length() > 0) return null;
        dummy = "'";
        do {
          i++;
          dummy += text.charAt(i);
          if (text.charAt(i) == '\'')
            break;
        } while (i < text.length() - 1);
      }
      else if (pos != ' ') {
        if ((pos == '(') || (pos == ')') || (pos == '[') || (pos == ']') ||
            (pos == ',') || (pos == '.') || (pos == '|') || (pos == '=') ||
            (pos == '<') || (pos == '>') || (pos == '%') || (pos == '\\') ||
            (pos == '+') || (pos == '-') || (pos == '*') || (pos == '/')) {
          if (dummy.length() > 0)
            result.addElement(dummy);
          dummy = "";
          result.addElement(dummy + pos);
        }
        else
          dummy += pos;
      }
      else {
        if (dummy.length() > 0)
          result.addElement(dummy);
        dummy = "";
      }
    }
    if (dummy.length() > 0)
      result.addElement(dummy);
    return result;
  } // end of Compiler.stringToList(String)

  String substituteVariable(String variable) {
    if ((variable.length() > 0) && (variable.compareTo("_") != 0))
      for (int i = 0; i < substitutionList.size(); i++)
        if (variable.compareTo(((KeyValue)substitutionList.elementAt(i)).key) == 0) {
          lastVar = ((KeyValue)substitutionList.elementAt(i)).stringValue;
          return lastVar;
        }
    String newVar = varPrefix + new Integer(substitutionList.size()).toString();
    substitutionList.addElement(new KeyValue(variable, newVar));
    lastVar = newVar;
    return newVar;
  } // end of Compiler.substituteVariable(String)

  boolean firstOccurrence(String variable) {
    if ((variable.length() > 0) && (variable.compareTo("_") != 0))
      for (int i = 0; i < substitutionList.size(); i++)
        if (variable.compareTo(((KeyValue)substitutionList.elementAt(i)).key) == 0)
          return false;
    return true;
  } // end of Compiler.firstOccurrence(String)

  // structureToCode takes a CompilerStructure, generated by the parser, and constructs
  // a WAM program from it, recursively
  Program structureToCode(CompilerStructure struc) {
    if (struc == null) return null;
    Program result = new Program(owner);
    if (struc.type == struc.PROGRAM) {
      if (struc.head == null) return null;
      result.addProgram(structureToCode(struc.head));
      result.addProgram(structureToCode(struc.tail));
    } // end of case struc.PROGRAM
    else if ((struc.type == struc.CALL) || (struc.type == struc.NOT_CALL)) {
      bodyCalls++;
      if (struc.tail != null) {
        CompilerStructure s = struc.tail;
        int argCount = 0;
        do {
          if (s.head.type == s.CONSTANT)
            result.addStatement(new Statement("", "put_constant", s.head.value, "A" + argCount));
          else if (s.head.type == s.VARIABLE) {
            if ((varPrefix.compareTo("Q") == 0) && (firstOccurrence(s.head.value)))
              result.addStatement(new Statement("", "create_variable", substituteVariable(s.head.value), s.head.value));
            result.addStatement(new Statement("", "put_value", substituteVariable(s.head.value), "A" + argCount));
          }
          else {
            result.addProgram(structureToCode(s.head));
            result.addStatement(new Statement("", "put_value", lastVar, "A" + argCount));
          }
          argCount++;
          s = s.tail;
        } while (s != null);
      }
      if (struc.type == struc.CALL)
        result.addStatement(new Statement("", "call", struc.head.value));
      else
        result.addStatement(new Statement("", "not_call", struc.head.value));
    } // end of case struc.CALL / struc.NOT_CALL
    else if (struc.type == struc.UNIFICATION) {
      result.addProgram(structureToCode(struc.head));
      String headVar = lastVar;
      result.addProgram(structureToCode(struc.tail));
      String tailVar = lastVar;
      result.addStatement(new Statement("", "unify_variable", headVar, tailVar));
    } // end of case struc.UNIFICATION
    else if (struc.type == struc.HEAD) {
      String name = struc.head.value;
      int j1 = name.indexOf('~');
      int j2 = name.indexOf('/');
      int atAll = new Integer(name.substring(j2 + 1)).intValue();
      name = name.substring(0, j2);
      int count = new Integer(name.substring(j1 + 1)).intValue();
      name = name.substring(0, j1);
      if (count == 1)
        struc.head.value = name;
      else
        struc.head.value = name + '~' + count;
      if (count < atAll) {
        if (count > 1)
          result.addStatement(new Statement(struc.head.value, "retry_me_else", name + '~' + (count + 1)));
        else
          result.addStatement(new Statement(struc.head.value, "try_me_else", name + '~' + (count + 1)));
      }
      else
        result.addStatement(new Statement(struc.head.value, "trust_me", ""));
      if (struc.tail != null) {
        CompilerStructure s = struc.tail;
        int argCount = 0;
        do {
          if (s.head.type == s.CONSTANT)
            result.addStatement(new Statement("", "get_constant", s.head.value, "A" + argCount));
          else if (s.head.type == s.VARIABLE) {
            if (firstOccurrence(s.head.value))
              result.addStatement(new Statement("", "get_variable",
                                                substituteVariable(s.head.value), "A" + argCount));
            else
              result.addStatement(new Statement("", "get_value",
                                                substituteVariable(s.head.value), "A" + argCount));
          }
          else {
            String subst = substituteVariable("");
            result.addStatement(new Statement("", "get_variable", subst, "A" + argCount));
            result.addProgram(structureToCode(s.head));
            result.addStatement(new Statement("", "unify_variable", subst, lastVar));
          }
          argCount++;
          s = s.tail;
        } while (s != null);
      }
    } // end of case struc.HEAD
    else if (struc.type == struc.CONSTANT)
      result.addStatement(new Statement("", "put_constant", struc.value, substituteVariable("")));
    else if (struc.type == struc.VARIABLE) {
      if ((varPrefix.compareTo("Q") == 0) && (firstOccurrence(struc.value)))
        result.addStatement(new Statement("", "create_variable", substituteVariable(struc.value), struc.value));
      substituteVariable(struc.value);
    }
    else if (struc.type == struc.LIST) {
      if (struc.head != null) {
        Program p = structureToCode(struc.head);  // first of all, compile the list's head (i.e. its first element)
        if (p == null) return null;
        result.addProgram(p);
        String headVar, tailVar;
        if (struc.head.type == struc.VARIABLE)
          headVar = substituteVariable(struc.head.value);
        else
          headVar = lastVar;
        if (struc.tail == null) {  // end of list: put NIL sign
          tailVar = substituteVariable("");
          result.addStatement(new Statement("", "put_constant", "[]", tailVar));
        }
        else {  // otherwise compile the tail
          p = structureToCode(struc.tail);
          if (p == null) return null;
          result.addProgram(p);
          tailVar = lastVar;
        }  // and finally, unify the list with head and tail
        result.addStatement(new Statement("", "unify_list", substituteVariable(""), headVar, tailVar));
        return result;
      }
      else // struc.head == null means: this is no real list, but a NIL
        result.addStatement(new Statement("", "put_constant", "[]", substituteVariable("")));
    } // end of case struc.LIST
    else if (struc.type == struc.STRUCTURE) {
      result.addProgram(structureToCode(struc.head));
      String headVar = lastVar;
      result.addProgram(structureToCode(struc.tail));
      String tailVar = lastVar;
      result.addStatement(new Statement("", "unify_struc", substituteVariable(""), headVar, tailVar));
      return result;
    } // end of case struc.STRUCTURE
    else if (struc.type == struc.CLAUSE) {
      substitutionList = new Vector();
      bodyCalls = 0;
      result.addProgram(structureToCode(struc.head));
      result.addProgram(structureToCode(struc.tail));
      if ((substitutionList.size() > 0) || (bodyCalls > 0)) {
        result.addStatementAtPosition(new Statement("", "allocate", ""), 1);
        result.addStatement(new Statement("", "deallocate", ""));
      }
      result.addStatement(new Statement("", "proceed", ""));
    } // end of case struc.CLAUSE
    else if (struc.type == struc.BODY) {
      CompilerStructure s = struc;
      do {
        if (s.head.type == s.CUT) {
          String y = substituteVariable("");
          result.addStatementAtPosition(new Statement("", "get_level", y), 0);
          result.addStatement(new Statement("", "cut", y));
        }
        else
          result.addProgram(structureToCode(s.head));
        s = s.tail;
      } while (s != null);
    } // end of case struc.BODY
    else if (struc.type == struc.QUERY) {
      if (struc.head == null)
        return null;
      result.addProgram(structureToCode(struc.head));
      if (struc.tail != null)
        result.addProgram(structureToCode(struc.tail));
      result.addStatement(new Statement("", "halt", ""));
    } // end of case struc.BODY
    else if (struc.type == struc.COMPARISON) {
      result.addProgram(structureToCode(struc.head));
      String headVar = lastVar;
      result.addProgram(structureToCode(struc.tail));
      String tailVar = lastVar;
      if (struc.value.compareTo(">") == 0)
        result.addStatement(new Statement("", "bigger", headVar, tailVar));
      else if (struc.value.compareTo("<") == 0)
        result.addStatement(new Statement("", "smaller", headVar, tailVar));
      else if (struc.value.compareTo(">=") == 0)
        result.addStatement(new Statement("", "biggereq", headVar, tailVar));
      else if (struc.value.compareTo("<=") == 0)
        result.addStatement(new Statement("", "smallereq", headVar, tailVar));
      else if (struc.value.compareTo("!=") == 0)
        result.addStatement(new Statement("", "unequal", headVar, tailVar));
    } // end of case struc.COMPARISON
    else if (struc.type == struc.ASSIGNMENT) {
      result.addProgram(structureToCode(struc.tail.head));
      String headVar = lastVar;
      result.addProgram(structureToCode(struc.tail.tail));
      String tailVar = lastVar;
      result.addProgram(structureToCode(struc.head));
      result.addStatement(new Statement("", "is", lastVar, struc.tail.value, headVar + " " + tailVar));
    } // end of case struc.COMPARISON
    return result;
  } // end of Compiler.structureToCode(CompilerStructure)

} // end of class Compiler

