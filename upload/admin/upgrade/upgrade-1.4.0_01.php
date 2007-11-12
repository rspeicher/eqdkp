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
    global $db;
    
    // Get rid of (what would be) invalid duplicate user_auth keys before we add a UNIQUE index
    $sql = "SELECT user_id, auth_id, COUNT(*) as num
            FROM __auth_users
            GROUP BY user_id, auth_id
            HAVING num > 1";
    $result = $db->query($sql);
    while ( $row = $db->fetch_record($result) )
    {
        $row['user_id'] = intval($row['user_id']);
        $row['auth_id'] = intval($row['auth_id']);
        $limit = $row['num'] - 1;
        
        $sql = "DELETE FROM __auth_users 
                WHERE (`user_id` = '{$row['user_id']}')
                AND (`auth_id` = '{$row['auth_id']}')
                LIMIT {$limit}";
        $db->query($sql);
    }
    $db->free_result($result);
    
    // Get rid of (what would be) invalid duplicate raid_member keys before we add a UNIQUE index
    $sql = "SELECT raid_id, member_name, COUNT(*) as num
            FROM __raid_attendees
            GROUP BY raid_id, member_name
            HAVING num > 1";
    $result = $db->query($sql);
    while ( $row = $db->fetch_record($result) )
    {
        $row['raid_id'] = intval($row['raid_id']);
        $row['member_name'] = $db->escape($row['member_name']);
        $limit = $row['num'] - 1;
        
        $sql = "DELETE FROM __raid_attendees
                WHERE (`raid_id` = '{$row['raid_id']}')
                AND (`member_name` = '{$row['member_name']}')
                LIMIT {$limit}";
        $db->query($sql);
    }
    $db->free_result($result);
    
    Upgrade::execute(array(
        // Change auth_users to use a UNIQUE index
        "ALTER TABLE __auth_users DROP INDEX `user_id`",
        "ALTER TABLE __auth_users DROP INDEX `auth_id`",
        "ALTER TABLE __auth_users ADD UNIQUE `user_auth` ( `user_id` , `auth_id` )",

        // Change raid_attendees to use a UNIQUE index
        "ALTER TABLE __raid_attendees DROP INDEX `raid_id`",
        "ALTER TABLE __raid_attendees DROP INDEX `member_name`",
        "ALTER TABLE __raid_attendees ADD UNIQUE `raid_member` ( `raid_id` , `member_name` )",

        // Update the size of all of our float values to larger doubles, since the 1.3 upgrade failed at this
        "ALTER TABLE __adjustments CHANGE `adjustment_value` `adjustment_value` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00",
        "ALTER TABLE __events CHANGE `event_value` `event_value` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00",
        "ALTER TABLE __items CHANGE `item_value` `item_value` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00",
        "ALTER TABLE __members CHANGE `member_earned` `member_earned` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00",
        "ALTER TABLE __members CHANGE `member_spent` `member_spent` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00",
        "ALTER TABLE __members CHANGE `member_adjustment` `member_adjustment` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00",
        "ALTER TABLE __raids CHANGE `raid_value` `raid_value` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'",
    ));
    
    // Finalize
    Upgrade::set_version($VERSION);
    Upgrade::progress($VERSION);
}