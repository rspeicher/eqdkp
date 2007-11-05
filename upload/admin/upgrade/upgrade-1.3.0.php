<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        upgrade-1.3.0.php
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

$VERSION = '1.3.0';

if ( class_exists('Upgrade') && Upgrade::should_run($VERSION) )
{
    Upgrade::set_version($VERSION);
    Upgrade::progress("Completed upgrade to $VERSION.");
}

$queries = array(
    "DROP TABLE IF EXISTS __classes",
    "DROP TABLE IF EXISTS __races",
    "DROP TABLE IF EXISTS __factions",
    
    "CREATE TABLE __classes ( c_index smallint(3) unsigned NOT NULL auto_increment, class_id smallint(3) unsigned NOT NULL, class_name varchar(50) NOT NULL, class_armor_type varchar(50) NOT NULL, class_hide enum('0','1') NOT NULL DEFAULT '0', class_min_level smallint(3) unsigned NOT NULL default '0', class_max_level smallint(3) unsigned NOT NULL DEFAULT '999', PRIMARY KEY (c_index));",
    "CREATE TABLE __races ( race_id smallint(3) unsigned NOT NULL UNIQUE, race_name varchar(50) NOT NULL, race_faction_id smallint(3) NOT NULL, race_hide enum('0','1') NOT NULL DEFAULT '0', PRIMARY KEY (race_id));",
    "CREATE TABLE __factions ( faction_id smallint(3) unsigned NOT NULL UNIQUE, faction_name varchar(50) NOT NULL, faction_hide enum('0','1') NOT NULL DEFAULT '0', PRIMARY KEY (faction_id));",

    "ALTER TABLE __member_ranks MODIFY rank_id smallint(6) NOT NULL default '0';",
    "ALTER TABLE __members MODIFY member_level tinyint(2) NOT NULL default '70';",
    "ALTER TABLE __members ADD member_class_id smallint(3) NOT NULL default '0';",
    "ALTER TABLE __members ADD member_race_id smallint(3) NOT NULL default '0';",
    "ALTER TABLE __items MODIFY item_value float (6,2);",

    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (0, 'Unknown', 'Plate');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (1, 'Warrior', 'Plate');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (2, 'Rogue', 'Chain');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (3, 'Monk', 'Leather');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (4, 'Ranger', 'Chain');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (5, 'Paladin', 'Plate');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (6, 'Shadow Knight', 'Plate');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (7, 'Bard', 'Plate');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (8, 'Beastlord', 'Leather');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (9, 'Cleric', 'Plate');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (10, 'Druid', 'Leather');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (11, 'Shaman', 'Chain');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (12, 'Enchanter', 'Silk');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (13, 'Wizard', 'Silk');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (14, 'Necromancer', 'Silk');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (15, 'Magician', 'Silk');",
    "INSERT IGNORE INTO __classes (class_id, class_name, class_armor_type) VALUES (16, 'Berserker', 'Leather');",

    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (0, 'Unknown');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (1, 'Gnome');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (2, 'Human');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (3, 'Barbarian');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (4, 'Dwarf');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (5, 'High Elf');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (6, 'Dark Elf');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (7, 'Wood Elf');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (8, 'Half Elf');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (9, 'Vah Shir');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (10, 'Troll');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (11, 'Ogre');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (12, 'Frog');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (13, 'Iksar');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (14, 'Erudite');",
    "INSERT IGNORE INTO __races (race_id, race_name) VALUES (15, 'Halfling');",

    "INSERT IGNORE INTO __factions (faction_id, faction_name) VALUES (1, 'Good');",
    "INSERT IGNORE INTO __factions (faction_id, faction_name) VALUES (2, 'Evil');",

    "INSERT INTO __config (config_name, config_value) VALUES ('default_game', 'Everquest');",
    "INSERT INTO __config (config_name, config_value) VALUES ('default_locale', 'en_US');",

    "UPDATE __members m, __classes c SET m.member_class_id = c.class_id WHERE m.member_class = c.class_name;",
    "UPDATE __members m, __races r SET m.member_race_id = r.race_id WHERE m.member_race = r.race_name;",

    "ALTER TABLE __members DROP member_class;",
    "ALTER TABLE __members DROP member_race;",
);