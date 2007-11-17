<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        game_manager.php
 * Began:       Sat Oct 06 2007
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

// TODO: Log files store the race and class *IDs*, not the strings
// This class needs to provide methods to find the names by ID.

class Game_Manager
{
	var $games        = array();
	
	var $current_game      = '';
	var $current_game_data = array();
	
    var $armor_types  = array();
    var $classes      = array();
    var $races        = array();
    
    /**
     * Returns a Game_Manager class instance for a specific game
     *
     * @param     string     $game             The game to create the game manager for.
     * @return    Game_Manager
     * @static
     */
    function factory($game)
    {
        $game = str_replace(' ', '', $game);
        
        $file = basedir(__FILE__) . 'gm_' . strtolower($game);
        $class = 'GM_' . ucfirst(strtolower($game));
        
        $retval = null;
        
        if ( file_exists($file) )
        {
            include_once($fie);
            $retval = new $class;
        }
        else
        {
            trigger_error("Game_Manager class file <b>{$file}</b> doesn't exist.", E_USER_WARNING);
        }
        
        return $retval;
    }
	
	/**
	 * List all valid games available for use by EQdkp.
	 * @access public
	 */
	function list_games()
	{
		global $eqdkp_root_path;
	
		$sort = array();

		$path   = $eqdkp_root_path . 'games/';
		$handle = @opendir($path);

		if (!$handle)
		{
			trigger_error('Unable to access the games directory', __LINE__, __FILE__);
		}

		// Look for game packages
		while (false !== ($entry = readdir($handle)))
		{
			$gameinfo = $this->_get_game_info($entry, true);
			// Retrieve the game information
			if (!count($gameinfo))
			{
				continue;
			}
			
			$this->games[$entry] = $gameinfo;
		}
		closedir($handle);
		unset($game_info, $classname);

		$sort = $this->games;
		ksort($sort);
		
		return $sort;
	}

	/**
	 * Sets the game manager's current game (and game data) to the specified game
	 */
	function set_current_game()
	{
	}

	/**
	 * Retrieve game information from a flat game package file
	 * @param     string     $game_id          The package name for the game. This must correspond with a folder name in the games folder.
	 * @param     bool       $info_only        Whether to retrieve only the general game information (true) or all game-specific data (false).
	 * @return    array                        The information for the requested game. If $info_only is false, all extra data is added to this array under the key 'data'.
	 *
	 * @access    private
	 */
	function _get_game_info($game_id, $info_only = false)
	{
		global $eqdkp_root_path;
		
		$path      = $eqdkp_root_path . 'games/' . $game_id;
		$classname = 'game_' . $game_id;
		$data      = array();

		// Retrieve the game information
		if ($info_only === true)
		{
			$get_gameinfo = true;
		}

		// If the specified game ID doesn't have a folder, we aren't interested.
		if (is_dir($path))
		{
			// Ignore any directory which isn't a valid game package, or don't have a valid game
			if (is_file($path . "/$classname.php"))
			{
				include($path . "/$classname.php");
				
				$data = (isset($game_info)) ? $game_info : $data;
				$data['classname'] = $classname;
				
				if (!$info_only && isset($game_data))
				{
					$data['data'] = $game_data;
					unset($game_data);
				}
				unset($game_info);
			}
		}
		
		return $data;
	}

	/**
	 * Retrieve the installed game's armor type information from the database
	 * @return    array
	 */
    function sql_armor_types()
    {
        global $db;
        
        if ( count($this->armor_types) == 0 )
        {
            $sql = "SELECT class_armor_type FROM __classes
                    GROUP BY class_armor_type";
            $result = $db->query($sql);
            while ( $row = $db->fetch_record($result) )
            {
                $this->armor_types[] = stripslashes($row['class_armor_type']);
            }
            $db->free_result($result);
        }
        
        return $this->armor_types;
    }
    
	/**
	 * Retrieve the installed game's armor type information from the database
	 * @return    array
	 */
    function sql_classes()
    {
        global $db;
        
        if ( count($this->classes) == 0 )
        {
            $sql = "SELECT class_name, class_id, class_min_level, class_max_level 
                    FROM __classes
                    ORDER BY class_name, class_min_level";
            $result = $db->query($sql);
            while ( $row = $db->fetch_record($result) )
            {
                $this->classes[] = array(
                    'name'      => stripslashes($row['class_name']),
                    'id'        => intval($row['class_id']),
                    'min_level' => intval($row['class_min_level']),
                    'max_level' => intval($row['class_max_level'])
                );
            }
            $db->free_result($result);
        }

        return $this->classes;
    }
    
	/**
	 * Retrieve the installed game's armor type information from the database
	 * @return    array
	 */
    function sql_races()
    {
        global $db;
        
        if ( count($this->races) == 0 )
        {
            $sql = "SELECT race_id, race_name, race_faction_id, race_hide
                    FROM __races 
                    GROUP BY race_name";
            $result = $db->query($sql);
            while ( $row = $db->fetch_record($result) )
            {
                $this->races[] = array(
                    'name'       => stripslashes($row['race_name']),
                    'id'         => intval($row['race_id']),
                    'faction_id' => intval($row['race_faction_id']),
                    'hide'       => intval($row['race_hide'])
                );
            }
            $db->free_result($result);
        }
        
        return $this->races;
    }
    
	/**
	 * Retrieve the installed game's armor type information from the database
	 * @param     string     $class_id         The class name to format
	 * @return    string                       The formatted class name for $class_id from $this->classes
	 */
    function format_class_name($class_id)
    {
        if ( count($this->classes) == 0 )
        {
            $this->sql_classes();
        }
        
        foreach ( $this->classes as $class )
        {
            if ( $class['id'] == $class_id )
            {
                return $this->_format_class_with_level($class['name'], $class['min_level'], $class['max_level']);
            }
        }
    }
    
    /**
     * This function is to provide compatibility with the retarded 1.3 method,
     * expect to deprecate this when we Do It Better™
     */
    // TODO: Localize
    function _format_class_with_level($class_name, $min_level = 0, $max_level = 0)
    {
        if ( empty($class_name) )
        {
            return '(None)';
        }
        
        if ( intval($min_level) == 0 )
        {
            return sanitize($class_name) . " (Level {$min_level}-{$max_level})";
        }
        else
        {
            return sanitize($class_name) . " (Level {$min_level}+)";
        }
    }
}
?>