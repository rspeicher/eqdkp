<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * WoW.php
 * Began: Fri May 13 2005
 *
 * $Id: WoW-german.php 6 2006-05-08 17:11:35Z tsigo $
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
            "UPDATE __members SET member_level = 70 WHERE member_level > 70;",
            "ALTER TABLE __members MODIFY member_level tinyint(2) NOT NULL default '60';",
            
            // FIXME: English has 14 classes and German has 12? Whatever.
            "TRUNCATE TABLE __classes;",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (0, 'Unknown', 'Platte',0,60);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (1, 'Krieger', 'Platte',0,60);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (2, 'Schurke', 'Leder',0,60);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (3, 'Jäger', 'Leder',0,39);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (4, 'Jäger', 'Schwere Rüstung',40,60);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (5, 'Paladin', 'Platte',0,60);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (6, 'Priester', 'Stoff',0,60);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (7, 'Druide', 'Stoff',0,60);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (8, 'Schamane', 'Leder',0,39);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (9, 'Schamane', 'Schwere Rüstung',40,60);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (10, 'Hexenmeister', 'Stoff',0,60);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (11, 'Magier', 'Stoff',0,60);",
            
            // Woah, they got the Faction count right!
            "DELETE FROM __factions;",
            "INSERT INTO __factions (faction_id, faction_name) VALUES (1, 'Allianz');",
            "INSERT INTO __factions (faction_id, faction_name) VALUES (2, 'Horde');",
            
            // FIXME: Again, there are more races in English than German.
            "TRUNCATE TABLE __races;",
            "INSERT INTO __races (race_id, race_name) VALUES (0, 'Unknown');",
            "INSERT INTO __races (race_id, race_name) VALUES (1, 'Gnom');",
            "INSERT INTO __races (race_id, race_name) VALUES (2, 'Mensch');",
            "INSERT INTO __races (race_id, race_name) VALUES (3, 'Zwerg');",
            "INSERT INTO __races (race_id, race_name) VALUES (4, 'Nachtelf');",
            "INSERT INTO __races (race_id, race_name) VALUES (5, 'Troll');",
            "INSERT INTO __races (race_id, race_name) VALUES (6, 'Untoter');",
            "INSERT INTO __races (race_id, race_name) VALUES (7, 'Ork');",
            "INSERT INTO __races (race_id, race_name) VALUES (8, 'Taure');",
            
            "UPDATE __config SET config_value = 'WoW' WHERE config_name = 'default_game';",
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