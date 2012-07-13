<?php

/**
 * Trail implements the WAM's trail (undo-list for bindings performed)
 */
class Trail
{

    private $contents = array();
    private $machine = null;

    public function __construct(PrologContext $ctx)
    {
        $this->contents = array();
        $this->machine = $ctx;
    }

    public function getLength()
    {
        return count($this->contents);
    }

    public function setLength($length)
    {
        if ($length > count($this->contents))
            $this->contents = array_pad($this->contents, $length, null);
        elseif ($length < count($this->contents))
            array_splice($this->contents, $length - count($this->contents));
    }

    public function addEntry(Variable $v)
    {
        $this->contents[] = $v;
    }

    public function getEntry($index)
    {
        return $this->contents[$index];
    }

    public function undo($index)
    {
        $v = $this->contents[$index];
        if ($v !== null) {
            if ($v->tag == WAM::ASSERT)
                $this->machine->retract($v->value);
            $v->tag = WAM::REF;
            $v->reference = $v;
        }
    }

}
