<?php

function tableau(array &$byref)
{
    array_shift($byref);
}

$tab = array(1, 2, 3, 4, 5, 6);
$tab2 = $tab;
tableau($tab);
print_r($tab);
print_r($tab2);


$s = "A_tat77ata";
if (preg_match('#^[A-Z][a-zA-Z0-9_]*$#', $s)) {
    echo "wesh $s\n";
}
