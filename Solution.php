<?php

/**
 * Description of Solution
 *
 * @author flo
 */
class Solution
{

    public $succeed = false;
    public $variable = array();
    protected $output = array('');

    public function writeLn($str)
    {
        $this->write($str);
        $this->output[] = '';
    }

    public function write($str)
    {
        $this->output[count($this->output) - 1] .= $str;
    }

}
