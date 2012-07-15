<?php

/**
 * This class contains one result for a submitted query against the WAMService
 *
 * @author florent
 */
class Solution
{

    public $succeed = false;
    public $variable = array();
    protected $output = array('');

    /**
     * Close a line and start a new one
     * @param string $str 
     */
    public function writeLn($str)
    {
        $this->write($str);
        $this->output[] = '';
    }

    /**
     * Continue a line with a string
     * @param string $str 
     */
    public function write($str)
    {
        $this->output[count($this->output) - 1] .= $str;
    }

}
