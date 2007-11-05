<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        upgrade-1.3.1.php
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

$VERSION = '1.3.1';

if ( class_exists('Upgrade') && Upgrade::should_run($VERSION) )
{
    Upgrade::set_version($VERSION);
    Upgrade::progress("Completed upgrade to $VERSION.");
}

/*
$sql = 'SELECT config_value FROM __config WHERE config_name = "default_game"';
$result = $db->query_first($sql);

if ( $result == "WoW" )
{
    $sql = "UPDATE __classes 
            SET class_armor_type = 'Mail'
            WHERE (class_armor_type = 'Chain')
            OR (class_armor_type = 'chain')";
    $db->query($sql);
}

$sql = 'CREATE UNIQUE INDEX member_idx ON __members (member_name)';
$result = $db->query_first($sql);
*/