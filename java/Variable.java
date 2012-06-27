/******************************************************************************
 * Warren's Abstract Machine  -  Implementation by Stefan Buettcher
 *
 * developed:   December 2001 / January 2002
 *
 * Variable.java contains the class Variable, implementing the internal
 * management of variables as references or bound to lists or single constants.
 ******************************************************************************/

public class Variable {
  public final int REF = 0;  // variable contains reference, i.e. has been unified/bound
  public final int CON = 1;  // variable has been bound to a constant
  public final int LIS = 2;  // variable contains list
  public final int STR = 3;  // variable contains structure / term

  public int tag;            // REF, CON, LIS or STR
  public String value;       // variable's content in case of CON
  public Variable reference; // variable's content in case of REF
  public String name;        // name of variable, e.g. when it's a query variable
  public Variable head, tail;  // list/struc stuff

  // constructor for creating a new, unbound variable
  public Variable(String aName) {
    tag = REF;
    value = "_";
    reference = this;   // unbound is expressed by self-reference
    head = this; tail = this;
    name = aName;
  }

  // constructor for creating a new variable and binding it to a constant
  public Variable(String aName, String s) {
    tag = CON;
    value = s;
    reference = this;
    name = aName;
  }

  // constructor for creating a new variable and unifying it with another
  public Variable(String aName, Variable v) {
    tag = REF;
    value = v.value;
    reference = v;
    name = aName;
  }

  // sets internal components to that of source
  public void copyFrom(Variable source) {
    tag = source.tag;
    reference = source.reference;
    value = source.value;
    head = source.head;
    tail = source.tail;
  }

  // dereferencing: if this variable points to another var, then return that dereferenced
  public Variable deref() {
    if ((tag == REF) && (reference != this))
      return reference.deref();
    else
      return this;
  }

  public void setReference(Variable v) {
    tag = REF;
    reference = v;
  }

  public void setValue(String v) {
    tag = CON;
    value = v;
  }

  public String getValue() {
    return value;
  }

  public void setHead(Variable v) {
    head = v;
  }

  public void setTail(Variable v) {
    tail = v;
  }

  // returns a string in the form NAME = VALUE, representing the variable's value
  public String toString() {
    if (tag == CON) {
      if (value.indexOf(' ') < 0) {
        if ((value.length() > 2) && (value.indexOf(".0") == value.length() - 2))
          return value.substring(0, value.length() - 2);
        else
          return value;
      }
      else
        return("'" + value + "'");
    }
    if (tag == LIS)
      return "[" + toString2() + "]";
    if (tag == STR) {
      String result = head.toString() + "(" + tail.toString2() + ")";
      return result;
    }
    if (tag == REF) {
      if (reference == this)
        return "_"; // "(unbound variable)";
      else
        return deref().toString();
    }
    return "";
  } // end of Variable.toString()

  public String toString2() {
    if (tag == LIS) {
      String result = head.toString();
      if ((tail != null) && (tail.tag != tail.CON))
        result += ", " + tail.toString2();
      return result;
    }
    return "";
  } // end of Variable.toString2()

} // end of class Variable
