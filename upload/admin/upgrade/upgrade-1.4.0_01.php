<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        upgrade-1.4.0_01.php
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

// FIXME: This needs to change as we decide our beta-testing plan
$VERSION = '1.4.0';

if ( class_exists('Upgrade') && Upgrade::should_run($VERSION) )
{
    Upgrade::set_version($VERSION);
    Upgrade::progress("Completed upgrade to $VERSION.");
}

/*
-- These are necessary because we changed to a REPLACE INTO syntax
ALTER TABLE __auth_users DROP INDEX `user_id`;
ALTER TABLE __auth_users DROP INDEX `auth_id`;
-- Before next query is run, we might need to remove possible duplicates, so it doesn't error out
ALTER TABLE __auth_users ADD UNIQUE `user_auth` ( `user_id` , `auth_id` );

ALTER TABLE __raid_attendees DROP INDEX `raid_id`;
ALTER TABLE __raid_attendees DROP INDEX `member_name`;
-- Before next query is run, we might need to remove possible duplicates, so it doesn't error out
ALTER TABLE __raid_attendees ADD UNIQUE `raid_member` ( `raid_id` , `member_name` );

-- Update all FLOAT values to larger DOUBLEs
ALTER TABLE __adjustments CHANGE `adjustment_value` `adjustment_value` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'
ALTER TABLE __events CHANGE `event_value` `event_value` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'
ALTER TABLE __items CHANGE `item_value` `item_value` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'
ALTER TABLE __members CHANGE `member_earned` `member_earned` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'
ALTER TABLE __members CHANGE `member_spent` `member_spent` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'
ALTER TABLE __members CHANGE `member_adjustment` `member_adjustment` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'
ALTER TABLE __raids CHANGE `raid_value` `raid_value` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'
*/