<?php

/* * ****************************************************************************
 * Warren's Abstract Machine  -  Implementation by Stefan Buettcher
 *
 * developed:   December 2001 until February 2002
 *
 * Statement.java contains the class Statement, representing a single line of
 * WAM code, e.g. "true: proceed".
 * **************************************************************************** */

// Statement class implements WAM code statements
// a statement looks like this:
// [label:] operator operand1 [operand2 [operand3]]
// label, op2 and op3 may be omitted. label is needed for jumps (calls)
class Statement {
    const opAllocate = 1;
    const opBigger = 2;
    const opCall = 3;
    const opCreateVariable = 4;
    const opCut = 5;
    const opDeallocate = 6;
    const opGetConstant = 7;
    const opGetValue = 8;
    const opGetVariable = 9;
    const opHalt = 10;
    const opIs = 11;
    const opGetLevel = 12;
    const opNoOp = 13;
    const opProceed = 14;
    const opPutConstant = 15;
    const opPutValue = 16;
    const opPutVariable = 17;
    const opRetryMeElse = 18;
    const opSmaller = 19;
    const opTrustMe = 20;
    const opTryMeElse = 21;
    const opUnifyList = 22;
    const opUnifyStruc = 23;
    const opUnequal = 24;
    const opUnifyVariable = 25;
    const opBiggerEq = 27;
    const opSmallerEq = 28;
    const opNotCall = 29;

    const callWrite = -10;
    const callWriteLn = -11;
    const callNewLine = -12;
    const callConsult = -13;
    const callReconsult = -14;
    const callLoad = -15;
    const callAssert = -16;
    const callRetractOne = -17;
    const callRetractAll = -18;
    const callIsInteger = -19;
    const callIsAtom = -20;
    const callIsBound = -21;
    const callReadLn = -22;
    const callCall = -23;

    private $label;      // the label (used for jumping hin und her)
    private $fonction;   // the operator
    private $args = array();       // the operands vector
    public $operator;       // same as function, but as integer (performance!)
    public $jump;           // for faster jumping: if operand = call, then lookup target line number at startup
    public $arg1, $arg2, $arg3;   // for faster argument access from WAM

    // creates a new statement with one operand/argument

    public function __construct($aLabel, $aFunction, $anArgument, $arg2 = '', $arg3 = '') {
        $this->label = trim($aLabel);
        $this->fonction = trim($aFunction);
        $this->args = array();
        $this->args[] = $anArgument;
        $this->args[] = $arg2;
        if (strlen($arg3)) {
            $rest = explode(' ', $args3);
            foreach ($rest as $item)
                $this->args[] = $item;
        } else
            $this->args[] = "";
        $this->doCommonStuff();
    }

// end of Statement.Statement(String, String, String)

    private function doCommonStuff() {
        $this->jump = -1;
        $this->operator = $this->functionToInt($this->fonction);
        $this->arg1 = $this->args[0];
        $this->arg2 = $this->args[1];
        $this->arg3 = $this->args[2];
    }

// end of Statement.doCommonStuff()

    public function functionToInt($fonction) {
        $mappingOp = array(
            "allocate" => self::opAllocate,
            "bigger" => self::opBigger,
            "biggereq" => self::opBiggerEq,
            "call" => self::opCall,
            "not_call" => self::opNotCall,
            "create_variable" => self::opCreateVariable,
            "cut" => self::opCut,
            "deallocate" => self::opDeallocate,
            "get_constant" => self::opGetConstant,
            "get_value" => self::opGetValue,
            "get_variable" => self::opGetVariable,
            "get_level" => self::opGetLevel,
            "halt" => self::opHalt,
            "is" => self::opIs,
            "proceed" => self::opProceed,
            "put_constant" => self::opPutConstant,
            "put_value" => self::opPutValue,
            "put_variable" => self::opPutVariable,
            "retry_me_else" => self::opRetryMeElse,
            "trust_me" => self::opTrustMe,
            "try_me_else" => self::opTryMeElse,
            "unequal" => self::opUnequal,
            "unify_list" => self::opUnifyList,
            "unify_struc" => self::opUnifyStruc,
            "unify_variable" => self::opUnifyVariable,
            "smaller" => self::opSmaller,
            "smallereq" => self::opSmallerEq,
            "nop" => self::opNoOp,
            "noop" => self::opNoOp
        );

        return array_key_exists($fonction, $mappingOp) ? $mappingOp[$fonction] : -1;
    }

// end of Statement.functionToInt()
    // returns the label name of the statement
    public function getLabel() {
        return $this->label;
    }

// end of Statement.getLabel()
    // sets the label name to newLabel
    public function setLabel($newLabel) {
        $this->label = $newLabel;
    }

// end of Statement.setLabel(String)
    // returns the operator string, e.g. "get_variable"
    public function getFunction() {
        return $this->fonction;
    }

// end of Statement.getFunction()

    public function setFunction($newFunction) {
        $this->fonction = $newFunction;
        $this->operator = $this->functionToInt($this->fonction);
    }

// end of Statement.setFunction
    // returns the operand strings vector
    public function getArgs() {
        return $this->args;
    }

// end of Statement.getArgs()
    // for code dumping: print the statement: "label: operator op1 op2"
    public function __toString() {
        if ($this->label == ";")
            return "; " . $this->fonction;
        // String result;
        if (strlen($this->label) > 0) {
            $result = $this->label . ": ";
            $result = str_pad($result, 14, ' ', STR_PAD_RIGHT);
        }
        else
            $result = str_repeat(" ", 14);
        $result .= $this->fonction;
        foreach ($this->args as $a) {
            if (false !== strpos($a, ' '))
                $result .= " " . $a;
            else
                $result .= " '$a'";
        }
        if ($this->jump >= 0)
            $result .= " (" . $this->jump . ")";
        return $result;
    }

// end of Statement.toString()

    public function toString2() {
        $result = $this->fonction;
        foreach ($this->args as $item)
            $result .= " " . $item;
        return trim($result);
    }

// end of Statement.toString2()
    // where do you have to go today?
    public function setJump($anAddress) {
        $this->jump = $anAddress;
    }

// end of Statement.setJump(int)
    // where do you want to go today?
    public function getJump() {
        return $this->jump;
    }

// end of Statement.getJump()
}

// end of class Statement
