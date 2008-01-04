<?php

define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = '../upload/';
include($eqdkp_root_path . 'common.php');

include_once($eqdkp_root_path . 'games/game_manager.php');

$gm = new Game_Manager();

$games = $gm->set_current_game('wow');
var_dump($games);
//var_dump($gm->games);

$log_entries = array(
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Tsigo]: 70 Dwarf Hunter',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
	'[Dazza] <Banimal>: 60 Night Elf Priest - Winterspring',
);

$results = array();

//
// Time trial!
//
set_time_limit(360);

$trials = 100;
$start_time = microtime(1);

for ($i=0; $i<$trials; $i++)
{
	foreach ($log_entries as $log)
	{
		$results[$i][] = $gm->parse_log_entry($log);
	}
}

$end_time = microtime(1);

echo "\n<p>" . $start_time . " -> " . $end_time . "</p>\n";
echo "<p>Total time taken: " . ($end_time - $start_time) . "</p>";
echo "<p>Average time: " . (($end_time - $start_time)/$trials) . "</p>";

echo "<h2>Logs</h2>\n\n";
echo "<pre>";
print_r($log_entries);
echo "</pre>";

/*
echo "<pre>";
foreach ($results as $result)
{
	print_r($result);
}
echo "</pre>";
*/

?>