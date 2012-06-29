<?php

function tableau(array &$byref)
{
    array_shift($byref);
}

$tab = array(1, 2, 3, 4, 5, 6);
$tab2 = clone $tab;
tableau($tab);
print_r($tab);
print_r($tab2);