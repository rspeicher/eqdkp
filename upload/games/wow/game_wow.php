<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        game_wow.php
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
    'id'        => 'wow',
    'name'      => 'World of Warcraft',
    'shortname' => 'WoW',
    'version'   => '1.0',
    'max_level' => 80,
    
    'available' => array(
        'armor_types' => true,
        'classes'     => true,
        'class_armor' => true,
        'factions'    => true,
        'races'       => true,
        
        'professions' => false,
        'parsing'     => true,
    ),
);


if (!isset($get_gameinfo))
{
    $game_data = array(
        // Armor types
        'armor_types'  => array(
            'cloth'        => array('id' => 1, 'name' => 'Cloth'),
            'leather'      => array('id' => 2, 'name' => 'Leather'),
            'mail'         => array('id' => 3, 'name' => 'Mail'),
            'plate'        => array('id' => 4, 'name' => 'Plate'),
        ),
        
        // Classes
        'classes'      => array(
            'druid'    => array(
                'id'       => 1, 
                'name'     => 'Druid', 
                'color'    => 'FF7D0A',
            ),
            'hunter'   => array(
                'id'       => 2, 
                'name'     => 'Hunter', 
                'color'    => 'ABD473',
            ),
            'mage'     => array(
                'id'       => 3, 
                'name'     => 'Mage', 
                'color'    => '69CCF0',
            ),
            'paladin'  => array(
                'id'       => 4, 
                'name'     => 'Paladin', 
                'color'    => 'F58CBA',
            ),
            'priest'   => array(
                'id'       => 5, 
                'name'     => 'Priest', 
                'color'    => 'FFFFFF',
            ),
            'rogue'    => array(
                'id'       => 6, 
                'name'     => 'Rogue', 
                'color'    => 'FFF569',
            ),
            'shaman'   => array(
                'id'       => 7, 
                'name'     => 'Shaman', 
                'color'    => '2459FF',
            ),
            'warlock'  => array(
                'id'       => 8, 
                'name'     => 'Warlock', 
                'color'    => '9482CA',
            ),
            'warrior'  => array(
                'id'       => 9, 
                'name'     => 'Warrior', 
                'color'    => 'C79C6E',
            ),
            'death_knight' => array(
                'id'       => 10, 
                'name'     => 'Death Knight', 
                'color'    => 'C41E3B',
            ),
        ),
        
        // Class-Armor mappings
        'class_armor'  => array(
            array('class' => 'death_knight', 'armor' => 'plate'),
            array('class' => 'druid',   'armor' => 'leather'),
            array('class' => 'hunter',  'armor' => 'leather'),
            array('class' => 'hunter',  'armor' => 'mail', 'min' => 40),
            array('class' => 'mage',    'armor' => 'cloth'),
            array('class' => 'paladin', 'armor' => 'mail'),
            array('class' => 'paladin', 'armor' => 'plate', 'min' => 40),
            array('class' => 'priest',  'armor' => 'cloth'),
            array('class' => 'rogue',   'armor' => 'leather'),
            array('class' => 'shaman',  'armor' => 'leather'),
            array('class' => 'shaman',  'armor' => 'mail', 'min' => 40),
            array('class' => 'warlock', 'armor' => 'cloth'),
            array('class' => 'warrior', 'armor' => 'mail'),
            array('class' => 'warrior', 'armor' => 'plate', 'min' => 40),
        ),
        
        // Factions
        'factions'     => array(
            'alliance' => array('id' => 1, 'name' => 'Alliance', 'races' => array('human','draenei','dwarf','gnome','night_elf')),
            'horde'    => array('id' => 2, 'name' => 'Horde', 'races' => array('blood_elf','orc','tauren','troll','undead')),
        ),

        // Races
        'races'        => array(
            'blood_elf'  => array(
                'id'       => 10, 
                'name'     => 'Blood Elf', 
                'faction'  => 'horde',
                'classes'  => array('death_knight','priest','rogue','mage','hunter','warlock','paladin')
            ),
            'human'    => array(
                'id'       => 2, 
                'name'     => 'Human', 
                'faction'  => 'alliance',
                'classes'  => array('death_knight','priest','rogue','warrior','mage','warlock','paladin')
            ),
            'draenei'  => array(
                'id'       => 9, 
                'name'     => 'Draenei', 
                'faction'  => 'alliance',
                'classes'  => array('death_knight','priest','warrior','mage','hunter','shaman','paladin')
            ),
            'dwarf'    => array(
                'id'       => 3, 
                'name'     => 'Dwarf', 
                'faction'  => 'alliance',
                'classes'  => array('death_knight','priest','rogue','warrior','hunter')
            ),
            'gnome'    => array(
                'id'       => 1, 
                'name'     => 'Gnome', 
                'faction'  => 'alliance',
                'classes'  => array('death_knight','rogue','warrior','mage','warlock')
            ),
            'night_elf'  => array(
                'id'       => 4, 
                'name'     => 'Night Elf', 
                'faction'  => 'alliance',
                'classes'  => array('death_knight','priest','rogue','warrior','druid','hunter')
            ),
            'orc'      => array(
                'id'       => 7, 
                'name'     => 'Orc', 
                'faction'  => 'horde',
                'classes'  => array('death_knight','rogue','warrior','hunter','warlock','shaman')
            ),
            'tauren'   => array(
                'id'       => 8, 
                'name'     => 'Tauren', 
                'faction'  => 'horde',
                'classes'  => array('death_knight','warrior','druid','hunter','shaman')
            ),
            'troll'    => array(
                'id'       => 5, 
                'name'     => 'Troll', 
                'faction'  => 'horde',
                'classes'  => array('death_knight','priest','rogue','warrior','mage','hunter','shaman')
            ),
            'undead'   => array(
                'id'       => 6, 
                'name'     => 'Undead', 
                'faction'  => 'horde',
                'classes'  => array('death_knight','priest','rogue','warrior','mage','warlock')
            ),
        ),
        
        'parsing'      => array(
            //[Dazza]: Level 60 Night Elf Priest <Banimal> - Winterspring
            //[Kamien]: Level 70 Undead Rogue <Juggernaut> - Black Temple
            '[__name__]: Level __level__ __race__ __class__? <__guild__>?? - __zone__?',
        ),
    );
}
?>