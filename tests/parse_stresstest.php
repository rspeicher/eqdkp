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
    //[__NAME__]: Level __LEVEL__ __RACE__ __CLASS__ <__GUILD__> - __ZONE__
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
);

$results = array();

//
// Time trial!
//
set_time_limit(360);

echo "<h1>Time Trial!</h1>\n\n";

echo "<h2>Logs</h2>\n\n";
echo "<pre>";
print_r($log_entries);
echo "</pre>";


// Begin the time trial
$trials = 400;
$start_time = microtime(1);

for ($i=0; $i<$trials; $i++)
{
    foreach ($log_entries as $log)
    {
        $results[$i][] = $gm->parse_log_entry($log);
    }
}

$end_time = microtime(1);

echo "<h3>Ran " . $trials . " times.</h3>\n\n";

echo "<h2>Results:</h2>\n";
echo "\n<p>" . $start_time . " -> " . $end_time . "</p>\n";
echo "<p>Total time taken: " . ($end_time - $start_time) . "</p>";
echo "<p>Average time: " . (($end_time - $start_time)/$trials) . "</p>";

/*
echo "<pre>";
foreach ($results as $result)
{
    print_r($result);
}
echo "</pre>";
*/

?>