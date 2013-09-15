<?php

namespace Trismegiste\WamBundle\Prolog\Inner;

use Trismegiste\WamBundle\Prolog\WAM;
use Trismegiste\WamBundle\Prolog\Solution;

/**
 * This class is an Y(n) in the W.A.M
 */
class Variable
{

    public $tag;            // UNB, REF, CON, LIS or STR
    public $value;       // variable's content in case of CON
    public /* Variable */ $reference; // variable's content in case of REF
    public $name;        // name of variable, e.g. when it's a query variable
    public /* Variable */ $head, $tail;  // list/struc stuff
    public /* ChoicePoint */ $cutLevel;  // f�r the cut and get_level instructions

    public function __construct($param1 = null, $param2 = null)
    {
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
            throw new \InvalidArgumentException("Construct Variable dies");
        }
    }

    // constructor for creating a new, unbound variable without a name
    protected function constructVariable1()
    {
        $this->tag = WAM::REF;
        $this->reference = $this;
    }

    // constructor for creating a new, unbound variable with a name
    protected function constructVariable2($aName)
    {
        $this->tag = WAM::REF;
        $this->reference = $this;
        $this->name = $aName;
    }

    // constructor for creating a new variable and binding it to a constant
    protected function constructVariable3($aName, $s)
    {
        $this->tag = WAM::CON;
        $this->value = $s;
        $this->name = $aName;
    }

    // constructor for creating a new variable and unifying it with another
    protected function constructVariable4($aName, Variable $v)
    {
        $this->tag = WAM::REF;
        $this->reference = $v;
        $this->name = $aName;
    }

    // copyFrom-constructor
    protected function constructVariable5(Variable $v)
    {
        $this->copyFrom($v);
    }

    // sets internal components to that of source
    public function copyFrom(Variable $source)
    {
        $this->tag = $source->tag;
        if ($this->tag == WAM::REF)
            $this->reference = $source->reference;
        elseif ($this->tag == WAM::CON)
            $this->value = (string) $source->value;
        else {
            $this->head = $source->head;
            $this->tail = $source->tail;
        }
    }

    // dereferencing: if this variable points to another var, then return that dereferenced
    public function deref()
    {
        if (($this->tag == WAM::REF) && ($this->reference !== $this)) {
            $result = $this->reference;
            while (($result->tag == WAM::REF) && ($result->reference !== $result))
                $result = $result->reference;
            return $result;
        }
        else
            return $this;
    }

    // returns a string in the form NAME = VALUE, representing the variable's value
    public function __toString()
    {
        if (($this->tag == WAM::REF) && ($this->reference === $this))
            return "_"; // "(unbound variable)";
        if ($this->tag == WAM::CON) {
//        if (value.indexOf(' ') < 0) {
            if ((strlen($this->value) > 2) && (strpos($this->value, ".0") === (strlen($this->value) - 2)))
                return substr($this->value, 0, strlen($this->value) - 2);
            else
                return $this->value;
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

    public function toString2()
    {
        if ($this->tag == WAM::LIS) {
            $result = $this->head->__toString();
            if (($this->tail !== null) && ($this->tail->tag != WAM::CON))
                $result .= ", " . $this->tail->toString2();
            return $result;
        }
        return "";
    }

    // returns a string in the form NAME = VALUE, representing the variable's value
    public function toArrayValue()
    {
        switch ($this->tag) {
            case WAM::REF:
                if ($this->reference === $this) {
                    return '_';
                }
                return $this->deref()->toArrayValue();
                break;

            case WAM::CON:
                if ((strlen($this->value) > 2) && (strpos($this->value, ".0") === (strlen($this->value) - 2)))
                    return substr($this->value, 0, strlen($this->value) - 2);
                else
                    return $this->value;
                break;

            case WAM::LIS:
                $result = new Solution\Lis();
                $this->pushTail($result);
                return $result;
                break;

            case WAM::STR:
                $result = new Solution\Str($this->head->toArrayalue());
                $this->tail->pushTail($result);
                return $result;
                break;
            default : return "";
        }
    }

    public function pushTail(\ArrayAccess $lst)
    {
        if ($this->tag == WAM::LIS) {
            $lst[] = $this->head->toArrayValue();
            if (($this->tail !== null) && ($this->tail->tag != WAM::CON))
                $this->tail->pushTail($lst);
        }
    }

}
