<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        game_everquest.php
 * Began:       Thu Nov 15 2007
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2008 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     games
 * @version     $Rev$
 */

if (!defined('EQDKP_INC') || !defined('IN_GAME_MANAGER'))
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

$game_info = array(
    'id'        => 'everquest',
    'name'      => 'EverQuest',
    'shortname' => 'EQ',
    'version'   => '1.0',
    'max_level' => 70,
    
    'available' => array(
        'armor_types' => true,
        'classes'     => true,
        'class_armor' => true,
        'factions'    => true,
        'races'       => true,
        
        'professions' => false,
        'parsing'     => false,
    ),
);


if (!isset($get_gameinfo))
{
    $game_data = array(
        // Armor types
        'armor_types'  => array(
            'silk'         => array('id' => 1, 'name' => 'Silk'),
            'leather'      => array('id' => 2, 'name' => 'Leather'),
            'chain'        => array('id' => 3, 'name' => 'Chain'),
            'plate'        => array('id' => 4, 'name' => 'Plate'),
        ),
        
        // Classes
        'classes'      => array(
            'bard'     => array(
                'id'       => 7, 
                'name'     => 'Bard', 
                'color'    => '',
            ),
            'beastlord' => array(
                'id'       => 8, 
                'name'     => 'Beastlord', 
                'color'    => '',
            ),
            'beserker' => array(
                'id'       => 16, 
                'name'     => 'Beserker', 
                'color'    => '',
            ),
            'cleric'   => array(
                'id'       => 9, 
                'name'     => 'Cleric', 
                'color'    => '',
            ),
            'druid'    => array(
                'id'       => 10, 
                'name'     => 'Druid', 
                'color'    => '',
            ),
            'enchanter' => array(
                'id'       => 12, 
                'name'     => 'Enchanter', 
                'color'    => '',
            ),
            'magician' => array(
                'id'       => 15, 
                'name'     => 'Magician', 
                'color'    => '',
            ),
            'monk'     => array(
                'id'       => 3, 
                'name'     => 'Monk', 
                'color'    => '',
            ),
            'necromancer' => array(
                'id'       => 14, 
                'name'     => 'Necromancer', 
                'color'    => '',
            ),
           'paladin'  => array(
                'id'       => 5, 
                'name'     => 'Paladin', 
                'color'    => '',
            ),
           'ranger'   => array(
                'id'       => 4, 
                'name'     => 'Ranger', 
                'color'    => '',
            ),
           'rogue'    => array(
                'id'       => 2, 
                'name'     => 'Rogue', 
                'color'    => '',
            ),
           'shadowknight' => array(
                'id'       => 6, 
                'name'     => 'Shadow Knight', 
                'color'    => '',
            ),
           'shaman'   => array(
                'id'       => 11, 
                'name'     => 'Shaman', 
                'color'    => '',
            ),
           'warrior'  => array(
                'id'       => 1, 
                'name'     => 'Warrior', 
                'color'    => '',
            ),
           'wizard'   => array(
                'id'       => 13, 
                'name'     => 'Wizard', 
                'color'    => '',
            ),
        ),
        
        // Class-Armor mappings
        'class_armor'  => array(
            array('class' => 'bard',        'armor' => 'plate'),
            array('class' => 'beastlord',   'armor' => 'leather'),
            array('class' => 'beserker',    'armor' => 'leather'),
            array('class' => 'cleric',      'armor' => 'plate'),
            array('class' => 'druid',       'armor' => 'leather'),
            array('class' => 'enchanter',   'armor' => 'silk'),
            array('class' => 'magician',    'armor' => 'silk'),
            array('class' => 'monk',        'armor' => 'leather'),
            array('class' => 'necromancer', 'armor' => 'silk'),
            array('class' => 'paladin',     'armor' => 'plate'),
            array('class' => 'ranger',      'armor' => 'chain'),
            array('class' => 'rogue',       'armor' => 'chain'),
            array('class' => 'shadowknight', 'armor' => 'plate'),
            array('class' => 'shaman',      'armor' => 'chain'),
            array('class' => 'warrior',     'armor' => 'plate'),
            array('class' => 'wizard',      'armor' => 'silk'),
        ),
        
        // Factions
        'factions'     => array(
            'good'     => array('id' => 1, 'name' => 'Good'),
            'evil'     => array('id' => 2, 'name' => 'Evil'),
        ),

        // Races
        'races'        => array(
            'gnome' => array(
                'id'       => 1, 
                'name'     => 'Gnome', 
                'faction'  => 'good',
            ),
            'human' => array(
                'id'       => 2, 
                'name'     => 'Human', 
                'faction'  => 'good',
            ),
            'barbarian' => array(
                'id'       => 3, 
                'name'     => 'Barbarian', 
                'faction'  => 'good',
            ),
            'dwarf' => array(
                'id'       => 4, 
                'name'     => 'Dwarf', 
                'faction'  => 'good',
            ),
            'highelf' => array(
                'id'       => 5, 
                'name'     => 'High Elf', 
                'faction'  => 'good',
            ),
            'darkelf' => array(
                'id'       => 6, 
                'name'     => 'Dark Elf', 
                'faction'  => 'good',
            ),
            'woodelf' => array(
                'id'       => 7, 
                'name'     => 'Wood Elf', 
                'faction'  => 'good',
            ),
            'halfelf' => array(
                'id'       => 8, 
                'name'     => 'Half Elf', 
                'faction'  => 'good',
            ),
            'vahshir' => array(
                'id'       => 9, 
                'name'     => 'Vah Shir', 
                'faction'  => 'good',
            ),
            'troll' => array(
                'id'       => 10, 
                'name'     => 'Troll', 
                'faction'  => 'good',
            ),
            'ogre' => array(
                'id'       => 11, 
                'name'     => 'Ogre', 
                'faction'  => 'good',
            ),
            'frog' => array(
                'id'       => 12, 
                'name'     => 'Frog', 
                'faction'  => 'good',
            ),
            'iksar' => array(
                'id'       => 13, 
                'name'     => 'Iksar', 
                'faction'  => 'good',
            ),
            'erudite' => array(
                'id'       => 14, 
                'name'     => 'Erudite', 
                'faction'  => 'good',
            ),
            'halfling' => array(
                'id'       => 15, 
                'name'     => 'Halfling', 
                'faction'  => 'good',
            ),
        ),
    );
}

?>