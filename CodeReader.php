<?php

/**
 * Warren's Abstract Machine  -  Implementation by Stefan Buettcher
 *                            -  Ported to PHP by Trismegiste
 *
 * developed:   December 2001 until February 2002
 * ported:      July 2012
 *
 * CodeReader class transforms a WAM code
 * input file into a Program structure (cf. Program / Statement)
 * 
 */
class CodeReader
{

    /**
     * @return Program
     */
    public static function readProgram($fileName)
    {
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
                    // ce passage est tout buggé
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

    /**
     * Write a program to a file
     * 
     * @param Program $prog
     * @param string $filename
     */
    public static function writeProgram(Program $prog, $filename)
    {
        if ($handle = fopen($filename, 'w')) {
            for ($k = 0; $k < $prog->getStatementCount(); $k++) {
                fprintf($handle, "%s\n", $prog->getStatement($k)->dumpWamCode());
            }
            $handle = fclose($handle);
        }

        return $handle;
    }

    /**
     * Compile a prolog file into a WAM file
     * 
     * @param string $prologFile
     * @param string $wamFile 
     */
    public static function prologToWamCode($prologFile, $wamFile)
    {
        $compiler = new PrologCompiler(new WAMService(new Program()));
        $p = $compiler->compileFile($prologFile);
        if (!is_null($p))
            self::writeProgram($p, $wamFile);
    }

}
