<?php

define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = '../upload/';
include($eqdkp_root_path . 'common.php');
include($eqdkp_root_path . 'games/game_installer.php');

$thegame = $in->get('game','');

$gm = new Game_Installer();
$games = $gm->list_games();

echo "<pre>\n";
var_dump($games);
echo "</pre>";
echo "<br /><br />\n\n";

if( count($games) )
{
    // NOTE: Retrieve/Keep the game's package id in order to access its information
    $game_keys = array_keys($games);
    
    echo "Game Package IDs: \n";
    echo "<pre>\n";
    print_r($game_keys);
    echo "</pre>";
    echo "<br /><br />\n\n";    

    // Alternatively, as of revision 395, the game data itself should have the id in it.
    echo "Game IDs - keys vs stored values" . "<br />\n<pre>";
    foreach ($game_keys as $game_id)
    {
        echo "Game: " . $game_id . " - ";
        echo (isset($games[$game_id]['id'])) ? 'id set' : 'no id';
        if (isset($games[$game_id]['id']))
        {
            echo " - " . $games[$game_id]['id'];
        }
        echo "\n";
    }
    echo "</pre>\n<br />\n";
    
    $thegame = (in_array($thegame, $game_keys)) ? $thegame : $game_keys[0];
    
    $gm->set_current_game($thegame);
    echo "Selected game data: " . "<br />\n<pre>";
    print_r($gm->get_game_data());
    echo "</pre>\n<br /><br />\n\n";
    
    // NOTE: This function is actually meant to be private.
    // In this revision (398), this function accepts a single boolean parameter echo_sql, which will echo all sql rather than executing it.
    echo "Game SQL queries" . "<br />\n";
    echo "<pre>\n";
    $gm->_create_database_tables(true);
    echo "</pre>";
}

?>