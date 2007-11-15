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

if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

include($eqdkp_root_path . 'games/game.php');

class game_wow extends game
{
	function game_wow()
	{
		$this->name      = 'World of Warcraft';
		$this->version   = '1.0';
		$this->max_level = 70;
		
		global $listgames;
		if( isset($listgames) && $listgames == true )
		{
			return;
		}

		// Armor types
		$this->armor_types = array(
			'cloth'    => array('id' => 1, 'name' => 'Cloth'),
			'leather'  => array('id' => 2, 'name' => 'Leather'),
			'chain'    => array('id' => 3, 'name' => 'Chain'),
			'plate'    => array('id' => 4, 'name' => 'Plate'),
		);
		
		// Classes
		$this->classes = array(
#           'deathknight'  => array('id' => 10, 'name' => 'Death Knight', 'color' => ''),
			'druid'    => array('id' => 1, 'name' => 'Druid', 'color' => ''),
			'hunter'   => array('id' => 2, 'name' => 'Hunter', 'color' => ''),
			'mage'     => array('id' => 3, 'name' => 'Mage', 'color' => ''),
			'paladin'  => array('id' => 4, 'name' => 'Paladin', 'color' => ''),
			'priest'   => array('id' => 5, 'name' => 'Priest', 'color' => 'FFFFFF'),
			'rogue'    => array('id' => 6, 'name' => 'Rogue', 'color' => ''),
			'shaman'   => array('id' => 7, 'name' => 'Shaman', 'color' => ''),
			'warlock'  => array('id' => 8, 'name' => 'Warlock', 'color' => ''),
			'warrior'  => array('id' => 9, 'name' => 'Warrior', 'color' => ''),
		);
		
		// Class-Armor mappings
		$this->class_armor = array(
			array('class' => 'druid', 'armor' => 'leather'),
			array('class' => 'hunter', 'armor' => 'leather'),
			array('class' => 'hunter', 'armor' => 'chain', 'min' => 40),
			array('class' => 'mage', 'armor' => 'cloth'),
			array('class' => 'paladin', 'armor' => 'chain'),
			array('class' => 'paladin', 'armor' => 'plate', 'min' => 40),
			array('class' => 'priest', 'armor' => 'cloth'),
			array('class' => 'rogue', 'armor' => 'leather'),
			array('class' => 'shaman', 'armor' => 'leather'),
			array('class' => 'shaman', 'armor' => 'chain', 'min' => 40),
			array('class' => 'warlock', 'armor' => 'cloth'),
			array('class' => 'warrior', 'armor' => 'chain'),
			array('class' => 'warrior', 'armor' => 'plate', 'min' => 40),
		);
		
		// Factions
		$this->factions = array(
			'alliance' => array('id' => 1, 'races' => array('human','draenei','dwarf','gnome','nightelf')),
			'horde'    => array('id' => 2, 'races' => array('bloodelf','orc','tauren','troll','undead')),
		);

		// Races
		$this->races = array(
			'bloodelf' => array(
				'id'       => 10, 
				'name'     => 'Blood Elf', 
				'faction'  => 'horde',
				'classes'  => array()
			),
			'human'    => array(
				'id'       => 2, 
				'name'     => 'Human', 
				'faction'  => 'alliance',
				'classes'  => array()
			),
			'draenei'  => array(
				'id'       => 9, 
				'name'     => 'Draenei', 
				'faction'  => 'alliance',
				'classes'  => array()
			),
			'dwarf'    => array(
				'id'       => 3, 
				'name'     => 'Dwarf', 
				'faction'  => 'alliance',
				'classes'  => array()
			),
			'gnome'    => array(
				'id'       => 1, 
				'name'     => 'Gnome', 
				'faction'  => 'alliance',
				'classes'  => array()
			),
			'nightelf' => array(
				'id'       => 4, 
				'name'     => 'Night Elf', 
				'faction'  => 'alliance',
				'classes'  => array()
			),
			'orc'      => array(
				'id'       => 7, 
				'name'     => 'Orc', 
				'faction'  => 'horde',
				'classes'  => array()
			),
			'tauren'   => array(
				'id'       => 8, 
				'name'     => 'Tauren', 
				'faction'  => 'horde',
				'classes'  => array()
			),
			'troll'    => array(
				'id'       => 5, 
				'name'     => 'Troll', 
				'faction'  => 'horde',
				'classes'  => array()
			),
			'undead'   => array(
				'id'       => 6, 
				'name'     => 'Undead', 
				'faction'  => 'horde',
				'classes'  => array()
			),
		);

	}
}
?>