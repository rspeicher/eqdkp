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
 * @copyright   2002-2007 The EQdkp Project Team
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
			'cloth'        => array('id' => 1, 'name' => 'Cloth'),
			'leather'      => array('id' => 2, 'name' => 'Leather'),
			'chain'        => array('id' => 3, 'name' => 'Chain'),
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
/*           'deathknight'  => array(
				'id'       => 10, 
				'name'     => 'Death Knight', 
				'color'    => '000000',
			),
*/		),
		
		// Class-Armor mappings
		'class_armor'  => array(
			array('class' => 'druid',   'armor' => 'leather'),
			array('class' => 'hunter',  'armor' => 'leather'),
			array('class' => 'hunter',  'armor' => 'chain', 'min' => 40),
			array('class' => 'mage',    'armor' => 'cloth'),
			array('class' => 'paladin', 'armor' => 'chain'),
			array('class' => 'paladin', 'armor' => 'plate', 'min' => 40),
			array('class' => 'priest',  'armor' => 'cloth'),
			array('class' => 'rogue',   'armor' => 'leather'),
			array('class' => 'shaman',  'armor' => 'leather'),
			array('class' => 'shaman',  'armor' => 'chain', 'min' => 40),
			array('class' => 'warlock', 'armor' => 'cloth'),
			array('class' => 'warrior', 'armor' => 'chain'),
			array('class' => 'warrior', 'armor' => 'plate', 'min' => 40),
		),
		
		// Factions
		'factions'     => array(
			'alliance' => array('id' => 1, 'name' => 'Alliance', 'races' => array('human','draenei','dwarf','gnome','nightelf')),
			'horde'    => array('id' => 2, 'name' => 'Horde', 'races' => array('bloodelf','orc','tauren','troll','undead')),
		),

		// Races
		'races'        => array(
			'bloodelf' => array(
				'id'       => 10, 
				'name'     => 'Blood Elf', 
				'faction'  => 'horde',
				'classes'  => array('priest','rogue','mage','hunter','warlock','paladin')
			),
			'human'    => array(
				'id'       => 2, 
				'name'     => 'Human', 
				'faction'  => 'alliance',
				'classes'  => array('priest','rogue','warrior','mage','warlock','paladin')
			),
			'draenei'  => array(
				'id'       => 9, 
				'name'     => 'Draenei', 
				'faction'  => 'alliance',
				'classes'  => array('priest','warrior','mage','hunter','shaman','paladin')
			),
			'dwarf'    => array(
				'id'       => 3, 
				'name'     => 'Dwarf', 
				'faction'  => 'alliance',
				'classes'  => array('priest','rogue','warrior','hunter')
			),
			'gnome'    => array(
				'id'       => 1, 
				'name'     => 'Gnome', 
				'faction'  => 'alliance',
				'classes'  => array('rogue','warrior','mage','warlock')
			),
			'nightelf' => array(
				'id'       => 4, 
				'name'     => 'Night Elf', 
				'faction'  => 'alliance',
				'classes'  => array('priest','rogue','warrior','druid','hunter')
			),
			'orc'      => array(
				'id'       => 7, 
				'name'     => 'Orc', 
				'faction'  => 'horde',
				'classes'  => array('rogue','warrior','hunter','warlock','shaman')
			),
			'tauren'   => array(
				'id'       => 8, 
				'name'     => 'Tauren', 
				'faction'  => 'horde',
				'classes'  => array('warrior','druid','hunter','shaman')
			),
			'troll'    => array(
				'id'       => 5, 
				'name'     => 'Troll', 
				'faction'  => 'horde',
				'classes'  => array('priest','rogue','warrior','mage','hunter','shaman')
			),
			'undead'   => array(
				'id'       => 6, 
				'name'     => 'Undead', 
				'faction'  => 'horde',
				'classes'  => array('priest','rogue','warrior','mage','warlock')
			),
		),
	);
}
?>