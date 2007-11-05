<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        upgrade-1.2.0_02.php
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

$VERSION = '1.2.0B2';

if ( class_exists('Upgrade') && Upgrade::should_run($VERSION) )
{
    Upgrade::set_version($VERSION);
    Upgrade::progress("Completed upgrade to $VERSION.");
}

$queries = array(
    "ALTER TABLE __users ADD user_lastpage varchar(100) default '' AFTER user_lastvisit;",
    "ALTER TABLE __plugins ADD plugin_version varchar(7);",
    "CREATE TABLE IF NOT EXISTS __member_user (
       member_id smallint(5) unsigned NOT NULL,
       user_id smallint(5) unsigned NOT NULL,
       KEY member_id (member_id),
       KEY user_id (user_id));",
);