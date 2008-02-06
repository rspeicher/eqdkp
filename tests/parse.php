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
    '[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring',
    '[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple',
    '[Aldos]: Level 40 Gnome Mage - Ironforge',
);

echo "<h2>Logs</h2>\n\n";
print_r($log_entries);

foreach ($log_entries as $log)
{
    echo "<h3>" . htmlspecialchars($log) . "</h3>";
    
    $result = $gm->parse_log_entry($log);
    
    echo "\n<p><pre>";
    var_dump($result);
    echo "</pre></p>\n";
}

?>