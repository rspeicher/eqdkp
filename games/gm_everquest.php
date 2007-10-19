<?php
if ( !defined('EQDKP_INC') )
{
     die('Do not access this file directly.');
}

class GM_Everquest extends Game_Manager
{
    var $_classes = array();
    
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
            
            "UPDATE __config SET config_value = 'EverQuest' WHERE config_name = 'default_game';",
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
        if ( count($_classes) == 0 )
        {
            $this->_classes = array(
                'Bard'          => array('Bard','Minstrel','Troubadour','Virtuoso','Maestro'),
                'Beastlord'     => array('Beastlord','Primalist','Animist','Savage Lord','Feral Lord'),
                'Berserker'     => array('Berserker','Brawler','Vehement','Rager','Fury'),
                'Cleric'        => array('Cleric','Vicar','Templar','High Priest','Archon'),
                'Druid'         => array('Druid','Wanderer','Preserver','Hierophant','Storm Warden'),
                'Enchanter'     => array('Enchanter','Illusionist','Beguiler','Phantasmist','Coercer'),
                'Magician'      => array('Magician','Elementalist','Conjurer','Arch Mage','Arch Convoker'),
                'Monk'          => array('Monk','Disciple','Master','Grandmaster','Transcendent'),
                'Necromancer'   => array('Necromancer','Heretic','Defiler','Warlock','Arch Lich'),
                'Paladin'       => array('Paladin','Cavalier','Knight','Crusader','Lord Protector'),
                'Ranger'        => array('Ranger','Pathfinder','Outrider','Warder','Hunter','Forest Stalker'),
                'Rogue'         => array('Rogue','Rake','Blackguard','Assassin','Deceiver'),
                'Shadow Knight' => array('Scourge Knight','Shadow Knight','Reaver','Revenant','Grave Lord','Dread Lord'),
                'Shaman'        => array('Shaman','Mystic','Luminary','Oracle','Prophet'),
                'Warrior'       => array('Warrior','Champion','Myrmidon','Warlord','Overlord'),
                'Wizard'        => array('Wizard','Channeler','Evoker','Sorcerer','Arcanist')
            );
        }
    }
    
    /**
     * Returns the original EverQuest class name given a level-based alias
     * Example: getOriginalClass('Blackguard') returns 'Rogue'
     *
     * @param string $class Class name
     * @return string
     */
    function getOriginalClass($class)
    {
        if ( count($this->_classes) == 0 )
        {
            $this->initParser();
        }
        
        foreach ( $this->_classes as $k => $v)
        {
            if ( in_array($class, $v) )
            {
                return $k;
            }
        }
        
        return '';
    }
}