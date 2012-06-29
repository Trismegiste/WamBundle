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
class WAM {

    public function debug($str, $lvl) {
        $this->writeLn($str);
    }

    public function writeLn($str) {
        echo $str . "\n";
    }

}

// end of class WAM

