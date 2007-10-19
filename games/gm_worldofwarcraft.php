<?php
if ( !defined('EQDKP_INC') )
{
     die('Do not access this file directly.');
}

class GM_Worldofwarcraft extends Game_Manager
{
    ## #########################################################################
    ## Database populating
    ## #########################################################################
    
    /**
     * Populate the __classes, __races and __factions tables with game-specific
     * values.
     */
    function initDatabase()
    {
        global $db;
        
        $queries = array(
            "UPDATE __members SET member_level = 70 WHERE member_level > 70;",
            "ALTER TABLE __members MODIFY member_level tinyint(2) NOT NULL default '60';",

            "TRUNCATE TABLE __classes;",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (0, 'Unknown', 'Plate',0,70);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (1, 'Warrior', 'Mail',0,39);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (2, 'Rogue', 'Leather',0,70);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (3, 'Hunter', 'Leather',0,39);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (4, 'Hunter', 'Mail',40,70);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (5, 'Paladin', 'Mail',0,39);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (6, 'Priest', 'Cloth',0,70);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (7, 'Druid', 'Leather',0,70);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (8, 'Shaman', 'Leather',0,39);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (9, 'Shaman', 'Mail',40,70);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (10, 'Warlock', 'Cloth',0,70);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (11, 'Mage', 'Cloth',0,70);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (12, 'Warrior', 'Plate',40,70);",
            "INSERT INTO __classes (class_id, class_name, class_armor_type, class_min_level, class_max_level) VALUES (13, 'Paladin', 'Plate',40,70);",

            "TRUNCATE TABLE __factions;",
            "INSERT INTO __factions (faction_id, faction_name) VALUES (1, 'Alliance');",
            "INSERT INTO __factions (faction_id, faction_name) VALUES (2, 'Horde');",

            "TRUNCATE TABLE __races;",
            "INSERT INTO __races (race_id, race_name) VALUES (0, 'Unknown');",
            "INSERT INTO __races (race_id, race_name, race_faction_id) VALUES (1, 'Gnome', 1);",
            "INSERT INTO __races (race_id, race_name, race_faction_id) VALUES (2, 'Human', 1);",
            "INSERT INTO __races (race_id, race_name, race_faction_id) VALUES (3, 'Dwarf', 1);",
            "INSERT INTO __races (race_id, race_name, race_faction_id) VALUES (4, 'Night Elf', 1);",
            "INSERT INTO __races (race_id, race_name, race_faction_id) VALUES (5, 'Troll', 2);",
            "INSERT INTO __races (race_id, race_name, race_faction_id) VALUES (6, 'Undead', 2);",
            "INSERT INTO __races (race_id, race_name, race_faction_id) VALUES (7, 'Orc', 2);",
            "INSERT INTO __races (race_id, race_name, race_faction_id) VALUES (8, 'Tauren', 2);",
            "INSERT INTO __races (race_id, race_name, race_faction_id) VALUES (9, 'Draenei', 1);",
            "INSERT INTO __races (race_id, race_name, race_faction_id) VALUES (10, 'Blood Elf', 2);",

            "UPDATE __config SET config_value = 'World of Warcraft' WHERE config_name = 'default_game';",
        );

        foreach ( $queries as $sql )
        {
            $db->query($sql);
        }
    }
    
    ## #########################################################################
    ## Log Parsing
    ## #########################################################################
    
    /**
     * Initialize the environment for log parsing
     */
    function initParser()
    {
    }
    
    /**
     * NOTE: Only here in case we make Game_Manager into an interface and this 
     * method ends up being required
     *
     * @param string $class Class name
     * @return string
     */
    function getOriginalClass($class)
    {
        return $class;
    }
}