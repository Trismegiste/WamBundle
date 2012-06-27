/******************************************************************************
 * Warren's Abstract Machine  -  Implementation by Stefan Buettcher
 *
 * developed:   December 2001 until February 2002
 *
 * Statement.java contains the class Statement, representing a single line of
 * WAM code, e.g. "true: proceed".
 ******************************************************************************/

import java.util.*;

// Statement class implements WAM code statements
// a statement looks like this:
// [label:] operator operand1 [operand2 [operand3]]
// label, op2 and op3 may be omitted. label is needed for jumps (calls)
public class Statement {
  public static final int opAllocate        =  1;
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

  private String label;      // the label (used for jumping hin und her)
  private String function;   // the operator
  private Vector args;       // the operands vector
  public int operator;       // same as function, but as integer (performance!)
  public int jump;           // for faster jumping: if operand = call, then lookup target line number at startup
  public String arg1, arg2, arg3;   // for faster argument access from WAM

  // creates a new statement with one operand/argument
  public Statement(String aLabel, String aFunction, String anArgument) {
    label = aLabel.trim();
    function = aFunction.trim();
    args = new Vector();
    args.addElement(anArgument);
    args.addElement("");
    args.addElement("");
    doCommonStuff();
  } // end of Statement.Statement(String, String, String)

  // creates a new statement with one operand/argument
  public Statement(String aLabel, String aFunction, String arg1, String arg2) {
    label = aLabel.trim();
    function = aFunction.trim();
    args = new Vector();
    args.addElement(arg1);
    args.addElement(arg2);
    args.addElement("");
    doCommonStuff();
  } // end of Statement.Statement(String, String, String, String)

  // creates a new statement with more than two ops
  public Statement(String aLabel, String aFunction, String arg1, String arg2, String arg3) {
    int i;
    label = aLabel.trim();
    function = aFunction.trim();
    args = new Vector();
    args.addElement(arg1);
    args.addElement(arg2);
    arg3 = arg3;
    while ((i = arg3.indexOf(" ")) > 0) {
      args.addElement(arg3.substring(0, i));
      arg3 = arg3.substring(i + 1);
    }
    if (arg3.length() > 0)
      args.addElement(arg3);
    if (args.size() < 2)
      args.addElement("");
    doCommonStuff();
  } // end of Statement.Statement(String, String, String, String, String)

  private void doCommonStuff() {
    jump = -1;
    operator = functionToInt(function);
    arg1 = (String)args.elementAt(0);
    arg2 = (String)args.elementAt(1);
    arg3 = (String)args.elementAt(2);
  } // end of Statement.doCommonStuff()

  public int functionToInt(String function) {
    if (function.compareTo("allocate") == 0)          return opAllocate;
    if (function.compareTo("bigger") == 0)            return opBigger;
    if (function.compareTo("biggereq") == 0)          return opBiggerEq;
    if (function.compareTo("call") == 0)              return opCall;
    if (function.compareTo("not_call") == 0)          return opNotCall;
    if (function.compareTo("create_variable") == 0)   return opCreateVariable;
    if (function.compareTo("cut") == 0)               return opCut;
    if (function.compareTo("deallocate") == 0)        return opDeallocate;
    if (function.compareTo("get_constant") == 0)      return opGetConstant;
    if (function.compareTo("get_value") == 0)         return opGetValue;
    if (function.compareTo("get_variable") == 0)      return opGetVariable;
    if (function.compareTo("get_level") == 0)         return opGetLevel;
    if (function.compareTo("halt") == 0)              return opHalt;
    if (function.compareTo("is") == 0)                return opIs;
    if (function.compareTo("proceed") == 0)           return opProceed;
    if (function.compareTo("put_constant") == 0)      return opPutConstant;
    if (function.compareTo("put_value") == 0)         return opPutValue;
    if (function.compareTo("put_variable") == 0)      return opPutVariable;
    if (function.compareTo("retry_me_else") == 0)     return opRetryMeElse;
    if (function.compareTo("trust_me") == 0)          return opTrustMe;
    if (function.compareTo("try_me_else") == 0)       return opTryMeElse;
    if (function.compareTo("unequal") == 0)           return opUnequal;
    if (function.compareTo("unify_list") == 0)        return opUnifyList;
    if (function.compareTo("unify_struc") == 0)       return opUnifyStruc;
    if (function.compareTo("unify_variable") == 0)    return opUnifyVariable;
    if (function.compareTo("smaller") == 0)           return opSmaller;
    if (function.compareTo("smallereq") == 0)         return opSmallerEq;
    if ((function.compareTo("nop") == 0) || (function.compareTo("noop") == 0)) return opNoOp;
    return -1;
  } // end of Statement.functionToInt()

  // returns the label name of the statement
  public String getLabel() {
    return label;
  } // end of Statement.getLabel()

  // sets the label name to newLabel
  public void setLabel(String newLabel) {
    label = newLabel;
  } // end of Statement.setLabel(String)

  // returns the operator string, e.g. "get_variable"
  public String getFunction() {
    return function;
  } // end of Statement.getFunction()

  public void setFunction(String newFunction) {
    function = newFunction;
    operator = functionToInt(function);
  } // end of Statement.setFunction

  // returns the operand strings vector
  public Vector getArgs() {
    return args;
  } // end of Statement.getArgs()

  // for code dumping: print the statement: "label: operator op1 op2"
  public String toString() {
    if (label.compareTo(";") == 0)
      return "; " + function;
    String result;
    if (label.length() > 0) {
      result = label + ": ";
      for (int i = 1; i <= 12 - label.length(); i++)
        result += " ";
    }
    else
      result = "              ";
    result += function;
    for (int i = 0; i < args.size(); i++) {
      String a = (String)args.elementAt(i);
      if (a.indexOf(' ') < 0)
        result += " " + a;
      else
        result += " '" + a + "'";
    }
    if (jump >= 0)
      result += " (" + jump + ")";
    return result;
  } // end of Statement.toString()

  public String toString2() {
    String result = function;
    for (int i = 0; i < args.size(); i++)
      result += " " + args.elementAt(i);
    return result.trim();
  } // end of Statement.toString2()

  // where do you have to go today?
  public void setJump(int anAddress) {
    jump = anAddress;
  } // end of Statement.setJump(int)

  // where do you want to go today?
  public int getJump() {
    return jump;
  } // end of Statement.getJump()

} // end of class Statement
