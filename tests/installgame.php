<?php

define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = '../upload/';
include($eqdkp_root_path . 'common.php');

include($eqdkp_root_path . 'games/game_manager.php');

$gm = new Game_Manager();
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
	var_dump($game_keys);
	echo "</pre>";
	echo "<br /><br />\n\n";	
	
	$gm->set_current_game($game_keys[0]);
	
	// NOTE: This function is actually meant to be private.
	// Also in this revision (360), this function doesn't touch the database - it var_dumps the built queries.
	echo "<pre>\n";
	$gm->_install_game();
	echo "</pre>";
}

?>