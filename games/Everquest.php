<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * Everquest.php
 * Began: Fri May 13 2005
 *
 * $Id: Everquest.php 6 2006-05-08 17:11:35Z tsigo $
 *
 ******************************/

if ( !defined('EQDKP_INC') )
{
    die('Hacking attempt');
}



class Manage_Game extends EQdkp_Admin
{ 
    function do_it()
    {
        global $db, $eqdkp, $user;
        global $SID, $dbname;

        parent::eqdkp_admin();

        $queries = array(
            "ALTER TABLE __members MODIFY member_level tinyint(2) NOT NULL default '70';",
            
            "TRUNCATE TABLE __classes;",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (0, 'Unknown', 'Plate');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (1, 'Warrior', 'Plate');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (2, 'Rogue', 'Chain');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (3, 'Monk', 'Leather');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (4, 'Ranger', 'Chain');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (5, 'Paladin', 'Plate');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (6, 'Shadow Knight', 'Plate');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (7, 'Bard', 'Plate');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (8, 'Beastlord', 'Leather');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (9, 'Cleric', 'Plate');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (10, 'Druid', 'Leather');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (11, 'Shaman', 'Chain');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (12, 'Enchanter', 'Silk');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (13, 'Wizard', 'Silk');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (14, 'Necromancer', 'Silk');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (15, 'Magician', 'Silk');",
            "INSERT INTO __classes (class_id, class_name, class_armor_type) VALUES (16, 'Berserker', 'Leather');",

            "TRUNCATE TABLE __races;",
            "INSERT INTO __races (race_id, race_name) VALUES (0, 'Unknown');",
            "INSERT INTO __races (race_id, race_name) VALUES (1, 'Gnome');",
            "INSERT INTO __races (race_id, race_name) VALUES (2, 'Human');",
            "INSERT INTO __races (race_id, race_name) VALUES (3, 'Barbarian');",
            "INSERT INTO __races (race_id, race_name) VALUES (4, 'Dwarf');",
            "INSERT INTO __races (race_id, race_name) VALUES (5, 'High Elf');",
            "INSERT INTO __races (race_id, race_name) VALUES (6, 'Dark Elf');",
            "INSERT INTO __races (race_id, race_name) VALUES (7, 'Wood Elf');",
            "INSERT INTO __races (race_id, race_name) VALUES (8, 'Half Elf');",
            "INSERT INTO __races (race_id, race_name) VALUES (9, 'Vah Shir');",
            "INSERT INTO __races (race_id, race_name) VALUES (10, 'Troll');",
            "INSERT INTO __races (race_id, race_name) VALUES (11, 'Ogre');",
            "INSERT INTO __races (race_id, race_name) VALUES (12, 'Frog');",
            "INSERT INTO __races (race_id, race_name) VALUES (13, 'Iksar');",
            "INSERT INTO __races (race_id, race_name) VALUES (14, 'Erudite');",
            "INSERT INTO __races (race_id, race_name) VALUES (15, 'Halfling');",

            "TRUNCATE TABLE __factions;",
            "INSERT INTO __factions (faction_id, faction_name) VALUES (1, 'Good');",
            "INSERT INTO __factions (faction_id, faction_name) VALUES (2, 'Evil');",
            
            "UPDATE __config SET config_value = 'Everquest' WHERE config_name = 'default_game';",
        );

        foreach ( $queries as $sql )
        {
            $db->query($sql);
        }
        
        redirect("admin/config.php");
    }
}

$manage = new Manage_Game;
$manage->do_it();