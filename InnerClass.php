<?php

class Variable {

    public $tag;            // UNB, REF, CON, LIS or STR
    public $value;       // variable's content in case of CON
    public /* Variable */ $reference; // variable's content in case of REF
    public $name;        // name of variable, e.g. when it's a query variable
    public /* Variable */ $head, $tail;  // list/struc stuff
    public /* ChoicePoint */ $cutLevel;  // f�r the cut and get_level instructions

    public function __construct($param1 = null, $param2 = null) {
        if (is_null($param1) && is_null($param2)) {
            $this->constructVariable1();
        } elseif (($param1 instanceof Variable) && is_null($param2)) {
            $this->constructVariable5($param1);
        } elseif (is_string($param1)) {
            if (is_null($param2)) {
                $this->constructVariable2($param1);
            } elseif ($param2 instanceof Variable) {
                $this->constructVariable4($param1, $param2);
            } else {
                $this->constructVariable3($param1, $param2);
            }
        } else {
            throw new InvalidArgumentException("Construct Variable dies");
        }
    }

    // constructor for creating a new, unbound variable without a name
    protected function constructVariable1() {
        $this->tag = WAM::REF;
        $this->reference = $this;
    }

    // constructor for creating a new, unbound variable with a name
    protected function constructVariable2($aName) {
        $this->tag = WAM::REF;
        $this->reference = $this;
        $this->name = $aName;
    }

    // constructor for creating a new variable and binding it to a constant
    protected function constructVariable3($aName, $s) {
        $this->tag = WAM::CON;
        $this->value = $s;
        $this->name = $aName;
    }

    // constructor for creating a new variable and unifying it with another
    protected function constructVariable4($aName, Variable $v) {
        $this->tag = WAM::REF;
        $this->reference = $v;
        $this->name = $aName;
    }

    // copyFrom-constructor
    protected function constructVariable5(Variable $v) {
        $this->copyFrom($v);
    }

    // sets internal components to that of source
    public function copyFrom(Variable $source) {
        $this->tag = $source->tag;
        if ($this->tag == WAM::REF)
            $this->reference = $source->reference;
        else if ($this->tag == WAM::CON)
            $this->value = $source->value;
        else {
            $this->head = $source->head;
            $this->tail = $source->tail;
        }
    }

    // dereferencing: if this variable points to another var, then return that dereferenced
    public function deref() {
        if (($this->tag == WAM::REF) && ($this->reference != $this)) {
            $result = $this->reference;
            while (($result->tag == WAM::REF) && ($result->reference != $result))
                $result = $result->reference;
            return $result;
        }
        else
            return $this;
    }

// end of Variable.deref()
    // returns a string in the form NAME = VALUE, representing the variable's value
    public function __toString() {
        if (($this->tag == WAM::REF) && ($this->reference == $this))
            return "_"; // "(unbound variable)";
        if ($this->tag == WAM::CON) {
//        if (value.indexOf(' ') < 0) {
            if ((strlen($this->value) > 2) && (strpos($this->value, ".0") === (strlen($this->value) - 2)))
                return substr($this->value, 0, strlen($this->value) - 2);
            else
                return $this->value;

//        }
//        else
//          return("'" + value + "'");
        }
        if ($this->tag == WAM::LIS)
            return "[" . $this->toString2() . "]";
        if ($this->tag == WAM::STR) {
            $result = $this->head->__toString() . "(" . $this->tail->toString2() . ")";
            return $result;
        }
        if ($this->tag == WAM::REF)
            return $this->deref()->__toString();
        return "";
    }

// end of Variable.toString()

    public function toString2() {
        if ($this->tag == WAM::LIS) {
            $result = $this->head->__toString();
            if (($this->tail != null) && ($this->tail->tag != WAM::CON))
                $result .= ", " . $this->tail->toString2();
            return $result;
        }
        return "";
    }

// end of Variable.toString2()
}

// end of class Variable
// class ChoicePoint implements the choice point concept, as presented by Ait-Kaci
class ChoicePoint {

    public $arguments = array();             // the Ai variables
    public /* Environment */ $lastEnviron;      // current environment when creating the choicepoint
    public $returnAddress;            // current continuation pointer (cp)
    public /* ChoicePoint */ $lastCP;           // last ChoicePoint on stack
    public /* ChoicePoint */ $cutPoint;         // copy of B0
    public $nextClause;               // current instruction pointer + 1
    public $trailPointer;             // current trail pointer

    // constructor gets A (argument variables vector), trailPtr (trail pointer) and
    // anAddress (current return address / continuation pointer)

    public function __construct($a, $trailPtr, $anAddress) {
        $this->arguments = array();
        $this->lastEnviron = null;
        $this->lastCP = null;
        $this->returnAddress = $anAddress;
        foreach ($a as $item)
            $this->arguments[] = new Variable($item);
        $this->trailPointer = $trailPtr;
    }

// end of ChoicePoint.ChoicePoint
}

// end of class ChoicePoint
// class Environment for storing local variables that must not be overridden
class Environment {

    public $variables = array();
    public /* Environment */ $lastEnviron;
    public $returnAddress;

    // constructor gets the current return address (continuation pointer) and a pointer to the previous environment on stack
    public function __construct($anAddress, $anEnv) {
        $this->lastEnviron = $anEnv;
        $this->returnAddress = $anAddress;
        $this->variables = array();
    }

// end of Environment.Environment(int, Environment)
}

// end of class Environment
// Trail implements the WAM's trail (undo-list for bindings performed)
class Trail {

    private $contents = array();

    public function __construct() {
        $this->contents = array();
    }

    public function getLength() {
        return count($this->contents);
    }

    public function setLength($length) {
        if ($length > count($this->contents))
            $this->contents = array_pad($this->contents, $length, null);
        else
            throw new InvalidArgumentException("There is reduction");
    }

    public function addEntry(Variable $v) {
        $this->contents[] = $v;
    }

    public function getEntry($index) {
        return $this->contents[$index];
    }

    public function undo($index) {
        $v = $this->contents[$index];
        if ($v != null) {
            if ($v->tag == WAM::ASSERT)
                retract($v->value);
            else {
                $v->tag = WAM::REF;
                $v->reference = $v;
            }
        }
    }

}

// end of class Trail
