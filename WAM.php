<?php

/* * *****************************************************************************
 * Warren's Abstract Machine  -  Implementation by Stefan Buettcher
 *
 * developed:   December 2001 until February 2002
 *
 * WAM.java contains the actual WAM and the additional structures ChoicePoint,
 * Environment and Trail
 * **************************************************************************** */

// class WAM is the core and contains the essential functions of the WAM
class WAM
{

    const UNB = 0;  // variable-related constants:
    const REF = 1;  // tag == REF means this variable is a reference
    const CON = 2;  // this one has been bound to an immediate constant
    const LIS = 3;  // is a list
    const STR = 4;  // is a structure
    const ASSERT = 9;  // this variable is no real variable but only used for trailing assert operations
    const opAllocate = 1;   // Statement constants, see there
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

    // internal parameters, accessible by using the "set" command
    public $debugOn = 1;   // display debug information?
    private $benchmarkOn = 0;   // show benchmark information?
    private $maxOpCount = 50000000;  // artificial stack overflow limit
    public $opCount, $backtrackCount;
    private /* Program */ $p;         // the program(s) loaded into memory
    private /* Trail */ $trail;       // undo-list (WAM trail)
    private $failed;    // set to true upon an unsuccessful binding operation
    protected $displayQValue = array(); //new boolean[100];   // which Query-Variables do have to displayed upon success?
    protected $displayQCount = 0;     // how many of them?
    // the WAM's register set
    private $queryVariables = array(); // query variables, to be accessed by Q1, Q2, and so on
    private $programCounter = 0; // program counter
    private $continuationPointer = 0; // continuation pointer
    private $choicePoint = null; // last choicepoint on stack
    private $cutPoint = null; // current choicepoint for cut instruction
    private $env = null; // last environment on stack
    private $arguments = array();      // argument registers

    // in case we want to use the WAM inside our GUI
    //public TextArea response = null;   // this is the memo box all the output is written into
    //public Frame frame = null;
    //public int GUImode = 0;    // 0 means: text mode, 1 means: GUI mode
    // creates a new WAM with program data initialized to aProgram

    public function __construct(Program $aProgram)
    {
        $this->p = $aProgram;
        $this->reset();
    }

// end of WAM.WAM(Program)
    // resets sets all WAM parameters to their initial values
    private function reset()
    {
        $this->arguments = array();  // no argument registers so far
        $this->arguments[] = new Variable();
        $this->env = new Environment(999999999, null);  // empty environment
        $this->continuationPointer = -1;  // no continuation point
        $this->trail = new Trail();
        $this->queryVariables = array();
        $this->displayQCount = 0;
        $this->displayQValue = array_fill(0, 100, false);
        $this->choicePoint = null;
        $this->cutPoint = null;
    }

// end of WAM.reset()
    // reads a String line from standard input
    private function readLn()
    {
        try {
            $handle = fopen("php://stdin", "r");
            $line = fgets($handle);

            return trim($line);
        } catch (Exception $e) {

            return "";
        }
    }

    // end of WAM.readLn()
    // displays a string
    public function write($s)
    {
        echo $s;
    }

// end of WAM.write(String)
    // displays a string followed by CRLF
    public function writeLn($s)
    {
        echo $s . "\n";
    }

// end of WAM.writeLn(String)
    // displays a debug information line
    public function debug($s, $debugLevel)
    {
        if ($debugLevel < 0) {
            if ($this->benchmarkOn > 0)
                $this->writeLn($s);
        }
        else
        if ($this->debugOn >= $debugLevel)
            $this->writeLn($s);
    }

// end of WAM.debug(String, int)
    // formats an integer to a string
    private function int2FormatStr($i)
    {
        return str_pad($i, 4, '0', STR_PAD_LEFT);
    }

// end of WAM.int2FormatStr(int)
    // displays the values of all internal parameters that can be modyfied using the "set" command
    private function displayInternalVariables()
    {
        $this->getInternalVariable("autostop");
        $this->getInternalVariable("benchmark");
        $this->getInternalVariable("debug");
    }

// end of WAM.displayInternalVariables()
    // sets the internal parameter specified by variable to a new value
    private function setInternalVariable($variable, $value)
    {
        try {
            if ($variable === "autostop")
                $this->maxOpCount = $this->parseInt($value);
            if ($variable === "benchmark")
                $this->benchmarkOn = $this->parseInt($value);
            if ($variable === "debug")
                $this->debugOn = $this->parseInt($value);
            $this->getInternalVariable($variable);
        } catch (Exception $e) {
            $this->writeLn("An error occurred. Illegal query.");
        }
    }

// end of WAM.setInternalVariable(String, String)
    // displays the value of the internal parameter specified by variable
    private function getInternalVariable($variable)
    {
        if ($variable === "autostop")
            $this->writeLn("Internal variable AUTOSTOP = " . $this->maxOpCount);
        else if ($variable === "benchmark")
            $this->writeLn("Internal variable BENCHMARK = " . $this->benchmarkOn);
        else if ($variable === "debug")
            $this->writeLn("Internal variable DEBUG = " . $this->debugOn);
        else
            $this->writeLn("Unknown internal variable.");
    }

// end of WAM.getInternalVariable(String)

    private function parseInt($number)
    {
        if (!preg_match('#^[0-9]+$#', $number))
            throw new Exception('NumberFormatException');
        return (int) $number;
    }

    // returns the Variable pointer belonging to a string, e.g. "A3", "Y25"
    // TODO preg_match all of this
    private function get_ref($name)
    {
        $anArray = array();
        switch ($name[0]) {
            case 'Y': $anArray = &$this->env->variables;
                break;  // TODO passage par ref ok ?
            case 'A': $anArray = &$this->arguments;
                break;
            case 'Q': $anArray = &$this->queryVariables;
                break;
            default: return null;
        }
        $len = strlen($name);
        $cnt = 0;
        $index = 0;
        while (++$cnt < $len)
            $index = $index * 10 + ($name[$cnt] - '0');
        $cnt = count($anArray);
        while ($cnt++ < ($index + 1))
            $anArray[] = new Variable();
        return $anArray[$index];
    }

// end of WAM.get_ref(String)

    /*     * ****************** BEGIN WAM CODE OPERATIONS ******************* */

// WAM code operations are described in Ait Kaci: Warren's Abstract Machine -- A Tutorial Reconstruction
    // gives a name to a variable; usually used on Qxx variables that occur within the query
    private function create_variable($v, $name)
    {
        if (strcmp($name, "_") != 0) {  // keep "_" from being displayed as solution
            $q = $this->get_ref($v);
            $q->name = $name;
            // update displayQ-stuff
            $i = $this->parseInt(substr($v, 1));   // TODO preg_match
            if (!$this->displayQValue[$i]) {
                $this->displayQCount++;
                $this->displayQValue[$i] = true;
            }
        }
        $this->programCounter++;
    }

// end of WAM.create_variable(String, String)
    // comparison manages "<", "<=", ">=", ">" and "!="
    private function comparison($s1, $s2, $comparator)
    {
        // comparator values: 1 = "<", 2 = "<=", 3 = ">=", 4 = ">", 5 = "!="
        $v1 = $this->get_ref($s1)->deref();
        $v2 = $this->get_ref($s2)->deref();
        if (($v1->tag == self::CON) && ($v2->tag == self::CON)) {
            //int compareValue;
            try {
                $compareValue = $this->parseInt($v1->value) - $this->parseInt($v2->value);
            } catch (Exception $e) {
                $compareValue = strcmp($v1->value, $v2->value);
            }
            switch ($comparator) {
                case 1: if ($compareValue < 0)
                        $this->programCounter++;
                    else
                        $this->backtrack();
                    break;
                case 2: if ($compareValue <= 0)
                        $this->programCounter++;
                    else
                        $this->backtrack();
                    break;
                case 3: if ($compareValue >= 0)
                        $this->programCounter++;
                    else
                        $this->backtrack();
                    break;
                case 4: if ($compareValue > 0)
                        $this->programCounter++;
                    else
                        $this->backtrack();
                    break;
                case 5: if ($compareValue != 0)
                        $this->programCounter++;
                    else
                        $this->backtrack();
                    break;
                default: $this->backtrack();
            }
        }
        else
            $this->backtrack();
    }

// end of WAM.comparison(String, String, String)

    private function smaller($s1, $s2)
    {
        $this->comparison($s1, $s2, 1);
    }

// end of WAM.smaller(String, String)

    private function smallereq($s1, $s2)
    {
        $this->comparison($s1, $s2, 2);
    }

// end of WAM.smallereq(String, String)

    private function biggereq($s1, $s2)
    {
        $this->comparison($s1, $s2, 3);
    }

// end of WAM.biggereq(String, String)

    private function bigger($s1, $s2)
    {
        $this->comparison($s1, $s2, 4);
    }

// end of WAM.bigger(String, String)

    private function unequal($s1, $s2)
    {
        $this->comparison($s1, $s2, 5);
    }

// end of WAM.unequal(String, String)
    // is manages integer arithmetic (floating point may be added later)
    private function is($target, $op, $s1, $s2)
    {
        //Variable v1, v2, v3;
        //int z1, z2, z3;
        // convert s1 or the value of the variable referenced by s1 to int value
        try {
            $z1 = $this->parseInt($s1);
        } catch (Exception $e) {
            $v1 = $this->get_ref($s1)->deref();
            if ($v1->tag != self::CON) {
                $this->backtrack();
                return;
            }
            try {
                $z1 = $this->parseInt($v1->value);
            } catch (Exception $e2) {
                $this->backtrack();
                return;
            }
        }
        // convert s2 or the value of the variable referenced by s2 to int value
        try {
            $z2 = $this->parseInt($s2);
        } catch (Exception $e) {
            $v2 = $this->get_ref($s2)->deref();
            if ($v2->tag != self::CON) {
                $this->backtrack();
                return;
            }
            try {
                $z2 = $this->parseInt($v2->value);
            } catch (Exception $e2) {
                $this->backtrack();
                return;
            }
        }
        // check which variable is referenced by target
        $v3 = $this->get_ref($target)->deref();
        try {
            $z3 = 0;
            // do the arithmetic
            if ($op == '+')
                $z3 = $z1 + $z2;
            if ($op == '-')
                $z3 = $z1 - $z2;
            if ($op == '*')
                $z3 = $z1 * $z2;
            if ($op == '/')
                $z3 = $z1 / $z2;
            if ($op == '%')
                $z3 = $z1 % $z2;
            // if v3 (the target) has already been bound, consider this an equality check
//      if ((v3.tag == CON) && (parseInt(v3.value) != z3))      // do not allow this for now, since problems might occur
//        backtrack();
            if ($v3->tag == self::REF) {
                // if it has not been bound yet, bind it to constant value z3 (the integer number)
                $this->trail->addEntry($v3);
                $v3->tag = self::CON;
                $v3->value = "" . $z3;
                $this->programCounter++;
            }
            // only when alle stricke reissen: backtrack!
            else
                $this->backtrack();
        } catch (Exception $e) {
            $this->backtrack();
        }
    }

// end of WAM.is(String, String, String, String)

    private function get_variable($s1, $s2)
    {
        $Vn = $this->get_ref($s1);
        $Ai = $this->get_ref($s2);
        $Vn->copyFrom($Ai);
        $this->programCounter++;
    }

// end of WAM.get_variable(String, String)

    private function get_value($s1, $s2)
    {
        $this->unify_variable($s2, $s1);
    }

// end of WAM.get_value(String, String)

    private function get_constant($c, $variable)
    {
        $v = $this->get_ref($variable)->deref();
        $fail = true;
        if ($v->tag == self::REF) {
            $this->trail->addEntry($v);
            $v->tag = self::CON;
            $v->value = $c;
            $fail = false;
        } elseif ($v->tag == self::CON) {
            if (strcmp($c, $v->value) == 0)
                $fail = false;
        }
        if ($fail)
            $this->backtrack();
        else
            $this->programCounter++;
    }

// end of WAM.get_constant(String, String)

    private function unify_variable2($v1, $v2)
    {
        if (($v1 == null) || ($v2 == null))
            return false;
        $v1 = $v1->deref();
        $v2 = $v2->deref();
        if ($v1 === $v2)
            return true;

        if ($v1->tag == self::REF) {
            $this->trail->addEntry($v1);
            $v1->copyFrom($v2);
            return true;
        }
        if ($v2->tag == self::REF) {
            $this->trail->addEntry($v2);
            $v2->copyFrom($v1);
            return true;
        }

        if (($v1->tag == self::CON) && ($v2->tag == self::CON)) {
            if ($v1->value === $v2->value)
                return true;
            else
                return false;
        }

        if ((($v1->tag == self::LIS) && ($v2->tag == self::LIS)) || (($v1->tag == self::STR) && ($v2->tag == self::STR)))
            if (($this->unify_variable2($v1->head, $v2->head)) && ($this->unify_variable2($v1->tail, $v2->tail)))
                return true;

        return false;
    }

// end of WAM.unify_variable2(Variable, Variable)

    private function unify_list2(Variable $liste, Variable $head, Variable $tail)
    {
//    liste = list.deref();
//    head = head.deref();
//    tail = tail.deref();
        if ($liste->tag == self::REF) {
            $this->trail->addEntry($liste);
            $liste->tag = self::LIS;
            $liste->head = $head;
            $liste->tail = $tail;
            return true;
        }
        if ($liste->tag == self::LIS) {
            if ($this->unify_variable2($head, $liste->head))
                if ($this->unify_variable2($tail, $liste->tail))
                    return true;
        }
        return false;
    }

// end of WAM.unify_list2(Variable, Variable, Variable)

    private function unify_struc2(Variable $struc, Variable $head, Variable $tail)
    {
//    struc = struc.deref();
//    head = head.deref();
//    tail = tail.deref();
        if ($struc->tag == self::REF) {
            $this->trail->addEntry($struc);
            $struc->tag = self::STR;
            $struc->head = $head;
            $struc->tail = $tail;
            return true;
        }
        if ($struc->tag == self::STR) {
            if ($this->unify_variable2($head, $struc->head))
                if ($this->unify_variable2($tail, $struc->tail))
                    return true;
        }
        return false;
    }

// end of WAM.unify_struc2(Variable, Variable, Variable)

    private function unify_variable($s1, $s2)
    {
        $v1 = $this->get_ref($s1);
        $v2 = $this->get_ref($s2);
        if ($this->unify_variable2($v1, $v2))
            $this->programCounter++;
        else
            $this->backtrack();
    }

// end of WAM.unify_variable(String, String)

    private function unify_list($l, $h, $t)
    {
        $liste = $this->get_ref($l);
        $head = $this->get_ref($h);
        $tail = $this->get_ref($t);
        if ($this->unify_list2($liste, $head, $tail))
            $this->programCounter++;
        else
            $this->backtrack();
    }

// end of WAM.unify_list(String, String, String)

    private function unify_struc($s, $h, $t)
    {
        $struc = $this->get_ref($s);
        $head = $this->get_ref($h);
        $tail = $this->get_ref($t);
        if ($this->unify_struc2($struc, $head, $tail))
            $this->programCounter++;
        else
            $this->backtrack();
    }

// end of WAM.unify_struc(String, String, String)

    private function put_constant($c, $a)
    {
        $Ai = $this->get_ref($a);
        $Ai->tag = self::CON;
        $Ai->value = $c;
        $this->programCounter++;
    }

// end of WAM.put_constant(String, String)

    private function put_list($h, $t, $a)
    {
        $Ai = $this->get_ref($a);
        $Ai->tag = self::LIS;
        $Ai->head = $this->get_ref($h)->deref();
        $Ai->tail = $this->get_ref($t)->deref();
        $this->programCounter++;
    }

// end of WAM.put_list(String, String, String);

    private function put_value($s1, $s2)
    {
        $Vi = $this->get_ref($s1);
        $An = $this->get_ref($s2);
        $An->copyFrom($Vi);
        $this->programCounter++;
    }

// end of WAM.put_value(String, String)

    private function put_variable($s1, $s2)
    {
        $Vn = $this->get_ref($s1)->deref();
        $Ai = $this->get_ref($s2);
        $Ai->tag = self::REF;
        $Ai->reference = $Vn;
        $this->programCounter++;
    }

// end of WAM.put_variable(String, String)

    private function try_me_else($whom)
    {
        //int i;
        $cp = new ChoicePoint($this->arguments, $this->trail->getLength(), $this->continuationPointer);
        $cp->lastCP = $this->choicePoint;
        $cp->cutPoint = $this->cutPoint;
        $this->choicePoint = $cp;
        $cp->nextClause = $whom;
        $cp->lastEnviron = $this->env;
        $this->programCounter++;
    }

// end of WAM.try_me_else(int)

    private function proceed()
    {
        $this->programCounter = $this->continuationPointer;
    }

// end of WAM.proceed()

    private function is_bound(Variable $v)
    {
        $v = $v->deref();
        if ($v->tag == self::REF)
            $this->backtrack();
        else
            $this->programCounter++;
    }

// end of WAM.is_bound(String)

    private function allocate()
    {
        $environment = new Environment($this->continuationPointer, $this->env);
        $this->env = $environment;
        $this->programCounter++;
    }

// end of WAM.allocate()

    private function deallocate()
    {
        $this->continuationPointer = $this->env->returnAddress;
        $this->env = $this->env->lastEnviron;
        $this->programCounter++;
    }

// end of WAM.deallocate()

    private function call($target)
    {
        if ($target >= 0) {
            $this->continuationPointer = $this->programCounter + 1;
            $this->cutPoint = $this->choicePoint;
            $this->programCounter = $target;
        } else  // linenumbers < 0 indicate internal predicates, e.g. writeln
        if (!$this->internalPredicate($target))
            $this->backtrack();
    }

// end of WAM.call(int)
    // not_call performs a negated call by invoking a new WAM process
    // if the new process' execution fails, not_call is successful (backtrack, otherwise)
    private function not_call($target)
    {
        if (($target <= -10) && ($target >= -40)) {
            $this->backtrack();
            return;
        }
        // create a second WAM with the same code inside
        $wam2 = new WAM($this->p);
        $wam2->programCounter = $target;  // set programCounter the continuationPointer to their desired values
        $wam2->continuationPointer = $this->p->getStatementCount();
        // add a halt statement, making wam2 return "true" upon success. this is necessary!
        $this->p->addStatement(new Statement("", "halt", ""));
        $wam2->arguments = array();  // now, duplicate the argument vector
        foreach ($this->arguments as $item)
            $wam2->arguments[] = new Variable($item);
        // we don't need any benchmarking information from the child WAM
        $wam2->debugOn = $this->debugOn;
        $wam2->benchmarkOn = 0;
        $wam2->run();
        $wam2failed = $wam2->failed;
        while ($wam2->choicePoint != null)
            $wam2->backtrack();
        $wam2->backtrack();
        $this->p->deleteFromLine($this->p->getStatementCount() - 1);  // remove the earlier added "halt" statement from p
        $this->opCount += $wam2->opCount;
        $this->backtrackCount += $wam2->backtrackCount;  // update benchmarking information
        if ($wam2failed) {  // if wam2 failed, return "success"
            $this->failed = false;
            $this->programCounter++;
        }
        else // if it succeeded, consider this bad (since we are inside a not statement)
            $this->backtrack();
    }

// end of WAM.not_call(int)

    private function cut($Vn)
    {
        $v = $this->get_ref($Vn);
        $this->choicePoint = $v->cutLevel;
        $this->programCounter++;
    }

// end of WAM.cut(String)

    private function get_level($Vn)
    {
        $v = $this->get_ref($Vn);
        $v->cutLevel = $this->cutPoint;
        $this->programCounter++;
    }

// of WAM.get_level(String)

    /*     * ****************** END WAM CODE OPERATIONS ******************* */

    // called upon an unsuccessful binding operation or a call with non-existent target
    private function backtrack()
    {
        //int i;
        if ($this->debugOn > 0)
            $this->writeLn("-> backtrack");
        $this->backtrackCount++;
        $this->failed = true;
        if ($this->choicePoint != null) {
            $this->continuationPointer = $this->choicePoint->returnAddress;
            $this->programCounter = $this->choicePoint->nextClause;
            $this->env = $this->choicePoint->lastEnviron;
            $tp = $this->choicePoint->trailPointer;
            for ($i = $this->trail->getLength() - 1; $i >= $tp; $i--)
                $this->trail->undo($i);
            $this->trail->setLength($tp);
            $this->arguments = $this->choicePoint->arguments;
            $this->cutPoint = $this->choicePoint->cutPoint;
            $this->choicePoint = $this->choicePoint->lastCP;
        } else {
            for ($i = $this->trail->getLength() - 1; $i >= 0; $i--)
                $this->trail->undo($i);
            $this->programCounter = -1;
        }
    }

// end of WAM.backtrack()

    /*     * ****************** BEGIN INTERNAL PREDICATES ******************* */

    // internalPredicate manages the execution of all built-in predicates, e.g. write, consult, isbound
    private function internalPredicate($index)
    {
        $result = true;
        $v = $this->arguments[0];
        if ($index == self::callIsAtom)
            $this->isAtom($v->deref());
        else if ($index == self::callIsInteger)
            $this->isInteger($v->__toString());
        else if ($index == self::callIsBound)
            $this->is_bound($v);
        else if ($index == self::callWrite) {
            $this->write($v->__toString());
            $this->programCounter++;
        } else if ($index == self::callWriteLn) {
            $this->writeLn($v->__toString());
            $this->programCounter++;
        } else if ($index == self::callNewLine) {
            $this->writeLn("");
            $this->programCounter++;
        } else if ($index == self::callAssert) {
            $this->assert($v->head->__toString(), $v->__toString());
            return true;
        } else if ($index == self::callRetractOne) {
            if ($this->retract($v->__toString()))
                $this->programCounter++;
            else
                $this->backtrack();
        }
        else if ($index == self::callRetractAll)
            $this->retractall($v->__toString());
        else if ($index == self::callCall) {  // internal predicate call(X)
            $v2tmp = new Variable($v);
            $v2 = $v2tmp->deref();
            $intg = null;
            $target = -1;
            if ($v2->tag == self::CON) {
                $intg = (int) $this->p->labels[$v2->value];
                if ($intg !== null)
                    $target = $intg;
            }
            else if ($v2->tag == self::STR) {
                $intg = (int) $this->p->labels[$v2->head->value];  // TODO notice array_key ?
                if ($intg !== null) {
                    $target = (int) $intg;   // TODO useless ?
                    $tail = $v2->tail;
                    $cnt = 0;
                    while ($tail != null) {
                        $this->get_ref("A" . $cnt)->tag = self::REF;
                        $this->get_ref("A" . $cnt)->reference = $tail->head;
                        $cnt++;
                        $tail = $tail->tail;
                    }
                }
            }
            if ($target >= 0)
                $this->call($target);
            else
                $this->backtrack();
        }
        else if ($index == self::callLoad)
            $this->load($v->__toString());
        else if ($index == self::callConsult)
            $this->consult($v->__toString());
        else if ($index == self::callReadLn) {
            $w = new Variable("", $this->readLn());  // TODO foireux ?
            $this->unify_variable2($v->deref(), $w);
            $this->programCounter++;
        }
        else
            $result = false;
        return $result;
    }

// end of WAM.internalPredicate(String)

    private function load($fileName)
    {
        $prog = CodeReader::readProgram($fileName);
        if ($prog == null)
            if (false === strpos($fileName, ".wam")) {  // if compilation didn't work, try with different file extension
                $this->writeLn("File \"" . $fileName . "\" could not be opened.");
                $this->writeLn("Trying \"" . $fileName . ".wam\" instead.");
                $prog = CodeReader::readProgram($fileName . ".wam");
            }
        if ($prog == null)
            $this->backtrack();
        else {
            $this->p->addProgram($prog);
            $this->p->updateLabels();
            $this->programCounter++;
        }
    }

// end of WAM.load(String)

    private function isAtom(Variable $v)
    {
        $v = $v->deref();
        if (($v->tag == self::CON) || ($v->tag == self::REF))
            $this->programCounter++;
        else
            $this->backtrack();
    }

// end of WAM.isAtom(Variable)
    // checks if stuff contains an integer number
    private function isInteger($stuff)
    {
        try {
            $this->parseInt($stuff);
            $this->programCounter++;
        } catch (Exception $e) {
            $this->backtrack();
        }
    }

// end of WAM.isInteger(String)
    // assert asserts a new clause to the current program
    private function assert($label, $clause)
    {
        $pc = new PrologCompiler($this);
        $prog = $pc->compileSimpleClause($clause . ".");
        if ($prog != null) {
            $this->p->addClause($label, $prog);
            $this->programCounter++;
            $v = new Variable("", $label);
            $v->tag = ASSERT;
            $this->trail->addEntry($v);
        }
        else
            $this->backtrack();
    }

// end of WAM.assert(String, String)

    private function removeProgramLines($fromLine)
    {
        $size = $this->p->getStatementCount();
        $removed = $this->p->deleteFromLine($fromLine);
        if ($this->programCounter >= $fromLine) {
            if ($this->programCounter >= $fromLine + $removed)
                $this->programCounter -= $removed;
            else
                $this->backtrack();
        }
    }

    // retract undoes an assert action
    private function retract($clauseName)
    {
        $index1 = $this->p->getLastClauseOf($clauseName);
        $index2 = $this->p->getLastClauseButOneOf($clauseName);
        if ($index1 >= 0) {
            $this->removeProgramLines($index1);
            if ($index2 >= 0) {
                $s = $this->p->getStatement($index2);
                $s->setFunction("trust_me");
                $s->setArgAt("", 0);
                $s->arg1 = "";
            }
            return true;
        }
        else
            return false;
    }

    // calls retract(String) until it returns false
    private function retractall($clauseName)
    {
        $success = false;
        $this->failed = false;
        while ($this->retract($clauseName)) {
            if ($this->failed)
                return;
            $success = true;
        };
        if ($success)
            $this->programCounter++;
        else
            $this->backtrack();
    }

// end of WAM.retractall(String)
    // consult compiles a prolog program and loads the resulting code into memory
    private function consult($fileName)
    {
        $pc = new PrologCompiler($this);
        $prog = $pc->compileFile($fileName);
        if ($prog == null)
            if (false === strpos($fileName, ".pro")) {  // if compilation didn't work, try with different file extension
                $this->writeLn("Trying \"" . $fileName . ".prolog\" instead.");
                $prog = $pc->compileFile($fileName . ".prolog");
            }
        if ($prog == null)  // program could not be compiled/loaded for whatever reason
            $this->backtrack();
        else {
            if ($this->debugOn > 1)  // in case of debug mode, display the WAM code
                $this->writeLn($prog->__toString());
            $this->p->owner = $this;
            $this->p->addProgram($prog);  // add program to that already in memory
            $this->p->updateLabels();  // and don't forget to update the jump labels
            $this->programCounter++;
        }
    }

// end of WAM.consult(String)

    /*     * ****************** END INTERNAL PREDICATES ******************* */

    // showHelp shows a list of the available commands
    private function showHelp()
    {
        $this->writeLn("This is Stu's mighty WAM speaking. Need some help?");
        $this->writeLn("");
        $this->writeLn("Available commands:");
        $this->writeLn("clear                   empties the output area (GUI mode only)");
        $this->writeLn("exit                    terminates the WAM");
        $this->writeLn("help                    displays this help");
        $this->writeLn("list                    lists the WAM program currently in memory");
        $this->writeLn("new                     removes all WAM code from memory");
        $this->writeLn("set [PARAM[=VALUE]]     displays all internal parameters (\"set\") or lets");
        $this->writeLn("                        the user set a parameter's new value, respectively");
        $this->writeLn("labels                  displays all labels that can be found in memory");
        $this->writeLn("procedures              displays the names of all procedures in memory");
        $this->writeLn("quit                    terminates the WAM");
        $this->writeLn("");
        $this->writeLn("Prolog programs can be compiled into memory by typing \"consult(filename).\",");
        $this->writeLn("e.g. \"consult('lists.pro').\". Existing WAM programs can be loaded into");
        $this->writeLn("memory by typing \"load(filename).\".");
        $this->writeLn("");
        $this->writeLn("" . $this->p->getStatementCount() . " lines of code in memory.");
    }

    public function traceOn()
    {
        $this->write("A=[");
            foreach ($this->arguments as $v) {
                $this->write($v->tag . "/");
                $this->write($v->value);
                $this->write("(".$v.") ");
            }
            $this->write("] V=[");
            foreach ($this->env->variables as $v) {
                $this->write($v->tag . "/");
                $this->write($v->value);
                $this->write("(".$v.") ");
            }
            $this->writeLn("]");
    }

// end of WAM.showHelp()
    // run starts the actual execution of the program in memory
    public function run()
    {
        // opCount and backtrackCount are used for benchmarking
        $this->opCount = 0;
        $this->backtrackCount = 0;

        $this->failed = true;

        while ($this->programCounter >= 0) {   // programCounter < 0 happens on jump error or backtrack without choicepoint
            $this->failed = false;
            $s = $this->p->getStatement($this->programCounter);  // get current WAM statement

            if ($this->debugOn > 0)  // display statement and line number information in case of debug mode
                $this->writeLn("(" . $this->int2FormatStr($this->programCounter) . ")  " . $s->__toString());

            // we have introduced an artificial stack overflow limit in order to prevent the WAM from infinite execution
            if ($this->opCount++ > $this->maxOpCount) {
                $this->writeLn("Maximum OpCount reached. Think of this as a stack overflow.");
                $this->failed = true;
                break;
            }

            $this->traceOn();

            // select WAM command and execute the responsible method, e.g. "deallocate()"
            // TODO switch FFS !
            $op = $s->operator;
            if ($op == self::opAllocate)
                $this->allocate();
            else if ($op == self::opCall)
                $this->call($s->jump);
            else if ($op == self::opNotCall)
                $this->not_call($s->jump);
            else if ($op == self::opCut)
                $this->cut($s->arg1);
            else if ($op == self::opDeallocate)
                $this->deallocate();
            else if ($op == self::opGetVariable)
                $this->get_variable($s->arg1, $s->arg2);
            else if ($op == self::opPutValue)
                $this->put_value($s->arg1, $s->arg2);
            else if ($op == self::opPutVariable)
                $this->put_variable($s->arg1, $s->arg2);
            else if ($op == self::opGetLevel)
                $this->get_level($s->arg1);
            else if ($op == self::opGetConstant)
                $this->get_constant($s->arg1, $s->arg2);
            else if ($op == self::opGetValue)
                $this->get_value($s->arg1, $s->arg2);
            else if ($op == self::opPutConstant)
                $this->put_constant($s->arg1, $s->arg2);
            else if ($op == self::opUnifyList)
                $this->unify_list($s->arg1, $s->arg2, $s->arg3);
            else if ($op == self::opUnifyStruc)
                $this->unify_struc($s->arg1, $s->arg2, $s->arg3);
            else if ($op == self::opUnifyVariable)
                $this->unify_variable($s->arg1, $s->arg2);
            else if ($op == self::opRetryMeElse)
                $this->try_me_else($s->jump);
            else if ($op == self::opTryMeElse)
                $this->try_me_else($s->jump);
            else if ($op == self::opTrustMe)
                $this->programCounter++;
            else if ($op == self::opProceed)
                $this->proceed();
            else if ($op == self::opBigger)
                $this->bigger($s->arg1, $s->arg2);
            else if ($op == self::opBiggerEq)
                $this->biggereq($s->arg1, $s->arg2);
            else if ($op == self::opSmaller)
                $this->smaller($s->arg1, $s->arg2);
            else if ($op == self::opSmallerEq)
                $this->smallereq($s->arg1, $s->arg2);
            else if ($op == self::opUnequal)
                $this->unequal($s->arg1, $s->arg2);
            else if ($op == self::opIs)
                $this->is($s->arg1, $s->arg2[0], $s->arg3, (string) $s->getArgAt(3));
            else if ($op == self::opHalt)
                break;
            else if ($op == self::opNoOp)
                $this->programCounter++;
            else if ($op == self::opCreateVariable)
                $this->create_variable($s->arg1, $s->arg2);
            else { // invalid command: backtrack!
                $this->writeLn("Invalid operation in line " . $this->int2FormatStr($this->programCounter));
                $this->backtrack();
            }

            $this->traceOn();

        }; // end of while (programCounter >= 0)
        if ($this->failed) {
            while ($this->choicePoint !== null)
                $this->backtrack();
            $this->backtrack();
        }
        if ($this->benchmarkOn > 0) {
            $this->writeLn("# operations: " . $this->opCount);
            $this->writeLn("# backtracks: " . $this->backtrackCount);
        }
    }

// end of WAM.run()
    // runQuery compiles a query given by s into a WAM program, adds it to the program in memory
    // and jumps to the label "query$", starting the execution
    public function runQuery($s)
    {
        $qc = new QueryCompiler($this);
        $this->reset();
        $this->p->deleteFrom("query$");
        $s = trim($s);

        /*         * ************* BEGIN SPECIAL COMMANDS ************** */

        // input "quit" or "exit" means: end the WAM now, dude!
        if (in_array($s, array("quit", "exit")))
            return false;
        if ($s == "clear") {
            $this->writeLn("Not in GUI mode.");
            return true;
        }
        if ($s == "help") {
            $this->showHelp();  // display some help information
            return true;
        }
        if ($s == "set") {
            $this->displayInternalVariables();  // show the states of the internal parameters
            return true;
        }
        if ($s == "labels") {  // show all labels of the current program
            for ($i = 0; $i < $this->p->getStatementCount(); $i++) {
                $m = $this->p->getStatement($i)->getLabel();
                if (strlen($m) > 0)
                    $this->writeLn($m);
            }
            return true;
        }
        if ($s == "procedures") {  // show all procedure names of the current program
            for ($i = 0; $i < $this->p->getStatementCount(); $i++) {
                $m = $this->p->getStatement($i)->getLabel();
                if ((strlen($m) > 0) && (false === strpos($m, '~')))
                    $this->writeLn($m);
            }
            return true;
        }
        if ($s == "list") {  // show the WAM code of the program currently in memory
            if ($this->p->getStatementCount() == 0)
                $this->writeLn("No program in memory.");
            else
                $this->writeLn($this->p->__toString());
            return true;
        }
        if ($s == "new") {  // clear memory
            $this->p = new Program($this);
            $this->writeLn("Memory cleared.");
            return true;
        }
        if ((strlen($s) > 4) && (substr($s, 0, 4) == "set ")) {  // TODO preg_match
            $s = substr($s, 4);  // set an internal parameter's new value
            $i = strpos($s, ' ');
            while ($i >= 0) {
                $s = substr($s, 0, $i) . substr($s, $i + 1);
                $i = strpos($s, ' ');
            }
            $i = strpos($s, '=');
            if ($i >= 0) {
                $variable = substr($s, 0, $i);
                $value = substr($s, $i + 1);
                $this->setInternalVariable($variable, $value);
            }
            else  // if no new value has been specified, display the current
                $this->getInternalVariable($s);
            return true;
        } // end of "set ..." command

        /*         * ************* END SPECIAL COMMANDS ************** */

        $query = $qc->compile($s);

        if ($query == null) {  // query could not be compiled
            $this->writeLn("Illegal query.");
            return true;
        } else {
            if ($this->debugOn > 1) {  // if in debug mode, display query WAM code
                $this->writeLn("----- BEGIN QUERYCODE -----");
                $this->writeLn($query->__toString());
                $this->writeLn("------ END QUERYCODE ------");
            }
            $this->p->addProgram($query);  // add query to program in memory and
            $this->p->updateLabels();  // update the labels for jumping hin und her
        }

        // reset the WAM's registers and jump to label "query$" (the current query, of course)
        $this->programCounter = $this->p->getLabelIndex("query$");
        $answer = "";
        do {
            $ms = microtime(true);
            $this->run();

            if ($this->benchmarkOn > 0)  // sometimes, we need extra benchmark information
                $this->writeLn("Total time elapsed: " + (microtime(true) - $ms) + " ms.");
            $this->writeLn("");

            if ($this->failed) {  // if execution failed, just tell that
                $this->writeLn("Failed.");
                break;
            }

            // if there are any query variables (e.g. in "start(X, Y)", X and Y would be such variables),
            // display their current values and ask the user if he/she wants to see more possible solutions
            if ($this->displayQCount > 0) {
                $this->write("Success: ");
                $cnt = 0;
                for ($i = 0; $i < 100; $i++)  // yes, we do not allow more than 100 query variables!
                    if ($this->displayQValue[$i]) {
                        $cnt++;  // if Q[i] is to be displayed, just do that
                        $this->write($this->queryVariables[$i]->name . " = ");
                        $this->write($this->queryVariables[$i]->__toString());
                        if ($cnt < $this->displayQCount)
                            $this->write(", ");
                        else
                            $this->writeLn(".");
                        $this->backtrack();
                        $answer = 'y';
                    }
            }
            else
                $this->writeLn("Success.");
            // if there are any more choicepoints left, ask the user if they shall be tried
      /*      if ($this->choicePoint != null) {

                $this->write("More? ([y]es/[n]o) ");
                $answer = $this->readLn();
                $this->writeLn("");
            }
            else
                break;
//      }
//      else {  // if there are no query variables at all, trying the remaining choicepoints seems senseless
//        writeLn("Success.");
//        break;
//      }
            // if the users decided to see more, show him/her. otherwise: terminate
            if (($answer == "y") || ($answer == "yes"))
                $this->backtrack();*/
        } while (($answer == "y") || ($answer == "yes"));
        $this->reset();
        return true;
    }

// end of WAM.runQuery(String)
    // the WAM's main loop
    public static function main(array $args)
    {
        echo"\nWelcome to Stu's mighty WAM!";
        echo "(December 2001 - February 2002 by Stefan Buettcher)\n";
        echo "Type \"help\" to get some help.\n";
        $wam = new WAM(new Program());
        $wam->p->owner = $wam;
        $s = '';
        do {
            $wam->writeLn("");
            $wam->write("QUERY > ");
            $s = $wam->readLn();
            $wam->writeLn("");
        } while (($s != null) && ($wam->runQuery($s)));
        $wam->writeLn("Goodbye!");
        $wam->writeLn("");
    }

// end of WAM.main(String[])
}

// end of class WAM
require_once('Program.php');
require_once('Statement.php');
require_once('CodeReader.php');
require_once('CompilerStructure.php');
require_once('Compiler.php');
require_once('QueryCompiler.php');
require_once('PrologCompiler.php');
require_once('InnerClass.php');

WAM::main($argv);
