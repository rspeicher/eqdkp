<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        upgrade-1.4.0_02.php
 * Began:       Thu Dec 11 2008
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2008 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     upgrade
 * @version     $Rev$
 */

if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

$VERSION = '1.4.0 B2';

if ( class_exists('Upgrade') && Upgrade::should_run($VERSION) )
{
    // Determine what the currently installed game is
    $sql = "SELECT config_value 
            FROM __config
            WHERE `config_name` = 'current_game'";
    $game_name = $db->query_first($sql);
    
    if ( $game_name == 'wow' )
    {
        Upgrade::execute(array(
            "INSERT INTO __classes VALUES ('10', 'Death Knight', 'death_knight', '0')",
            "INSERT INTO __class_armor VALUES ('10', '4', '0', NULL)",
        ));
    }
    
    Upgrade::set_version($VERSION);
    Upgrade::progress($VERSION);
}