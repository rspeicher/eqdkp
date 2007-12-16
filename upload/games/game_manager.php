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
define('IN_GAME_MANAGER', true);

// TODO: Log files store the race and class *IDs*, not the strings
// This class needs to provide methods to find the names by ID.

class Game_Manager
{
    // Uninstalled game information
    var $games        = array();
    var $current_game = '';
    
    // Installed game info cache
    var $armor_types  = array();
    var $classes      = array();
    var $races        = array();
    
    
    function Game_Manager($game_id = false)
    {
        $this->games        = array();

        $this->armor_types  = array();
        $this->classes      = array();
        $this->races        = array();

        $this->current_game = '';

        if ($game_id !== false)
        {
            if (false === set_current_game($game_id))
            {
                $this->current_game = '';
            }
        }
    }
    
    /**
     * Add the language file for the current (or specified) game
     */
    function add_lang($game_id = false)
    {
        global $eqdkp, $eqdkp_root_path, $user, $lang;
        
        if (empty($this->current_game) && $game_id === false)
        {
            return;
        }
        
        if ($game_id === false)
        {
            $game_id = $this->current_game;
        }
        
        $path   = $eqdkp_root_path . 'games/';
        $handle = @opendir($path);

        if (!$handle)
        {
            trigger_error("Unable to access the <b>games</b> directory", E_USER_WARNING);
        }

        $lang_file = $path . "{$game_id}/{$user->data['user_lang']}/lang_game.php";

        if (!@include_once($lang_file))
        {
            return false;
        }
        
		// Append the language strings to the user's
		$user->lang = array_merge($user->lang, $lang);
		
        return true;
    }
    
    /**
     * List all valid games available for use by EQdkp.
     * 
     * @param     bool      $ids_only         If true, will only return an indexed array, where the values are the ids of all valid games.
     *
     * @return    array
     * @access    public
     */
    function list_games($ids_only = false)
    {
        global $eqdkp_root_path;
    
        $sort = array();

        $path   = $eqdkp_root_path . 'games/';
        $handle = @opendir($path);

        if (!$handle)
        {
            trigger_error("Unable to access the <b>games</b> directory", E_USER_WARNING);
        }

        $ignore = array('.','..','.svn');

        // Look for game packages
        while (false !== ($entry = readdir($handle)))
        {
            if (in_array($entry, $ignore))
            {
                continue;
            }
            
            // Retrieve the game information only
            $gameinfo = $this->_retrieve_game_data($entry, true);
            // If the file wasn't a valid game package, or no valid data was found for the game
            if ($gameinfo === false || !count($gameinfo))
            {
                continue;
            }
            
            // NOTE: Consecutive calls will always override whatever is currently held in the games array
            $this->games[$entry] = $gameinfo;
        }
        closedir($handle);
        unset($game_info, $classname);

        $sort = $this->games;
        ksort($sort);
        
        if ($ids_only)
        {
            $sort = array_keys($sort);
        }
        
        return $sort;
    }

    /**
     * Sets the game manager's current game (and game data) to the specified game
     * 
     * @param     string     $game_id          The package name for the game. This must correspond with a folder name in the games folder.
     * @return    mixed                        Returns false if the game doesn't exist. Otherwise, returns the current game name 
     *                                         (if no valid game data was found, $current_game remains the same as it was before).
     */
    function set_current_game($game_id)
    {
        // Retrieve the game data for the specified game
        $gamedata = $this->retrieve_game_data($game_id);
        
        if ($gamedata === false)
        {
            return false;
        }
        else
        {
            if (count($gamedata))
            {
                $this->current_game = $game_id;
            }
            return $this->current_game;
        }
    }

    /**
     * Returns the game data for the specified game. If no game id is specified, it will attempt to retrieve the current game's data.
     */
    function get_game_data($game_id = false)
    {
        if (!is_string($game_id) || !strlen($game_id))
        {
            $game_id = (!empty($this->current_game)) ? $this->current_game : false;
        }
        
        if ($game_id == false)
        {
            return false;
        }
        
        if (!isset($this->games[$game_id]) || !isset($this->games[$game_id]['data']))
        {
            $this->retrieve_game_data($game_id);
        }
        
        return $this->games[$game_id];
    }

    /**
     * Retrieves and returns the game data for the specified game. The game data will be held in $games
     * 
     * @param     string     $game_id          The package name for the game. This must correspond with a folder name in the games folder.
     * @return    mixed                        False if the game id is invalid. Empty array if no data was found. Filled array if data existed.
     */
    // TODO: Perhaps add code here to accept $game_id as the array values returned from list_games() / retrieve_game_data() ? Probably not.
    function retrieve_game_data($game_id)
    {
        // If we didn't get a valid game package name, there's no point in continuing.
        if (!is_string($game_id) || !strlen($game_id))
        {
            return false;
        }
        
        // Retrieve the game data for the specified game
        $gamedata = $this->_retrieve_game_data($game_id);
        
        if (!count($gamedata))
        {
            return array();
        }
        
        // Update the data for this game ID, regardless of the data stored in there before.
        $this->current_game = $game_id;
        $this->games[$game_id] = $gamedata;
        
        return $this->games[$game_id];
    }

    
    /**
     * Retrieve game information from a flat game package file
     * 
     * @param     string     $game_id          The package name for the game. This must correspond with a folder name in the games folder.
     * @param     bool       $info_only        Whether to retrieve only the general game information (true) or all game-specific data (false).
     * @return    mixed                        If successful, returns the information for the requested game. If $info_only is false, all extra 
     *                                         data is added to this array under the key 'data'. If the game package was invalid, returns false.
     *
     * @access    private
     */
    function _retrieve_game_data($game_id, $info_only = false)
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
            if (file_exists($path . "/{$classname}.php"))
            {
                include($path . "/{$classname}.php");
                
                if(!isset($game_info) || !count($game_info))
                {
                    return array();
                }
                // TODO: Check for game_info array validity (check for id, name)
                
                $data = $game_info;
                $data['classname'] = $classname;
                
                if (!$info_only && isset($game_data))
                {
                    $data['data'] = $game_data;
                    unset($game_data);
                }
                unset($game_info);

                return $data;
            }
        }
        return false;    
    }

    /**
     * Retrieve the installed game's armor type information from the database
     * 
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
     * 
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
     * 
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
     * 
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
     * 
     * @deprecated
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