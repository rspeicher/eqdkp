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
    global $db, $eqdkp;
    
    Upgrade::prepare_uniquekey('members', array('member_name'));
    
    $queries = array();
    if ( isset($eqdkp->config['default_game']) && $eqdkp->config['default_game'] == 'WoW' )
    {
        $queries[] = "UPDATE __classes SET class_armor_type = 'Mail' WHERE (LOWER(`class_armor_type`) = 'chain')";
    }
    $queries[] = "CREATE UNIQUE INDEX member_idx ON __members (member_name)";

    Upgrade::execute($queries);
    
    Upgrade::set_version($VERSION);
    Upgrade::progress($VERSION);
}