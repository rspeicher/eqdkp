<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        upgrade-1.3.2.php
 * Began:       Sun Nov  4 2007
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

$VERSION = '1.3.2';

if ( class_exists('Upgrade') && Upgrade::should_run($VERSION) )
{
    $queries = array();
    
    // Determine what the currently installed game is
    $sql = "SELECT config_value 
            FROM __config
            WHERE `config_name` = 'default_game'";
    $game_name = $db->query_first($sql);
        
    switch (strtolower($game_name))
    {
        case 'wow':
            $queries[] = "INSERT IGNORE INTO __races (race_id, race_name) VALUES (9, 'Draenei')";
            $queries[] = "INSERT IGNORE INTO __races (race_id, race_name) VALUES (10, 'Blood Elf')";
            $queries[] = "UPDATE __classes SET class_max_level = 70 WHERE class_max_level = 60";
            break;
    }
    
    $queries[] = "UPDATE __auth_options SET auth_value = 'a_backup' WHERE (auth_id = '36')";
    
    Upgrade::execute($queries);
    
    Upgrade::set_version($VERSION);
    Upgrade::progress($VERSION);
}