<?php

/* * ****************************************************************************
 * Warren's Abstract Machine  -  Implementation by Stefan Buettcher
 *
 * developed:   December 2001 until February 2002
 *
 * CodeReader.java contains the CodeReader class that transforms a WAM code
 * input file into a Program structure (cf. Program.java / Statement.java)
 * **************************************************************************** */

class CodeReader {

    /**
     * @return Program
     */
    public static function readProgram($fileName) {
        // BufferedReader b;
        try {
            $b = fopen($fileName, 'r');
            $p = new Program();
            // Statement s;
            // String str;
            do {
                $str = fgets($b);
                if ($str != null) {
                    //int j;
                    $str = trim($str);
                    if (strlen($str) == 0)
                        continue;
                    if (in_array($str[0], array(';', '#', '%')))
                        continue;
                    $mark = "";
                    $j = strpos($str, ":");
                    if ($j > 0) {
                        $mark = trim(substr($str, 0, $j));
                        $str = trim(substr($str, $j + 1));
                    }
                    $fonction = "";
                    $j = strpos($str, " ");
                    if ($j > 0) {
                        $fonction = trim(substr($str, 0, $j));
                        $str = trim(substr($str, $j + 1));
                        $j = strpos($str, " ");
                        if ($j > 0)
                            $s = new Statement($mark, $fonction, trim(substr($str, 0, $j)), trim(substr($str, $j + 1)));
                        else
                            $s = new Statement($mark, $fonction, $str);
                    }
                    else
                        $s = new Statement($mark, $str, "");
                    $p->addStatement($s);
                }
            } while ($str !== false);
            fclose($b);
            $p->updateLabels();
            return $p;
        } catch (Exception $io) {
            return null;
        }
    }

// end of CodeReader.readProgram()
}

// end of class CodeReader
