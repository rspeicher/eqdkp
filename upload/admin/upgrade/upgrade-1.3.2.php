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
 * @copyright   2002-2007 The EQdkp Project Team
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
    Upgrade::set_version($VERSION);
    Upgrade::progress("Completed upgrade to $VERSION.");
}

/*
$sql = "SELECT config_value FROM __config WHERE config_name = 'default_game'";
$result = $db->query_first($sql);

$sql = array();

if ( $result == "WoW" )
{
    $sql[] = "INSERT IGNORE INTO __races (race_id, race_name) VALUES (9, 'Draenei')";
    $sql[] = "INSERT IGNORE INTO __races (race_id, race_name) VALUES (10, 'Blood Elf')";
    $sql[] = "UPDATE __classes SET class_max_level = 70 WHERE class_max_level = 60";
}

$sql[] = "UPDATE __auth_options SET auth_value = 'a_backup' WHERE (auth_id = '36')";

if ( count($sql) > 0 ) 
{
    foreach ( $sql as $query )
    {
        $db->query($query);
    }
}
*/