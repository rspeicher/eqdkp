<?php
$array1 = array('blue'  => 1, 'red'  => 2, 'green'  => 3, 'purple' => 4);
$array2 = array('green' => 5, 'blue' => 6, 'yellow' => 7, 'cyan'   => 8);

var_dump(array_diff_key($array1, $array2));

$diff_key = array();
$diffs = array_diff(array_keys($array1), array_keys($array2));
foreach ( $diffs as $key )
{
    $diff_key[$key] = $array1[$key];
}
var_dump($diff_key);
?>
