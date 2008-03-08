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

// FIXME: This needs to change as we decide our beta-testing plan
$VERSION = '1.4.0 B1';

if ( class_exists('Upgrade') && Upgrade::should_run($VERSION) )
{
    global $db;
    
    // Make sure the files that were deleted for this version are, in fact, missing
    Upgrade::assert_deleted(array(
        '/ChangeLog',
        // '/COPYING',
        '/INSTALL',
        // '/LICENSE',
        '/README',
        '/install.php',
        '/upgrade.php',
        '/admin/mm/mm_addmember.php.NEW.gz',
        '/admin/soap/',
        '/admin/config.php',
        '/admin/fix_negative.php',
        '/admin/lua.php',
        '/admin/lua_config.php',
        '/admin/DAoC.php',
        '/admin/Everquest.php',
        '/admin/Everquest2-german.sql',
        '/admin/Everquest2.php',
        '/admin/WoW-english.php',
        '/admin/WoW-german.php',
        '/admin/WoW.php',
        '/dbal/',
        '/games/DAoC.php',
        '/games/Everquest.php',
        '/games/Everquest2.php',
        '/games/Vanguard-SoH.php',
        '/games/WoW-german.php',
        '/games/WoW.php',
        '/includes/lib/',
        '/includes/file_upload.php',
        '/includes/nusoap.php',
        '/images/arrow.gif',
        '/images/error.gif',
        '/images/glyphs/',
        '/images/statbox.jpg',
        '/images/view.gif',
        '/templates/default/admin/config.html',
        '/templates/default/admin/lua.html',
        '/templates/default/admin/menu.html',
    ));
    
    $db->sql_transaction('begin');
    
    Upgrade::prepare_uniquekey('auth_users',     array('user_id', 'auth_id'));
    Upgrade::prepare_uniquekey('raid_attendees', array('raid_id', 'member_name'));
    
    // Determine what the currently installed game is
    $sql = "SELECT config_value 
            FROM __config
            WHERE `config_name` = 'default_game'";
    $game_name = $db->query_first($sql);


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
        "ALTER TABLE __adjustments CHANGE `adjustment_value` `adjustment_value` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'",
        "ALTER TABLE __events CHANGE `event_value` `event_value` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'",
        "ALTER TABLE __items CHANGE `item_value` `item_value` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'",
        "ALTER TABLE __members CHANGE `member_earned` `member_earned` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'",
        "ALTER TABLE __members CHANGE `member_spent` `member_spent` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'",
        "ALTER TABLE __members CHANGE `member_adjustment` `member_adjustment` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'",
        "ALTER TABLE __raids CHANGE `raid_value` `raid_value` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'",
        
        // New session and user management
        "DELETE FROM __config WHERE (config_name IN ('session_cleanup','cookie_domain','cookie_path'))", // Unused config values
        "ALTER TABLE __users CHANGE `username` `user_name` VARCHAR( 30 ) NOT NULL", // username to user_name
        "ALTER TABLE __sessions CHANGE `session_user_id` `user_id` SMALLINT( 5 ) NOT NULL DEFAULT '-1'", // session_user_id to user_id
        "ALTER TABLE __users CHANGE `user_password` `user_password` VARCHAR( 40 ) NOT NULL", // Increase user_password length to 40, for SHA1 hashes
        "ALTER TABLE __users CHANGE `user_newpassword` `user_newpassword` VARCHAR( 40 ) NULL DEFAULT NULL",
        "ALTER TABLE __users ADD `user_salt` VARCHAR( 40 ) NOT NULL AFTER `user_password`",
        "ALTER TABLE __sessions DROP INDEX `session_current`",
        "ALTER TABLE __sessions DROP `session_last_visit`",
    ));
    
    // New Game Management changes
    Upgrade::execute(array(
        // Update the default game values
        "INSERT INTO __config (`config_name`, `config_value`) VALUES ('current_game_name', '" . $game_name . "')",
        "UPDATE __config SET `config_name` = 'current_game' WHERE `config_name` = 'default_game' LIMIT 1",
        
        // Add the new game tables
        "CREATE TABLE IF NOT EXISTS __armor_types (
          `armor_type_id` smallint(3) unsigned NOT NULL UNIQUE,
          `armor_type_name` varchar(50) NOT NULL,
          `armor_type_key` varchar(30) NOT NULL,
          PRIMARY KEY (`armor_type_id`)
        )TYPE=InnoDB;",
        
        "CREATE TABLE IF NOT EXISTS __class_armor (
          `class_id` smallint(3) unsigned NOT NULL UNIQUE,
          `armor_type_id` smallint(3) unsigned NOT NULL,
          `armor_min_level` smallint(3) NOT NULL DEFAULT '0',
          `armor_max_level` smallint(3),
          PRIMARY KEY (`class_id`, `armor_type_id`),
          INDEX classes (`class_id`),
          INDEX armor_types (`armor_type_id`)
        )TYPE=InnoDB;",
        
        // Re-name the old classes table. We'll be using it to normalize and add the old game data.
        "RENAME TABLE __classes TO __classes_old;",
        
        // Add the new classes table. We'll be calling it classes_new for the time being.
        "CREATE TABLE IF NOT EXISTS __classes_new (
          `class_id` smallint(3) unsigned NOT NULL UNIQUE,
          `class_name` varchar(50) NOT NULL,
          `class_key` varchar(30) NOT NULL,
          `class_hide` enum('0','1') NOT NULL DEFAULT '0',
          PRIMARY KEY (`class_id`)
        )TYPE=InnoDB;",
    ));
    
    // Normalize the data from the old classes table into the new ones.
    normalize_game_tables();

    // Update the member class IDs
    update_member_classes();

    // Cleanup for the classes table
    Upgrade::execute(array(
        "RENAME TABLE __classes_new TO __classes;",
        "DROP TABLE __classes_old;",
    ));
    
    // Create game data language key fields for the races and factions tables
    Upgrade::execute(array(
        "ALTER TABLE __factions ADD `faction_key` VARCHAR( 30 ) NOT NULL",
        "ALTER TABLE __races ADD `race_key` VARCHAR( 30 ) NOT NULL",
    ));
    
    // Populate the game data language key field values
    game_keys('faction');
    game_keys('race');
    
    // Generate an installation-specific unique salt value
    $auth_salt = generate_salt();
    Upgrade::execute(array(
        "REPLACE INTO __config (config_name, config_value) VALUES ('auth_salt', '{$auth_salt}')",
    ));
    
    // Finalize
    $db->sql_transaction('commit');
    
    Upgrade::set_version($VERSION);
    Upgrade::progress($VERSION);
}

/**
 * Normalizes the old classes table into the three new tables
 *
 * @return void
 */
function normalize_game_tables()
{
    global $db;
    
    $old_class_dbtable      = "__classes_old";
    $new_class_dbtable      = "__classes_new";
    $new_armor_dbtable      = "__armor_types";
    $new_classarmor_dbtable = "__class_armor";
    
    $armortype_sql = array("INSERT INTO `" . $new_armor_dbtable . "` (`armor_type_id`, `armor_type_name`, `armor_type_key`) VALUES ('0', 'None', 'none')");
    $class_sql     = array("INSERT INTO `" . $new_class_dbtable . "` (`class_id`, `class_name`, `class_key`) VALUES ('0', 'Unknown', 'unknown')");
    
    //
    // Get all the unique armor types, and add them to the armor type SQL array
    //
    $result = $db->sql_query("SELECT DISTINCT `class_armor_type` FROM `" . $old_class_dbtable . "` ORDER BY `c_index`");

    $armor_type_count = 1;
    while ($row = $db->sql_fetchrow($result))
    {
        // There's a special case for the 'Unknown' armor type. We'll be calling this armor type 'None'.
        if (strcasecmp($row['class_armor_type'], 'Unknown') == 0)
        {
            continue;
        }
    
        // Create the armor type SQL
        $armortype_sql[] = "INSERT INTO `" . $new_armor_dbtable . "` (
                        `armor_type_id`, 
                        `armor_type_name`, 
                        `armor_type_key`
                    ) 
                    VALUES (
                        '" . $armor_type_count . "', 
                        '" . $row['class_armor_type'] . "', 
                        '" . game_key_value($row['class_armor_type']) . "'
                    )";
        $armor_type_count++;
    }
    $db->sql_freeresult($result);
    
    //
    // Get all the unique classes, and add them to the class SQL array
    //
    $result = $db->sql_query("SELECT DISTINCT `class_name` FROM `" . $old_class_dbtable . "` ORDER BY `class_name`");

    $class_count = 1;
    while ($row = $db->sql_fetchrow($result))
    {
        // Spacial case for the Unknown class type.
        if (strcasecmp($row['class_name'], 'Unknown') == 0)
        {
            continue;
        }
    
        // Create the class SQL
        $class_sql[] = "INSERT INTO `" . $new_class_dbtable . "` (
                        `class_id`, 
                        `class_name`, 
                        `class_key`
                    ) 
                    VALUES (
                        '" . $class_count . "', 
                        '" . $row['class_name'] . "', 
                        '" . game_key_value($row['class_name']) . "'
                    )";
        $class_count++;
    }
    $db->sql_freeresult($result);
    
    
    //
    // Insert all the unique classes and armor types into the new tables
    //
    ksort($class_sql);
    ksort($armortype_sql);
    foreach (array_merge($class_sql, $armortype_sql) as $sql)
    {
        $db->sql_query($sql);
    }
    
    
    //
    // Now we'll do a few table joins to get our class-armor mappings.
    //
    $classarmor_sql = array("REPLACE INTO " . $new_classarmor_dbtable . " (class_id, armor_type_id, armor_min_level, armor_max_level) VALUES ('0', '0', '0', NULL)");
    
    $sql = "SELECT oldclass.`class_min_level`, oldclass.`class_max_level`, at.`armor_type_id` AS new_armor_type_id, class.`class_id` AS new_class_id 
                FROM `" . $old_class_dbtable . "` AS oldclass
                LEFT JOIN `" . $new_class_dbtable . "` AS class ON oldclass.`class_name` = class.`class_name`
                LEFT JOIN `" . $new_armor_dbtable . "` AS at ON oldclass.`class_armor_type` = at.`armor_type_name`";
    $result = $db->sql_query($sql);
    
    // Construct the SQL for the class-armor mappings, and add it to an array of SQL statements to execute later.
    while ($row = $db->sql_fetchrow($result))
    {
        // Special case for the unknown class type
        if ($row['new_class_id'] == 0)
        {
            continue;
        }

        // Construct the class-armor mapping SQL
        $query = $db->build_query('INSERT', array(
            'class_id'        => $row['new_class_id'],
            'armor_type_id'   => $row['new_armor_type_id'],
            'armor_min_level' => $row['class_min_level'],
            'armor_max_level' => $row['class_max_level'],
        ));
        $classarmor_sql[] = "REPLACE INTO {$new_classarmor_dbtable} {$query}";
    }
    $db->sql_freeresult($result);


    // To offer more robustness, we're going to make an additional mapping for all classes with the 'None' armor type.
    // NOTE: We're selecting from the NEW class table, so we're only getting unique IDs.
    $sql = "SELECT class.`class_id`, at.`armor_type_id` 
                FROM `" . $new_class_dbtable . "` AS class, `" . $new_armor_dbtable . "` AS at 
                WHERE at.`armor_type_key` = 'none'";
    $result = $db->sql_query($sql);
    
    // Construct the SQL for the class-armor mappings, and add it to an array of SQL statements to execute later.
    while ($row = $db->sql_fetchrow($result))
    {
        $query = $db->build_query('INSERT', array(
            'class_id'        => $row['class_id'],
            'armor_type_id'   => $row['armor_type_id'],
            'armor_min_level' => 0,
            'armor_max_level' => NULL,
        ));
        $classarmor_sql[] = "REPLACE INTO {$new_classarmor_dbtable} {$query}";
    }
    $db->sql_freeresult($result);


    // Now add all the class-armor mappings to the database
    foreach ($classarmor_sql as $sql)
    {
        $db->sql_query($sql);
    }
}


/**
 * Updates the members class IDs.
 *
 * This function will retrieve a list of all members, combined with the old and new class ID values.
 * Then, each one will be updated individually. Doing things this way may not be the most efficient
 * solution in terms of database access, but it is a sure-fire approach that will guarantee the correct
 * values get set.
 */
function update_member_classes()
{
    global $db;
    
    $old_class_dbtable = "__classes_old";
    $new_class_dbtable = "__classes_new";

    // Select all the members, and get the old -> new class ID for each.
    $sql = "SELECT member.`member_id`, oldclass.`class_id` AS old_class_id, newclass.`class_id` AS new_class_id
                FROM __members AS member 
                LEFT JOIN " . $old_class_dbtable . " AS oldclass ON member.`member_class_id` = oldclass.`class_id`
                LEFT JOIN " . $new_class_dbtable . " AS newclass ON oldclass.`class_name` = newclass.`class_name`";
    $result = $db->sql_query($sql);
    
    // Build a list of update queries to set all the member's class IDs to their new values.
    $member_class_sql = array();
    while ($row = $db->sql_fetchrow($result))
    {
        $member_class_sql[] = "UPDATE __members SET member_class_id = '" . $row['new_class_id'] . "' WHERE member_id = '" . $row['member_id'] . "'";
    }
    $db->sql_freeresult($result);
    
    // Execute the updates
    foreach($member_class_sql as $sql)
    {
        $db->sql_query($sql);
    }
}


/**
 * Populates one of the class/faction/race language key fields
 *
 * @param string $type class | faction | race
 * @return void
 */
function game_keys($type)
{
    global $db;
    
    // Pluralize table name
    $table = ( $type == 'class' ) ? 'classes' : $type . 's';
    $name  = "{$type}_name";
    $key   = "{$type}_key";
    
    $sql = "SELECT {$name} FROM __{$table} ORDER BY {$name}";
    $result = $db->query($sql);
    while ( $row = $db->fetch_record($result) )
    {
        $key_val = game_key_value($row[$name]);
        $db->query("UPDATE __{$table} SET :params WHERE ({$name} = '" . $db->escape($row[$name]) . "')", array(
            $key => $key_val
        ));
    }
    $db->free_result($result);
}

/**
 * Returns a game entity name in the new key format, that can be used as an index in an associative array.
 */
function game_key_value($name)
{
    return preg_replace('/[^\w]/', '_', strtolower($name));
}
