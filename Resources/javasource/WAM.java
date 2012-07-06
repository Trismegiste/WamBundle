/*******************************************************************************
 * Warren's Abstract Machine  -  Implementation by Stefan Buettcher
 *
 * developed:   December 2001 until February 2002
 *
 * WAM.java contains the actual WAM and the additional structures ChoicePoint,
 * Environment and Trail
 ******************************************************************************/

import java.awt.*;
import java.io.*;
import java.util.*;

// class WAM is the core and contains the essential functions of the WAM
public class WAM {
  public static final int UNB = 0;  // variable-related constants:
  public static final int REF = 1;  // tag == REF means this variable is a reference
  public static final int CON = 2;  // this one has been bound to an immediate constant
  public static final int LIS = 3;  // is a list
  public static final int STR = 4;  // is a structure

  public static final int ASSERT = 9;  // this variable is no real variable but only used for trailing assert operations


  public static final int opAllocate        =  1;   // Statement constants, see there
  public static final int opBigger          =  2;
  public static final int opCall            =  3;
  public static final int opCreateVariable  =  4;
  public static final int opCut             =  5;
  public static final int opDeallocate      =  6;
  public static final int opGetConstant     =  7;
  public static final int opGetValue        =  8;
  public static final int opGetVariable     =  9;
  public static final int opHalt            = 10;
  public static final int opIs              = 11;
  public static final int opGetLevel        = 12;
  public static final int opNoOp            = 13;
  public static final int opProceed         = 14;
  public static final int opPutConstant     = 15;
  public static final int opPutValue        = 16;
  public static final int opPutVariable     = 17;
  public static final int opRetryMeElse     = 18;
  public static final int opSmaller         = 19;
  public static final int opTrustMe         = 20;
  public static final int opTryMeElse       = 21;
  public static final int opUnifyList       = 22;
  public static final int opUnifyStruc      = 23;
  public static final int opUnequal         = 24;
  public static final int opUnifyVariable   = 25;
  public static final int opBiggerEq        = 27;
  public static final int opSmallerEq       = 28;
  public static final int opNotCall         = 29;

  public static final int callWrite         = -10;
  public static final int callWriteLn       = -11;
  public static final int callNewLine       = -12;
  public static final int callConsult       = -13;
  public static final int callReconsult     = -14;
  public static final int callLoad          = -15;
  public static final int callAssert        = -16;
  public static final int callRetractOne    = -17;
  public static final int callRetractAll    = -18;
  public static final int callIsInteger     = -19;
  public static final int callIsAtom        = -20;
  public static final int callIsBound       = -21;
  public static final int callReadLn        = -22;
  public static final int callCall          = -23;


  /****************************** BEGIN SUBCLASSES ******************************/

  public class Variable {
    public int tag;            // UNB, REF, CON, LIS or STR
    public String value;       // variable's content in case of CON
    public Variable reference; // variable's content in case of REF
    public String name;        // name of variable, e.g. when it's a query variable
    public Variable head, tail;  // list/struc stuff
    public ChoicePoint cutLevel;  // f�r the cut and get_level instructions

    // constructor for creating a new, unbound variable without a name
    public Variable() {
      tag = REF;
      reference = this;
    }

    // constructor for creating a new, unbound variable with a name
    public Variable(String aName) {
      tag = REF;
      reference = this;
      name = aName;
    }

    // constructor for creating a new variable and binding it to a constant
    public Variable(String aName, String s) {
      tag = CON;
      value = s;
      name = aName;
    }

    // constructor for creating a new variable and unifying it with another
    public Variable(String aName, Variable v) {
      tag = REF;
      reference = v;
      name = aName;
    }

    // copyFrom-constructor
    public Variable(Variable v) {
      copyFrom(v);
    }

    // sets internal components to that of source
    public void copyFrom(Variable source) {
      tag = source.tag;
      if (tag == REF)
        reference = source.reference;
      else if (tag == CON)
        value = source.value;
      else {
        head = source.head;
        tail = source.tail;
      }
    }

    // dereferencing: if this variable points to another var, then return that dereferenced
    public Variable deref() {
      if ((tag == REF) && (reference != this)) {
        Variable result = reference;
        while ((result.tag == REF) && (result.reference != result))
          result = result.reference;
        return result;
      }
      else
        return this;
    } // end of Variable.deref()

    // returns a string in the form NAME = VALUE, representing the variable's value
    public String toString() {
      if ((tag == REF) && (reference == this))
        return "_"; // "(unbound variable)";
      if (tag == CON) {
//        if (value.indexOf(' ') < 0) {
          if ((value.length() > 2) && (value.indexOf(".0") == value.length() - 2))
            return value.substring(0, value.length() - 2);
          else
            return value;
//        }
//        else
//          return("'" + value + "'");
      }
      if (tag == LIS)
        return "[" + toString2() + "]";
      if (tag == STR) {
        String result = head.toString() + "(" + tail.toString2() + ")";
        return result;
      }
      if (tag == REF)
        return deref().toString();
      return "";
    } // end of Variable.toString()

    public String toString2() {
      if (tag == LIS) {
        String result = head.toString();
        if ((tail != null) && (tail.tag != CON))
          result += ", " + tail.toString2();
        return result;
      }
      return "";
    } // end of Variable.toString2()

  } // end of class Variable

  // class ChoicePoint implements the choice point concept, as presented by Ait-Kaci
  class ChoicePoint {
    public Vector arguments;             // the Ai variables
    public Environment lastEnviron;      // current environment when creating the choicepoint
    public int returnAddress;            // current continuation pointer (cp)
    public ChoicePoint lastCP;           // last ChoicePoint on stack
    public ChoicePoint cutPoint;         // copy of B0
    public int nextClause;               // current instruction pointer + 1
    public int trailPointer;             // current trail pointer

    // constructor gets A (argument variables vector), trailPtr (trail pointer) and
    // anAddress (current return address / continuation pointer)
    public ChoicePoint(Vector a, int trailPtr, int anAddress) {
      arguments = new Vector();
      lastEnviron = null;
      lastCP = null;
      returnAddress = anAddress;
      for (int i = 0; i < a.size(); i++)
        arguments.addElement(new Variable((Variable)a.elementAt(i)));
      trailPointer = trailPtr;
    } // end of ChoicePoint.ChoicePoint

  } // end of class ChoicePoint

  // class Environment for storing local variables that must not be overridden
  class Environment {
    public Vector variables;
    public Environment lastEnviron;
    public int returnAddress;

    // constructor gets the current return address (continuation pointer) and a pointer to the previous environment on stack
    public Environment(int anAddress, Environment anEnv) {
      lastEnviron = anEnv;
      returnAddress = anAddress;
      variables = new Vector();
    } // end of Environment.Environment(int, Environment)

  } // end of class Environment

  // Trail implements the WAM's trail (undo-list for bindings performed)
  class Trail {
    private Vector contents;

    public Trail() {
      contents = new Vector();
    }

    public int getLength() {
      return contents.size();
    }

    public void setLength(int length) {
      contents.setSize(length);
    }

    public void addEntry(Variable v) {
      contents.addElement(v);
    }

    public Variable getEntry(int index) {
      return (Variable)contents.elementAt(index);
    }

    public void undo(int index) {
      Variable v = (Variable)contents.elementAt(index);
      if (v != null) {
        if (v.tag == ASSERT)
          retract(v.value);
        else {
          v.tag = REF;
          v.reference = v;
        }
      }
    }

  } // end of class Trail

/****************************** END SUBCLASSES ******************************/

  // internal parameters, accessible by using the "set" command
  public int debugOn = 0;   // display debug information?
  private int benchmarkOn = 0;   // show benchmark information?
  private int maxOpCount = 50000000;  // artificial stack overflow limit

  public int opCount, backtrackCount;

  private Program p;         // the program(s) loaded into memory
  private Trail trail;       // undo-list (WAM trail)
  private boolean failed;    // set to true upon an unsuccessful binding operation
  boolean displayQValue[] = new boolean[100];   // which Query-Variables do have to displayed upon success?
  int displayQCount = 0;     // how many of them?

  // the WAM's register set
  private Vector queryVariables; // query variables, to be accessed by Q1, Q2, and so on
  private int programCounter = 0; // program counter
  private int continuationPointer = 0; // continuation pointer
  private ChoicePoint choicePoint = null; // last choicepoint on stack
  private ChoicePoint cutPoint = null; // current choicepoint for cut instruction
  private Environment env = null; // last environment on stack
  private Vector arguments;      // argument registers

  // in case we want to use the WAM inside our GUI
  public TextArea response = null;   // this is the memo box all the output is written into
  public Frame frame = null;
  public int GUImode = 0;    // 0 means: text mode, 1 means: GUI mode

  // creates a new WAM with program data initialized to aProgram
  public WAM(Program aProgram) {
    p = aProgram;
    reset();
  } // end of WAM.WAM(Program)

  // resets sets all WAM parameters to their initial values
  private void reset() {
    arguments = new Vector();  // no argument registers so far
    arguments.addElement(new Variable());
    env = new Environment(999999999, null);  // empty environment
    continuationPointer = -1;  // no continuation point
    trail = new Trail();
    queryVariables = new Vector();
    displayQCount = 0;
    for (int i = 0; i < 100; i++) displayQValue[i] = false;
    choicePoint = null;
    cutPoint = null;
  } // end of WAM.reset()

  // reads a String line from standard input
  private String readLn() {
    try {
      return new BufferedReader(new InputStreamReader(System.in)).readLine();
    } catch (IOException io) {
      return "";
    }
  } // end of WAM.readLn()

  // displays a string
  public void write(String s) {
    if (GUImode == 0)
      System.out.print(s);
    else
      response.append(s);
  } // end of WAM.write(String)

  // displays a string followed by CRLF
  public void writeLn(String s) {
    if (GUImode == 0)
      System.out.println(s);
    else
      response.append(s + "\n");
  } // end of WAM.writeLn(String)

  // displays a debug information line
  public void debug(String s, int debugLevel) {
    if (debugLevel < 0) {
      if (benchmarkOn > 0)
        writeLn(s);
    }
    else
      if (debugOn >= debugLevel)
        writeLn(s);
  } // end of WAM.debug(String, int)

  // formats an integer to a string
  private String int2FormatStr(int i) {
    String result = "";
    if (i < 1000) result += "0";
    if (i < 100) result += "0";
    if (i < 10) result += "0";
    result += i;
    return result;
  } // end of WAM.int2FormatStr(int)

  // displays the values of all internal parameters that can be modyfied using the "set" command
  private void displayInternalVariables() {
    getInternalVariable("autostop");
    getInternalVariable("benchmark");
    getInternalVariable("debug");
  } // end of WAM.displayInternalVariables()

  // sets the internal parameter specified by variable to a new value
  private void setInternalVariable(String variable, String value) {
    try {
      if (variable.compareToIgnoreCase("autostop") == 0)
        maxOpCount = parseInt(value);
      if (variable.compareToIgnoreCase("benchmark") == 0)
        benchmarkOn = parseInt(value);
      if (variable.compareToIgnoreCase("debug") == 0)
        debugOn = parseInt(value);
      getInternalVariable(variable);
    } catch (Exception e) {
      writeLn("An error occurred. Illegal query.");
    }
  } // end of WAM.setInternalVariable(String, String)

  // displays the value of the internal parameter specified by variable
  private void getInternalVariable(String variable) {
    if (variable.compareToIgnoreCase("autostop") == 0)
      writeLn("Internal variable AUTOSTOP = " + maxOpCount);
    else if (variable.compareToIgnoreCase("benchmark") == 0)
      writeLn("Internal variable BENCHMARK = " + benchmarkOn);
    else if (variable.compareToIgnoreCase("debug") == 0)
      writeLn("Internal variable DEBUG = " + debugOn);
    else
      writeLn("Unknown internal variable.");
  } // end of WAM.getInternalVariable(String)

  private int parseInt(String number) throws NumberFormatException {
    int len = number.length();
    int cnt = -1;
    int value = 0;
    char c;
    while (++cnt < len) {
      c = number.charAt(cnt);
      if ((c < '0') || (c > '9')) throw new NumberFormatException();
      value = value * 10 + (number.charAt(cnt) - '0');
    }
    return value;
  }

  // returns the Variable pointer belonging to a string, e.g. "A3", "Y25"
  private Variable get_ref(String name) {
    Vector array;
    switch (name.charAt(0)) {
      case 'Y': array = env.variables; break;
      case 'A': array = arguments; break;
      case 'Q': array = queryVariables; break;
      default: return null;
    }
    int len = name.length();
    int cnt = 0;
    int index = 0;
    while (++cnt < len)
      index = index * 10 + (name.charAt(cnt) - '0');
    cnt = array.size();
    while (cnt++ < index + 1)
      array.addElement(new Variable());
    return (Variable)array.elementAt(index);
  } // end of WAM.get_ref(String)

/******************** BEGIN WAM CODE OPERATIONS ********************/

// WAM code operations are described in Ait Kaci: Warren's Abstract Machine -- A Tutorial Reconstruction

  // gives a name to a variable; usually used on Qxx variables that occur within the query
  private void create_variable(String v, String name) {
    if (name.compareTo("_") != 0) {  // keep "_" from being displayed as solution
      Variable q = get_ref(v);
      q.name = name;
      // update displayQ-stuff
      int i = parseInt(v.substring(1));
      if (!displayQValue[i]) {
        displayQCount++;
        displayQValue[i] = true;
      }
    }
    programCounter++;
  } // end of WAM.create_variable(String, String)

  // comparison manages "<", "<=", ">=", ">" and "!="
  private void comparison(String s1, String s2, int comparator) {
    // comparator values: 1 = "<", 2 = "<=", 3 = ">=", 4 = ">", 5 = "!="
    Variable v1 = get_ref(s1).deref();
    Variable v2 = get_ref(s2).deref();
    if ((v1.tag == CON) && (v2.tag == CON)) {
      int compareValue;
      try { compareValue = parseInt(v1.value) - parseInt(v2.value); }
      catch (Exception e) { compareValue = v1.value.compareTo(v2.value); }
      switch (comparator) {
        case 1: if (compareValue < 0) programCounter++;
                  else backtrack();
                break;
        case 2: if (compareValue <= 0) programCounter++;
                  else backtrack();
                break;
        case 3: if (compareValue >= 0) programCounter++;
                  else backtrack();
                break;
        case 4: if (compareValue > 0) programCounter++;
                  else backtrack();
                break;
        case 5: if (compareValue != 0) programCounter++;
                  else backtrack();
                break;
        default: backtrack();
      }
    }
    else
      backtrack();
  } // end of WAM.comparison(String, String, String)

  private void smaller(String s1, String s2) {
    comparison(s1, s2, 1);
  } // end of WAM.smaller(String, String)

  private void smallereq(String s1, String s2) {
    comparison(s1, s2, 2);
  } // end of WAM.smallereq(String, String)

  private void biggereq(String s1, String s2) {
    comparison(s1, s2, 3);
  } // end of WAM.biggereq(String, String)

  private void bigger(String s1, String s2) {
    comparison(s1, s2, 4);
  } // end of WAM.bigger(String, String)

  private void unequal(String s1, String s2) {
    comparison(s1, s2, 5);
  } // end of WAM.unequal(String, String)

  // is manages integer arithmetic (floating point may be added later)
  private void is(String target, char op, String s1, String s2) {
    Variable v1, v2, v3;
    int z1, z2, z3;
    // convert s1 or the value of the variable referenced by s1 to int value
    try { z1 = parseInt(s1); }
    catch (Exception e) {
      v1 = get_ref(s1).deref();
      if (v1.tag != CON) { backtrack(); return; }
      try { z1 = parseInt(v1.value); }
      catch (Exception e2) { backtrack(); return; }
    }
    // convert s2 or the value of the variable referenced by s2 to int value
    try { z2 = parseInt(s2); }
    catch (Exception e) {
      v2 = get_ref(s2).deref();
      if (v2.tag != CON) { backtrack(); return; }
      try { z2 = parseInt(v2.value); }
      catch (Exception e2) { backtrack(); return; }
    }
    // check which variable is referenced by target
    v3 = get_ref(target).deref();
    try {
      z3 = 0;
      // do the arithmetic
      if (op == '+') z3 = z1 + z2;
      if (op == '-') z3 = z1 - z2;
      if (op == '*') z3 = z1 * z2;
      if (op == '/') z3 = z1 / z2;
      if (op == '%') z3 = z1 % z2;
      // if v3 (the target) has already been bound, consider this an equality check
//      if ((v3.tag == CON) && (parseInt(v3.value) != z3))      // do not allow this for now, since problems might occur
//        backtrack();
      if (v3.tag == REF) {
        // if it has not been bound yet, bind it to constant value z3 (the integer number)
        trail.addEntry(v3);
        v3.tag = CON;
        v3.value = "" + z3;
        programCounter++;
      }
      // only when alle stricke reissen: backtrack!
      else
        backtrack();
    }
    catch (Exception e) {
      backtrack();
    }
  } // end of WAM.is(String, String, String, String)

  private void get_variable(String s1, String s2) {
    Variable Vn = get_ref(s1);
    Variable Ai = get_ref(s2);
    Vn.copyFrom(Ai);
    programCounter++;
  } // end of WAM.get_variable(String, String)

  private void get_value(String s1, String s2) {
    unify_variable(s2, s1);
  } // end of WAM.get_value(String, String)

  private void get_constant(String c, String variable) {
    Variable v = get_ref(variable).deref();
    //writeLn(variable + " => " + v + " (" + c + ")");
    boolean fail = true;
    if (v.tag == REF) {
      trail.addEntry(v);
      v.tag = CON;
      v.value = c;
      fail = false;
    }
    else if (v.tag == CON) {
      if (c.compareTo(v.value) == 0)
        fail = false;
    }
    if (fail)
      backtrack();
    else
      programCounter++;
  } // end of WAM.get_constant(String, String)

  private boolean unify_variable2(Variable v1, Variable v2) {
    if ((v1 == null) || (v2 == null)) return false;
    v1 = v1.deref();
    v2 = v2.deref();
    if (v1 == v2) return true;

    if (v1.tag == REF) {
      trail.addEntry(v1);
      v1.copyFrom(v2);
      return true;
    }
    if (v2.tag == REF) {
      trail.addEntry(v2);
      v2.copyFrom(v1);
      return true;
    }

    if ((v1.tag == CON) && (v2.tag == CON)) {
      if (v1.value.compareTo(v2.value) == 0)
        return true;
      else
        return false;
    }

    if (((v1.tag == LIS) && (v2.tag == LIS)) || ((v1.tag == STR) && (v2.tag == STR)))
      if ((unify_variable2(v1.head, v2.head)) && (unify_variable2(v1.tail, v2.tail)))
        return true;

    return false;
  } // end of WAM.unify_variable2(Variable, Variable)

  private boolean unify_list2(Variable list, Variable head, Variable tail) {
//    list = list.deref();
//    head = head.deref();
//    tail = tail.deref();
    if (list.tag == REF) {
      trail.addEntry(list);
      list.tag = LIS;
      list.head = head;
      list.tail = tail;
      return true;
    }
    if (list.tag == LIS) {
      if (unify_variable2(head, list.head))
        if (unify_variable2(tail, list.tail))
          return true;
    }
    return false;
  } // end of WAM.unify_list2(Variable, Variable, Variable)

  private boolean unify_struc2(Variable struc, Variable head, Variable tail) {
//    struc = struc.deref();
//    head = head.deref();
//    tail = tail.deref();
    if (struc.tag == REF) {
      trail.addEntry(struc);
      struc.tag = STR;
      struc.head = head;
      struc.tail = tail;
      return true;
    }
    if (struc.tag == STR) {
      if (unify_variable2(head, struc.head))
        if (unify_variable2(tail, struc.tail))
          return true;
    }
    return false;
  } // end of WAM.unify_struc2(Variable, Variable, Variable)

  private void unify_variable(String s1, String s2) {
    Variable v1 = get_ref(s1);
    Variable v2 = get_ref(s2);
    if (unify_variable2(v1, v2))
      programCounter++;
    else
      backtrack();
  } // end of WAM.unify_variable(String, String)

  private void unify_list(String l, String h, String t) {
    Variable list = get_ref(l);
    Variable head = get_ref(h);
    Variable tail = get_ref(t);
    if (unify_list2(list, head, tail))
      programCounter++;
    else
      backtrack();
  } // end of WAM.unify_list(String, String, String)

  private void unify_struc(String s, String h, String t) {
    Variable struc = get_ref(s);
    Variable head = get_ref(h);
    Variable tail = get_ref(t);
    if (unify_struc2(struc, head, tail))
      programCounter++;
    else
      backtrack();
  } // end of WAM.unify_struc(String, String, String)

  private void put_constant(String c, String a) {
    Variable Ai = get_ref(a);
    Ai.tag = CON;
    Ai.value = c;
    programCounter++;
  } // end of WAM.put_constant(String, String)

  private void put_list(String h, String t, String a) {
    Variable Ai = get_ref(a);
    Ai.tag = LIS;
    Ai.head = get_ref(h).deref();
    Ai.tail = get_ref(t).deref();
    programCounter++;
  } // end of WAM.put_list(String, String, String);

  private void put_value(String s1, String s2) {
    Variable Vi = get_ref(s1);
    Variable An = get_ref(s2);
    An.copyFrom(Vi);
    programCounter++;
  } // end of WAM.put_value(String, String)

  private void put_variable(String s1, String s2) {
    Variable Vn = get_ref(s1).deref();
    Variable Ai = get_ref(s2);
    Ai.tag = REF;
    Ai.reference = Vn;
    programCounter++;
  } // end of WAM.put_variable(String, String)

  private void try_me_else(int whom) {
    int i;
    ChoicePoint cp = new ChoicePoint(arguments, trail.getLength(), continuationPointer);
    cp.lastCP = choicePoint;
    cp.cutPoint = cutPoint;
    choicePoint = cp;
    cp.nextClause = whom;
    cp.lastEnviron = env;
    programCounter++;
  } // end of WAM.try_me_else(int)

  private void proceed() {
    programCounter = continuationPointer;
  } // end of WAM.proceed()

  private void is_bound(Variable v) {
    v = v.deref();
    if (v.tag == REF)
      backtrack();
    else
      programCounter++;
  } // end of WAM.is_bound(String)

  private void allocate() {
    Environment environment = new Environment(continuationPointer, env);
    env = environment;
    programCounter++;
  } // end of WAM.allocate()

  private void deallocate() {
    continuationPointer = env.returnAddress;
    env = env.lastEnviron;
    programCounter++;
  } // end of WAM.deallocate()

  private void call(int target) {
    if (target >= 0) {
      continuationPointer = programCounter + 1;
      cutPoint = choicePoint;
      programCounter = target;
    }
    else  // linenumbers < 0 indicate internal predicates, e.g. writeln
      if (!internalPredicate(target))
        backtrack();
  } // end of WAM.call(int)

  // not_call performs a negated call by invoking a new WAM process
  // if the new process' execution fails, not_call is successful (backtrack, otherwise)
  private void not_call(int target) {
    if ((target <= -10) && (target >= -40)) {
      backtrack();
      return;
    }
    // create a second WAM with the same code inside
    WAM wam2 = new WAM(p);
    wam2.programCounter = target;  // set programCounter the continuationPointer to their desired values
    wam2.continuationPointer = p.getStatementCount();
    // add a halt statement, making wam2 return "true" upon success. this is necessary!
    p.addStatement(new Statement("", "halt", ""));
    wam2.arguments.clear();  // now, duplicate the argument vector
    for (int i = 0; i < arguments.size(); i++)
      wam2.arguments.addElement(new Variable((Variable)arguments.elementAt(i)));
    // we don't need any benchmarking information from the child WAM
    wam2.debugOn = debugOn;
    wam2.benchmarkOn = 0;
    wam2.run();
    boolean wam2failed = wam2.failed;
    while (wam2.choicePoint != null)
      wam2.backtrack();
    wam2.backtrack();
    p.deleteFromLine(p.getStatementCount() - 1);  // remove the earlier added "halt" statement from p
    opCount += wam2.opCount;
    backtrackCount += wam2.backtrackCount;  // update benchmarking information
    if (wam2failed) {  // if wam2 failed, return "success"
      failed = false;
      programCounter++;
    }
    else // if it succeeded, consider this bad (since we are inside a not statement)
      backtrack();
  } // end of WAM.not_call(int)

  private void cut(String Vn) {
    Variable v = get_ref(Vn);
    choicePoint = v.cutLevel;
    programCounter++;
  } // end of WAM.cut(String)

  private void get_level(String Vn) {
    Variable v = get_ref(Vn);
    v.cutLevel = cutPoint;
    programCounter++;
  } // of WAM.get_level(String)

/******************** END WAM CODE OPERATIONS ********************/

  // called upon an unsuccessful binding operation or a call with non-existent target
  private void backtrack() {
    int i;
    if (debugOn > 0)
      writeLn("-> backtrack");
    backtrackCount++;
    failed = true;
    if (choicePoint != null) {
      continuationPointer = choicePoint.returnAddress;
      programCounter = choicePoint.nextClause;
      env = choicePoint.lastEnviron;
      int tp = choicePoint.trailPointer;
      for (i = trail.getLength() - 1; i >= tp; i--)
        trail.undo(i);
      trail.setLength(tp);
      arguments = choicePoint.arguments;
      cutPoint = choicePoint.cutPoint;
      choicePoint = choicePoint.lastCP;
    }
    else {
      for (i = trail.getLength() - 1; i >= 0; i--)
        trail.undo(i);
      programCounter = -1;
    }
  } // end of WAM.backtrack()

/******************** BEGIN INTERNAL PREDICATES ********************/

  // internalPredicate manages the execution of all built-in predicates, e.g. write, consult, isbound
  private boolean internalPredicate(int index) {
    boolean result = true;
    Variable v = (Variable)arguments.elementAt(0);
    if (index == callIsAtom)
      isAtom(v.deref());
    else if (index == callIsInteger)
      isInteger(v.toString());
    else if (index == callIsBound)
      is_bound(v);
    else if (index == callWrite) {
      write(v.toString());
      programCounter++;
    }
    else if (index == callWriteLn) {
      writeLn(v.toString());
      programCounter++;
    }
    else if (index == callNewLine) {
      writeLn("");
      programCounter++;
    }
    else if (index == callAssert) {
      assert(v.head.toString(), v.toString());
      return true;
    }
    else if (index == callRetractOne) {
      if (retract(v.toString()))
        programCounter++;
      else
        backtrack();
    }
    else if (index == callRetractAll)
      retractall(v.toString());
    else if (index == callCall) {  // internal predicate call(X)
      Variable v2 = new Variable(v).deref();
      Integer intg;
      int target = -1;
      if (v2.tag == CON) {
        intg = (Integer)p.labels.get(v2.value);
        if (intg != null)
          target = intg.intValue();
      }
      else if (v2.tag == STR) {
        intg = (Integer)p.labels.get(v2.head.value);
        if (intg != null) {
          target = intg.intValue();
          Variable tail = v2.tail;
          int cnt = 0;
          while (tail != null) {
            get_ref("A" + cnt).tag = REF;
            get_ref("A" + cnt).reference = tail.head;
            cnt++;
            tail = tail.tail;
          }
        }
      }
      if (target >= 0)
        call(target);
      else
        backtrack();
    }
    else if (index == callLoad)
      load(v.toString());
    else if (index == callConsult)
      consult(v.toString());
    else if (index == callReadLn) {
      Variable w = new Variable("", readLn());
      unify_variable2(v.deref(), w);
      programCounter++;
    }
    else
      result = false;
    return result;
  } // end of WAM.internalPredicate(String)

  private void load(String fileName) {
    Program prog = CodeReader.readProgram(fileName);
    if (prog == null)
      if (fileName.indexOf(".wam") <= 0) {  // if compilation didn't work, try with different file extension
        writeLn("File \"" + fileName + "\" could not be opened.");
        writeLn("Trying \"" + fileName + ".wam\" instead.");
        prog = CodeReader.readProgram(fileName + ".wam");
      }
    if (prog == null)
      backtrack();
    else {
      p.addProgram(prog);
      p.updateLabels();
      programCounter++;
    }
  } // end of WAM.load(String)

  private void isAtom(Variable v) {
    v = v.deref();
    if ((v.tag == CON) || (v.tag == REF))
      programCounter++;
    else
      backtrack();
  } // end of WAM.isAtom(Variable)

  // checks if stuff contains an integer number
  private void isInteger(String stuff) {
    try {
      parseInt(stuff);
      programCounter++;
    }
    catch (Exception e) {
      backtrack();
    }
  } // end of WAM.isInteger(String)

  // assert asserts a new clause to the current program
  private void assert(String label, String clause) {
    PrologCompiler pc = new PrologCompiler(this);
    Program prog = pc.compileSimpleClause(clause + ".");
    if (prog != null) {
      p.addClause(label, prog);
      programCounter++;
      Variable v = new Variable("", label);
      v.tag = ASSERT;
      trail.addEntry(v);
    }
    else
      backtrack();
  } // end of WAM.assert(String, String)

  private void removeProgramLines(int fromLine) {
    int size = p.getStatementCount();
    int removed = p.deleteFromLine(fromLine);
    if (programCounter >= fromLine) {
      if (programCounter >= fromLine + removed)
        programCounter -= removed;
      else
        backtrack();
    }
  }

  // retract undoes an assert action
  private boolean retract(String clauseName) {
    int index1 = p.getLastClauseOf(clauseName);
    int index2 = p.getLastClauseButOneOf(clauseName);
    if (index1 >= 0) {
      removeProgramLines(index1);
      if (index2 >= 0) {
        Statement s =  p.getStatement(index2);
        s.setFunction("trust_me");
        s.getArgs().setElementAt("", 0);
        s.arg1 = "";
      }
      return true;
    }
    else
      return false;
  }

  // calls retract(String) until it returns false
  private void retractall(String clauseName) {
    boolean success = false;
    failed = false;
    while (retract(clauseName)) {
      if (failed) return;
      success = true;
    };
    if (success)
      programCounter++;
    else
      backtrack();
  } // end of WAM.retractall(String)

  // consult compiles a prolog program and loads the resulting code into memory
  private void consult(String fileName) {
    PrologCompiler pc = new PrologCompiler(this);
    Program prog = pc.compileFile(fileName);
    if (prog == null)
      if (fileName.indexOf(".pro") <= 0) {  // if compilation didn't work, try with different file extension
        writeLn("Trying \"" + fileName + ".prolog\" instead.");
        prog = pc.compileFile(fileName + ".prolog");
      }
    if (prog == null)  // program could not be compiled/loaded for whatever reason
      backtrack();
    else {
      if (debugOn > 1)  // in case of debug mode, display the WAM code
        writeLn(prog.toString());
      p.owner = this;
      p.addProgram(prog);  // add program to that already in memory
      p.updateLabels();  // and don't forget to update the jump labels
      programCounter++;
    }
  } // end of WAM.consult(String)

/******************** END INTERNAL PREDICATES ********************/


  // showHelp shows a list of the available commands
  private void showHelp() {
    writeLn("This is Stu's mighty WAM speaking. Need some help?");
    writeLn("");
    writeLn("Available commands:");
    writeLn("clear                   empties the output area (GUI mode only)");
    writeLn("exit                    terminates the WAM");
    writeLn("help                    displays this help");
    writeLn("list                    lists the WAM program currently in memory");
    writeLn("new                     removes all WAM code from memory");
    writeLn("set [PARAM[=VALUE]]     displays all internal parameters (\"set\") or lets");
    writeLn("                        the user set a parameter's new value, respectively");
    writeLn("labels                  displays all labels that can be found in memory");
    writeLn("procedures              displays the names of all procedures in memory");
    writeLn("quit                    terminates the WAM");
    writeLn("");
    writeLn("Prolog programs can be compiled into memory by typing \"consult(filename).\",");
    writeLn("e.g. \"consult('lists.pro').\". Existing WAM programs can be loaded into");
    writeLn("memory by typing \"load(filename).\".");
    writeLn("");
    writeLn("" + p.getStatementCount() + " lines of code in memory.");
  } // end of WAM.showHelp()

  protected void traceOn()
  {
      write("A=[");
      for (int i = 0; i < arguments.size(); i++) {
          Variable v = (Variable) arguments.elementAt(i);
          write(v.tag + "/");
          write(v.value);
          write("("+v.toString()+") ");
      }
      write("] V=[");
      for (int i = 0; i < env.variables.size(); i++) {
          Variable v = (Variable) env.variables.elementAt(i);
          write(v.tag + "/");
          write(v.value);
          write("("+v.toString()+") ");
      }
      writeLn("]");
  }

  // run starts the actual execution of the program in memory
  public void run() {
    // opCount and backtrackCount are used for benchmarking
    opCount = 0;
    backtrackCount = 0;

    failed = true;

    while (programCounter >= 0) {   // programCounter < 0 happens on jump error or backtrack without choicepoint
      failed = false;
      Statement s = p.getStatement(programCounter);  // get current WAM statement

      if (debugOn > 0)  // display statement and line number information in case of debug mode
        writeLn("(" + int2FormatStr(programCounter) + ")  " + s.toString());

      // we have introduced an artificial stack overflow limit in order to prevent the WAM from infinite execution
      if (opCount++ > maxOpCount) {
        writeLn("Maximum OpCount reached. Think of this as a stack overflow.");
        failed = true;
        break;
      }

      traceOn();

      // select WAM command and execute the responsible method, e.g. "deallocate()"
      int op = s.operator;
           if (op == opAllocate) allocate();
      else if (op == opCall) call(s.jump);
      else if (op == opNotCall) not_call(s.jump);
      else if (op == opCut) cut(s.arg1);
      else if (op == opDeallocate) deallocate();
      else if (op == opGetVariable) get_variable(s.arg1, s.arg2);
      else if (op == opPutValue) put_value(s.arg1, s.arg2);
      else if (op == opPutVariable) put_variable(s.arg1, s.arg2);
      else if (op == opGetLevel) get_level(s.arg1);
      else if (op == opGetConstant) get_constant(s.arg1, s.arg2);
      else if (op == opGetValue) get_value(s.arg1, s.arg2);
      else if (op == opPutConstant) put_constant(s.arg1, s.arg2);
      else if (op == opUnifyList) unify_list(s.arg1, s.arg2, s.arg3);
      else if (op == opUnifyStruc) unify_struc(s.arg1, s.arg2, s.arg3);
      else if (op == opUnifyVariable) unify_variable(s.arg1, s.arg2);
      else if (op == opRetryMeElse) try_me_else(s.jump);
      else if (op == opTryMeElse) try_me_else(s.jump);
      else if (op == opTrustMe) programCounter++;
      else if (op == opProceed) proceed();
      else if (op == opBigger) bigger(s.arg1, s.arg2);
      else if (op == opBiggerEq) biggereq(s.arg1, s.arg2);
      else if (op == opSmaller) smaller(s.arg1, s.arg2);
      else if (op == opSmallerEq) smallereq(s.arg1, s.arg2);
      else if (op == opUnequal) unequal(s.arg1, s.arg2);
      else if (op == opIs) is(s.arg1, s.arg2.charAt(0), s.arg3, (String)s.getArgs().elementAt(3));
      else if (op == opHalt) break;
      else if (op == opNoOp) programCounter++;
      else if (op == opCreateVariable) create_variable(s.arg1, s.arg2);
      else { // invalid command: backtrack!
        writeLn("Invalid operation in line " + int2FormatStr(programCounter));
        backtrack();
      }

      traceOn();

    }; // end of while (programCounter >= 0)
    if (failed) {
      while (choicePoint != null) backtrack();
      backtrack();
    }
    if (benchmarkOn > 0) {
      writeLn("# operations: " + opCount);
      writeLn("# backtracks: " + backtrackCount);
    }
  } // end of WAM.run()

  // runQuery compiles a query given by s into a WAM program, adds it to the program in memory
  // and jumps to the label "query$", starting the execution
  public boolean runQuery(String s) {
    QueryCompiler qc = new QueryCompiler(this);
    reset();
    p.deleteFrom("query$");
    s = s.trim();

    /*************** BEGIN SPECIAL COMMANDS ***************/

    // input "quit" or "exit" means: end the WAM now, dude!
    if ((s.compareTo("quit") == 0) || (s.compareTo("exit") == 0))
      return false;
    if (s.compareTo("clear") == 0) {
      if (GUImode == 0) writeLn("Not in GUI mode.");
                   else response.setText("");
      return true;
    }
    if (s.compareTo("help") == 0) {
      showHelp();  // display some help information
      return true;
    }
    if (s.compareTo("set") == 0) {
      displayInternalVariables();  // show the states of the internal parameters
      return true;
    }
    if (s.compareTo("labels") == 0) {  // show all labels of the current program
      for (int i = 0; i < p.getStatementCount(); i++) {
        String m = p.getStatement(i).getLabel();
        if (m.length() > 0) writeLn(m);
      }
      return true;
    }
    if (s.compareTo("procedures") == 0) {  // show all procedure names of the current program
      for (int i = 0; i < p.getStatementCount(); i++) {
        String m = p.getStatement(i).getLabel();
        if ((m.length() > 0) && (m.indexOf('~') < 0)) writeLn(m);
      }
      return true;
    }
    if (s.compareTo("list") == 0) {  // show the WAM code of the program currently in memory
      if (p.getStatementCount() == 0)
        writeLn("No program in memory.");
      else
        writeLn(p.toString());
      return true;
    }
    if (s.compareTo("new") == 0) {  // clear memory
      p = new Program(this);
      writeLn("Memory cleared.");
      return true;
    }
    if ((s.length() > 4) && (s.substring(0, 4).compareTo("set ") == 0)) {
      s = s.substring(4);  // set an internal parameter's new value
      int i = s.indexOf(' ');
      while (i >= 0) {
        s = s.substring(0, i) + s.substring(i + 1);
        i = s.indexOf(' ');
      }
      i = s.indexOf('=');
      if (i >= 0) {
        String variable = s.substring(0, i);
        String value = s.substring(i + 1);
        setInternalVariable(variable, value);
      }
      else  // if no new value has been specified, display the current
        getInternalVariable(s);
      return true;
    } // end of "set ..." command

    /*************** END SPECIAL COMMANDS ***************/

    Program query = qc.compile(s);

    if (query == null) {  // query could not be compiled
      writeLn("Illegal query.");
      return true;
    }
    else {
      if (debugOn > 1) {  // if in debug mode, display query WAM code
        writeLn("----- BEGIN QUERYCODE -----");
        writeLn(query.toString());
        writeLn("------ END QUERYCODE ------");
      }
      p.addProgram(query);  // add query to program in memory and
      p.updateLabels();  // update the labels for jumping hin und her
    }

    // reset the WAM's registers and jump to label "query$" (the current query, of course)
    programCounter = p.getLabelIndex("query$");
    String answer = "";
    do {
      long ms = System.currentTimeMillis();
      run();

      if (benchmarkOn > 0)  // sometimes, we need extra benchmark information
        writeLn("Total time elapsed: " + (System.currentTimeMillis() - ms) + " ms.");
      writeLn("");

      if (failed) {  // if execution failed, just tell that
        writeLn("Failed.");
        break;
      }

      // if there are any query variables (e.g. in "start(X, Y)", X and Y would be such variables),
      // display their current values and ask the user if he/she wants to see more possible solutions
      if (displayQCount > 0) {
        write("Success: ");
        int cnt = 0;
        for (int i = 0; i < 100; i++)  // yes, we do not allow more than 100 query variables!
          if (displayQValue[i]) {
            cnt++;  // if Q[i] is to be displayed, just do that
            write(((Variable)queryVariables.elementAt(i)).name + " = ");
            write(((Variable)queryVariables.elementAt(i)).toString());
            if (cnt < displayQCount) write(", ");
              else writeLn(".");
          }
      }
      else
        writeLn("Success.");
        // if there are any more choicepoints left, ask the user if they shall be tried
        if (choicePoint != null) {
          if (GUImode == 0) {
            write("More? ([y]es/[n]o) ");
            answer = readLn();
            writeLn("");
          }
          else {
            Dialog dlg = new Dialog(frame, "Decision", true);
            dlg.show();
          }
        }
        else
          break;
//      }
//      else {  // if there are no query variables at all, trying the remaining choicepoints seems senseless
//        writeLn("Success.");
//        break;
//      }
      // if the users decided to see more, show him/her. otherwise: terminate
      if ((answer.compareTo("y") == 0) || (answer.compareTo("yes") == 0))
        backtrack();
    } while ((answer.compareTo("y") == 0) || (answer.compareTo("yes") == 0));
    reset();
    return true;
  } // end of WAM.runQuery(String)

  // the WAM's main loop
  public static void main(String args[]) {
    System.out.println("\nWelcome to Stu's mighty WAM!");
    System.out.println("(December 2001 - February 2002 by Stefan Buettcher)\n");
    System.out.println("Type \"help\" to get some help.\n");
    WAM wam = new WAM(new Program());
    wam.p.owner = wam;
    String s;
    do {
      wam.writeLn("");
      wam.write("QUERY > ");
      s = wam.readLn();
      wam.writeLn("");
    } while ((s != null) && (wam.runQuery(s)));
    wam.writeLn("Goodbye!"); wam.writeLn("");
  } // end of WAM.main(String[])

} // end of class WAM

