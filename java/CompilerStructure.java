/******************************************************************************
 * Warren's Abstract Machine  -  Implementation by Stefan Buettcher
 *
 * developed:   December 2001 until February 2002
 *
 * CompilerStructure.java contains the CompilerStructure class, which is needed
 * for transforming the input vector (Prolog program) to the output Program
 * (WAM code) via a certain program structure graph.
 ******************************************************************************/

// Each instance of CompilerStructure represents a node in the program graph
// of the original Prolog program. Every node is of a certain type (see constants
// below).
public class CompilerStructure {
  public final int NO_TYPE     = -1;   // no type or unknown type
  public final int QUERY       = +0;   // this is a query (list), composed of
                                       // a set of conditions
  public final int TERM        = +1;   // this is a term, e.g. "s(a, Y)"
  public final int LIST        = +2;   // this is a list, e.g. "s", "a, X, c"
  public final int CONSTANT    = +3;   // this is a constant, e.g. "a", "b", "z"
  public final int VARIABLE    = +4;   // this is a variable, e.g. "A", "B", "Z"
  public final int PREDICATE   = +5;   // this is a predicate, e.g. "father", "length"
  public final int CLAUSE      = +6;   // this is a clause, composed of a HEAD (this.head) and a BODY (this.tail)
  public final int PROGRAM     = +7;   // this is a whole Prolog program, i.e. a list of PROCEDUREs
  public final int HEAD        = +8;   // this is a PROCEDURE's head, composed of a PREDICATE name and a parameter LIST
  public final int BODY        = +9;   // this is a PROCEDURE's body, i.e. a list of CONDITIONs
  public final int CALL        = 10;   // this is a condition, e.g. "father(X, Y)", composed of the PREDICATE name
                                       // and a LIST of calling arguments
  public final int NOT_CALL    = 11;   // negated call, invokes a new process and returns true upon failure
  public final int UNIFICATION = 12;   // this is a unification of the form "X = Y" (args in head and tail).
  public final int ASSIGNMENT  = 13;   // this is an assignment of the form "X = 1 + 3",
                                       // where X can be found in head, + in tail.value and 1 (3) in tail.head (tail.tail)
  public final int EXPRESSION  = 14;   // this is an arithmetic expression, to be used in ASSIGNMENTs,
                                       // in "X = 1 + 3", "1 + 3" would be the expression, with + as value,
                                       // 1 as (constant) head and 3 as (constant) tail
  public final int COMPARISON  = 15;   // something like "X < 5" or "Z > Y"
  public final int STRUCTURE   = 16;   // this is a structure, e.g. "s(x, y, X)", "auto(mobil, nix_is)"
  public final int CUT         = 17;   // a cut instruction ("!")

  public int type;                     // the type of the node, as explained above
  public CompilerStructure head, tail; // sub-nodes in case of non-trivial nodes (lists, queries, ...)
  public String value;                 // the value, e.g. the variable's name in case of type == VARIABLE

  // create a new structure of unknown type
  public CompilerStructure() {
    type = NO_TYPE;
    head = null;
    tail = null;
    value = "";
  } // end of CompilerStructure.CompilerStructure()

  // create a new, trivial structure of type aType with initial value aValue
  public CompilerStructure(int aType, String aValue) {
    type = aType;
    value = aValue;
  } // end of CompilerStructure.CompilerStructure(int, String)

  // return the string that shall be used to display this node on the screen
  public String toString() {
    if (type == NO_TYPE)
      return "[no type]";
    else if ((type == TERM) || (type == QUERY)) {
      if (tail == null)
        return head.toString();
      else
        return head.toString() + "(" + tail.toString() + ")";
    }
    else if (type == PREDICATE)
      return value;
    else if (type == CONSTANT)
      return "const " + value;
    else if (type == VARIABLE)
      return "var " + value;
    else if (type == PROGRAM) {
      if (tail == null)
        return "\n" + head.toString();
      else
        return "\n" + head.toString() + tail.toString();
    }
    else if (type == CLAUSE) {
      if (tail == null)
        return head.toString() + ".";
      else
        return head.toString() + " :-\n" + tail.toString() + ".";
    }
    else if (type == HEAD) {
      if (tail == null)
        return head.toString();
      else
        return head.toString() + "(" + tail.toString() + ")";
    }
    else if (type == BODY) {
      if (tail == null)
        return "  " + head.toString();
      else
        return "  " + head.toString() + ",\n" + tail.toString();
    }
    else if (type == CALL) {
      if (tail == null)
        return head.toString();
      else
        return head.toString() + "(" + tail.toString() + ")";
    }
    else if (type == NOT_CALL) {
      if (tail == null)
        return "not " + head.toString();
      else
        return "not " + head.toString() + "(" + tail.toString() + ")";
    }
    else if (type == COMPARISON) {
      return head.toString() + " " + value + " " + tail.toString();
    }
    else if (type == LIST) {
      if (head == null)
        return "[]";
      else {
        if (tail == null)
          return head.toString();
        else
          return head.toString() + ", " + tail.toString();
      }
    }
    else if (type == STRUCTURE) {
      if (tail == null)
        return head.toString();
      else
        return head.toString() + "(" + tail.toString() + ")";
    }
    else
      return "[unknown type]";
  } // end of CompilerStructure.toString()

} // end of class CompilerStructure
