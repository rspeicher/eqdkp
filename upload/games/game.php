<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        game.php
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

class Game
{
    /**#@+
     * @access private
     */
    var $id;               // The class name for the game instance which is extending the Game class
    var $name;             // The game name in full
    var $version;          // The EQdkp version for this revision of the game information
    
    var $max_level = 0;
    
    var $armor_types   = array(); 
    var $classes       = array(); 
    var $class_armor   = array(); // Class-Armor mappings
    var $class_aliases = array(); // Optional class aliases for the standard classes
    var $factions      = array();
    var $races         = array();
    /**#@-*/
    
    /**
     * @access public
     */
     
    // No constructor
    
    function get_name()
    {
        return $this->game_name;
    }
    
    function get_armor_types()
    {
        return $this->armor_types;
    }

    function get_classes()
    {
        return $this->classes;
    }
    
    function get_factions()
    {
        return $this->factions;
    }

    function get_races()
    {
        return $this->races;
    }
}