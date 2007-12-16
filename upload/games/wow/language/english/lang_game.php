<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        lang_game.php
 * Began:       Sun Dec 16 2007
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */
 
if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

// Initialize the language array if it isn't already
if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// %1\$<type> prevents a possible error in strings caused
// by another language re-ordering the variables
// $s is a string, $d is an integer, $f is a float

$lang = array_merge($lang, array(
    'GAME_NAME'         => 'World of Warcraft',
    'GAME_ID'           => 'WoW',

    'GAME_CLASS'        => 'Class',
    'GAME_RACE'         => 'Race',

    'ARMOR_CLOTH'       => 'Cloth',
    'ARMOR_LEATHER'     => 'Leather',
    'ARMOR_CHAIN'       => 'Chain',
    'ARMOR_PLATE'       => 'Plate',

    'CLASS_DRUID'       => 'Druid',
    'CLASS_HUNTER'      => 'Hunter',
    'CLASS_MAGE'        => 'Mage',
    'CLASS_PALADIN'     => 'Paladin',
    'CLASS_PRIEST'      => 'Priest',
    'CLASS_ROGUE'       => 'Rogue',
    'CLASS_SHAMAN'      => 'Shaman',
    'CLASS_WARLOCK'     => 'Warlock',
    'CLASS_WARRIOR'     => 'Warrior',

    'FACTION_HORDE'     => 'Horde',
    'FACTION_ALLIANCE'  => 'Alliance',

    'RACE_BLOODELF'     => 'Blood Elf',
    'RACE_DRAENEI'      => 'Draenei',
    'RACE_DWARF'        => 'Dwarf',
    'RACE_GNOME'        => 'Gnome',
    'RACE_HUMAN'        => 'Human',
    'RACE_NIGHTELF'     => 'Night Elf',
    'RACE_ORC'          => 'Orc',
    'RACE_TAUREN'       => 'Tauren',
    'RACE_TROLL'        => 'Troll',
    'RACE_UNDEAD'       => 'Undead',

    'WOW'               => 'World of Warcraft',
    'WORLD_OF_WARCRAFT' => 'World of Warcraft',
));