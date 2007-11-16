<?php

define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = '../upload/';
include($eqdkp_root_path . 'common.php');

include($eqdkp_root_path . 'games/game_manager.php');

/* Here's a nice gotcha for using is_dir:

@param    string   filename     Path to the file. **If filename is a relative filename, it will be checked relative to the current working directory.**
@return   bool                  TRUE if the filename exists and is a directory, FALSE otherwise.

Make sure you use $eqdkp_root_path every time you check for a valid file, otherwise is_dir / is_file etc will check in the active directory (the directory with the calling script)!
For instance, once the Game_Manager starts running, the active directory will still be this folder (_tests), NOT (../uploads/games/)!
*/

$filename = $eqdkp_root_path . 'games/wow';
echo $filename . "<br />\n\n";
echo (is_dir($filename)) ? 'true' : 'false';
echo "<br />\n\n";

$gm = new Game_Manager();
$games = $gm->list_games();

var_dump($games);

?>