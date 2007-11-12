<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        upgrade-1.2.0_01.php
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

$VERSION = '1.2.0B1';

if ( class_exists('Upgrade') && Upgrade::should_run($VERSION) )
{
    Upgrade::execute(array(
        "ALTER TABLE __users ADD user_lastpage varchar(100) default '' AFTER user_lastvisit",
        "INSERT INTO __config (config_name, config_value) VALUES ('start_page', 'viewnews.php')",
        "ALTER TABLE __plugins ADD plugin_version varchar(7)",
        
        "CREATE TABLE IF NOT EXISTS __member_user (
           member_id smallint(5) unsigned NOT NULL,
           user_id smallint(5) unsigned NOT NULL,
           KEY member_id (member_id),
           KEY user_id (user_id)
        )",
        
        "RENAME TABLE __member_flags TO __member_ranks",
        "ALTER TABLE __member_ranks CHANGE flag_id rank_id smallint(3) unsigned NOT NULL UNIQUE",
        "ALTER TABLE __member_ranks CHANGE flag_name rank_name varchar(50) NOT NULL",
        "ALTER TABLE __member_ranks ADD rank_hide enum('0','1') NOT NULL DEFAULT '0'",
        "ALTER TABLE __member_ranks ADD rank_prefix varchar(75) NOT NULL default ''",
        "ALTER TABLE __member_ranks ADD rank_suffix varchar(75) NOT NULL default ''",
        "ALTER TABLE __members CHANGE member_flag member_rank_id smallint(3) NOT NULL default '0'"
    ));
    
    Upgrade::set_version($VERSION);
    Upgrade::progress($VERSION);
}