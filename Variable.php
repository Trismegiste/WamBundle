<?php
/******************************************************************************
 * Warren's Abstract Machine  -  Implementation by Stefan Buettcher
 *
 * developed:   December 2001 / January 2002
 *
 * Variable.java contains the class Variable, implementing the internal
 * management of variables as references or bound to lists or single constants.
 ******************************************************************************/

class Variable {
  const REF = 0;  // variable contains reference, i.e. has been unified/bound
  const CON = 1;  // variable has been bound to a constant
  const LIS = 2;  // variable contains list
  const STR = 3;  // variable contains structure / term

  public $tag;            // REF, CON, LIS or STR
  public $value;       // variable's content in case of CON
  public $reference; // variable's content in case of REF
  public $name;        // name of variable, e.g. when it's a query variable
  public $head, $tail;  // Variable list/struc stuff

  // constructor for creating a new, unbound variable
  public function __construct($aName) {
    $this->tag = self::REF;
    $this->value = "_";
    $this->reference = $this;   // unbound is expressed by self-reference
    $this->head = $this;
    $this->tail = $this;
    $this->name = $aName;
  }

  // constructor for creating a new variable and binding it to a constant
  public function __construct2($aName, $s) {
    $this->tag = self::CON;
    $this->value = s;
    $this->reference = $this;
    $this->name = $aName;
  }

  // constructor for creating a new variable and unifying it with another
  public function __construct3($aName, $v) {
    $this->tag = self::REF;
    $this->value = $v->value;
    $this->reference = $v;
    $this->name = $aName;
  }

  // sets internal components to that of source
  public function copyFrom($source) {
    $this->tag = $source->tag;
    $this->reference = $source->reference;
    $this->value = $source->value;
    $this->head = $source->head;   // __clone ?????
    $this->tail = $source->tail;
  }

  // dereferencing: if this variable points to another var, then return that dereferenced
  public function deref() {
    if (($this->tag == self::REF) && ($this->reference != $this))
      return $this->reference->deref();
    else
      return $this;
  }

  public function setReference($v) {
    $this->tag = self::REF;
    $this->reference = $v;
  }

  public function setValue($v) {
    $this->tag = self::CON;
    $this->value = $v;
  }

  public function getValue() {
    return $this->value;
  }

  public function setHead($v) {
    $this->head = $v;
  }

  public function setTail($v) {
    $this->tail = $v;
  }

  // returns a string in the form NAME = VALUE, representing the variable's value
  public function __toString() {
    if ($this->tag == self::CON) {
      if ($this->value.indexOf(' ') < 0) {  // <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        if (($this->value.length() > 2) && ($this->value.indexOf(".0") == $this->value.length() - 2))
          return $this->value.substring(0, $this->value.length() - 2);
        else
          return $this->value;
      }
      else
        return("'" + $this->value + "'");
    }
    if ($this->tag == LIS)
      return "[" + toString2() + "]";
    if ($this->tag == STR) {
      $result = $this->head.toString() + "(" + $this->tail.toString2() + ")";
      return result;
    }
    if ($this->tag == REF) {
      if ($this->reference == $this)
        return "_"; // "(unbound variable)";
      else
        return deref().toString();
    }
    return "";
  } // end of Variable.toString()

  public function toString2() {
    if ($this->tag == LIS) {
      $result = $this->head->__toString();
      if (($this->tail != null) && ($this->tail->tag != $this->tail.CON))  // ????
        $result += ", " + $this->tail->toString2();
      return result;
    }
    return "";
  } // end of Variable.toString2()

} // end of class Variable
