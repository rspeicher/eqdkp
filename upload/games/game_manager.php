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
 * @copyright   2002-2008 The EQdkp Project Team
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
	 * Returns a language-specific string from either the user's language (if the user is set) 
	 * or the language array (if the user isn't set and $lang is).
	 * Returns false if neither exist, or the specified key isn't set.
	 */
	function get_lang_string($key)
	{
		global $user, $lang;
		
		$lang_string = '';

		// Check the user's language values
		if (isset($user) && isset($user->lang) && is_array($user->lang))
		{
			$lang_string = isset($user->lang[$key]) ? $user->lang[$key] : $lang_string;
			
		}
		
		// Check any global language values
		if (!$lang_string && isset($lang) && is_array($lang))
		{
			$lang_string = isset($lang[$key]) ? $lang[$key] : $lang_string;
		}
		
		return ($lang_string) ? $lang_string : false;
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
        global $db, $lang;
        
        if ( count($this->armor_types) == 0 )
        {
            $sql = "SELECT armor_type_id, armor_type_name, armor_type_key 
                    FROM __armor_types
                    ORDER BY armor_type_name";
            $result = $db->sql_query($sql);
            while ($row = $db->sql_fetchrow($result))
            {
				$base_key = stripslashes($row['armor_type_key']);
				$lang_key = $this->get_lang_string(strtoupper('ARMOR_' . $base_key));
			
                $this->armor_types[] = array(
                    'name'      => ($lang_key !== false) ? $lang_key : stripslashes($row['armor_type_name']),
                    'id'        => intval($row['armor_type_id']),
                    'key'       => $base_key,
                );
            }
            $db->sql_freeresult($result);
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
        global $db, $lang;

        if ( count($this->classes) == 0 )
        {
            $sql = "SELECT class_name, class_id, class_key 
                    FROM __classes
                    ORDER BY class_name";
            $result = $db->sql_query($sql);
            while ( $row = $db->sql_fetchrow($result) )
            {
				$base_key = stripslashes($row['class_key']);
				$lang_key = $this->get_lang_string(strtoupper('CLASS_' . $base_key));
			
                $this->classes[] = array(
                    'name'      => ($lang_key !== false) ? $lang_key : stripslashes($row['class_name']),
                    'id'        => intval($row['class_id']),
                    'key'       => $base_key,
                );
            }
            $db->sql_freeresult($result);
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
        global $db, $lang;
        
        if ( count($this->races) == 0 )
        {
            $sql = "SELECT race_id, race_name, race_key, race_faction_id, race_hide
                    FROM __races 
                    GROUP BY race_name";
            $result = $db->sql_query($sql);
            while ( $row = $db->sql_fetchrow($result) )
            {
 				$base_key = stripslashes($row['race_key']);
				$lang_key = $this->get_lang_string(strtoupper('RACE_' . $base_key));
			
               $this->races[] = array(
                    'name'       => ($lang_key !== false) ? $lang_key : stripslashes($row['race_name']),
                    'id'         => intval($row['race_id']),
                    'key'        => $base_key,
                    'faction_id' => intval($row['race_faction_id']),
                    'hide'       => intval($row['race_hide'])
                );
            }
            $db->sql_freeresult($result);
        }
        
        return $this->races;
    }
        
    /**
     * Retrieve the installed game's faction information from the database
     * 
     * @return    array
     */
    function sql_factions()
    {
        global $db;
        
        if (count($this->factions) == 0)
        {
            $sql = "SELECT faction_id, faction_name, faction_key
                    FROM __factions
                    ORDER BY faction_name";
            $result = $db->sql_query($sql);
            while ($row = $db->sql_fetchrow($result))
            {
 				$base_key = stripslashes($row['faction_key']);
				$lang_key = $this->get_lang_string(strtoupper('FACTION_' . $base_key));
			
                $this->factions[] = array(
                    'name'        => ($lang_key !== false) ? $lang_key : stripslashes($row['faction_name']),
                    'id'          => intval($row['faction_id']),
                    'key'         => $base_key,
                );
            }
            $db->sql_freeresult($result);
        }
        
        return $this->factions;
    }    
    
    /** 
     * Parse a game log entry
     * 
     * @param     string     $log_entry        The string log entry to parse
     * @param     string     $parse_string     An optional format for the parsing string
     * @return    array                        The parsed data for the log entry.
     * 
     * @note This function requires that the current game be set.
     * 
     * The data is formatted as follows:
     * array(
     *    'name'    => (string)       
     *    'level'   => (int)          
     *    'race'    => (multi-string) (optional) 
     *    'class'   => (multi-string) 
     *    'guild'   => (multi-string) (optional) 
     *    'zone'    => (optional)
     * )
     *
     * If the data is not present in the log entry, the index's value will be set to false.
     */
    function parse_log_entry($log_entry, $parse_string = false)
    {
        // TODO: Set current game? If we do, then it kind of breaks the standard method of instantiating and using the class.
         
        $log_data = array(
            'name'   => false,
            'level'  => false,
            'race'   => false,
            'class'  => false,
            'guild'  => false,
            'zone'   => false,
        );
        
        // Undo any HTML/SQL formatting to the log entry
        $raw_log_entry = $log_entry;
        $log_entry = unsanitize($log_entry);
        
        // Get the parse string
        if ($parse_string === false)
        {
            // Check the current game is set, and there is data for it
            if (!empty($this->current_game) && isset($this->games[$this->current_game]))
            {
                // Check if there are parsing string(s) set for the game
                if (($this->games[$this->current_game]['available']['parsing'] == true) && (count($this->games[$this->current_game]['data']['parsing']) > 0))
                {
                    $parse_string = $this->games[$this->current_game]['data']['parsing'][0];
                }
            }
        }
        
        // Begin the parsing!
        $this->_parse_log_entry($log_entry, $parse_string, $log_data);
        
        return $log_data;
    }
    
    /**
     * Parse a game log entry helper function
     * 
     * Takes a log entry string, a (EQdkp-formatted) parse string, and an array for the results.
     */
    function _parse_log_entry($log_entry, $parse_string, &$log_data)
    {
        $matched_length = 0;

        // If we have a valid parse string, let us begin
        if ($parse_string !== false && strlen($parse_string) > 0)
        {
            // Remove any extraneous question marks so that they don't destroy the regex parsing
            $char_count = count_chars($parse_string, 0);
            // The ASCII character number for question marks is 63
            if (isset($char_count[63]))
            {
                if (($char_count[63] % 2) !== 0)
                {
                    $last_mark   = strrpos($parse_string, '?'); // This is guaranteed to be a number now
                    $first_half  = substr($parse_string, 0, $last_mark);
                    $second_half = substr($parse_string, $last_mark+1);

                    $parse_string = (($second_half !== false)) ? $first_half . $second_half : $first_half;
                }
            }
        
            /** 
             * EQdkp parse string format (in ... format):
             *
             * <pre-text><magic-tag><post-text>
             * 
             * <pre-text>          : <utf8-text>
             * <magic-tag>         : __<text>__
             * <post-tag>          : <utf8-text> | <pre-text><optional-group>
             * <optional-group>    : ?<pre-text><magic-tag><utf8-text>?
             * <text>              : any number of english alphabetic characters
             * <utf8-text>         : any number of utf-8 characters EXCLUDING the question-mark (?) character
             *
             * NOTES:
             *  - Magic tags are two and only two underscores either side of a name for the tag.
             *  - 
             */
             
            /**
             * Match string segments in the EQdkp parse string form.
             * This match will return data in the following format:
             * array(
             *   [0] => array( all full string matches for entire regex )
             *   [1] => array( name of magic tag that was captured in this string )
             * );
             */
            preg_match_all('#[^_]*?__(\w+?)__(?:[^_?])*(?:(?:\?.*?\?)+)?(?:[^_?])*#', $parse_string, $captures);

            $capture_strings = $captures[0];
            $capture_names   = $captures[1];
            
            $results = array();

            // For each component of the parse string, we try to get its value separately.
            $partial_parse_string = array();
            $datatype = '';

            for($i = 0; $i < count($capture_strings); $i++)
            {
                $partial_log_entry       = substr($log_entry, $matched_length);
                $partial_parse_string    = $capture_strings[$i];
                $datatype                = $capture_names[$i];
                
                $matched_length += $this->_parse_log_entry_component($partial_log_entry, $partial_parse_string, $datatype, $log_data);
            }
        }
        
        return $matched_length;
    }
    
    
    /**
     * Parse log entry helper function
     * 
     * Does the work for a particular component of the log entry, such as parsing the name, the race, class, etc.
     */
    function _parse_log_entry_component($log_entry, $parse_string, $datatype, &$log_data)
    {
        $results = array();
        $matched_length = 0;
        
        // NOTE: Using regexes here may cause the whole process to slow down considerably...
        // NOTE: Is there a chance the parse string would have had slashes added to it already?
        // First thing's first - let's escape anything that isn't a special tag.
        $regex_string = mysql_escape_string($parse_string);
        $regex_string = preg_replace('#([-:;<>\[\]\(\)\{\}\^\$\'\"\#])#', '\\\\\1', $regex_string);

        // Now replace optional components from the parse string with optional regular expression groupings
        $regex_string = preg_replace('#\?(.*?)\?#', '(?:\1)?', $regex_string);

        // We have to match differently depending on the type of data
        switch ($datatype)
        {
            // These values are highly variable, so we're going to match everything.
            case 'name':
            case 'guild':
            case 'zone':
                $regex_string = preg_replace('#__.*?__#', '(.*)', $regex_string);
                preg_match('#' . $regex_string . '#', $log_entry, $results);
            break;
            
            // We want to match a simple number
            case 'level':
                $regex_string = str_replace('__level__', '(\d+)', $regex_string);
                $regex_string = preg_replace('#__.*?__#', '(.*)', $regex_string);
                preg_match('#' . $regex_string . '#', $log_entry, $results);
            break;

            // These data types have a finite set of possible values, and can include any unicode character depending on language.
            // For this reason, we will offload the matching process to a special method.
            case 'race':
            case 'class':
                $value = $this->_parse_special_data($log_entry, $datatype);
                // If the match was successful...
                if (!empty($value))
                {
                    $results = array(
                        0 => preg_replace('#^__.*?__#', $value, $parse_string),
                        1 => $value,
                    );
                }
            break;    
            
            // If the data type is invalid, we'll return an empty result
            default:
                $results = array();
            break;
        }

        if (count($results) > 1)
        {
            // If the match was successful, merge in the data from the match into the parsed log data.
            $log_data = array_merge($log_data, array($datatype => $results[1]));
        }
        else
        {
            // Otherwise, we'll create a blank result to use when processing the optional groups.
            $results = array(0 => '', 1 => '');
        }

        // Check for any optional components of the parse string. 
        // If there are any, make a recursive call to parse that component the log.
        $first_mark = strpos($parse_string, '?');
        if ($first_mark == false)
        {
            // If there are no optional components to the string, we can use the length of the matched string
            $matched_length = strlen($results[0]);
        }
        else
        {
            // Otherwise, only know that we've matched everything up until the first optional grouping.
            $matched_length = strpos($results[0], '?');
            $second_mark = false;
            
            // NOTE: The while loop is to capture multiple optional groups
            while ($first_mark !== false)
            {
                $second_mark = strpos($parse_string, '?', $first_mark+1);
                $substring_length = $second_mark - $first_mark;
    
                // Remove the matched content from the parse string and log entry.
                $optional_log_entry = substr($log_entry, $matched_length);
                $optional_parse_string = substr($parse_string, $first_mark+1, $substring_length-1);
    
                $optional_matched_length = $this->_parse_log_entry($optional_log_entry, $optional_parse_string, $log_data);
                
                $matched_length += $optional_matched_length;
                
                // Find the next optional group, if it exists.
                $first_mark = strpos($parse_string, '?', $second_mark+1);
            }
        }
        
        return $matched_length;
    }
    
    /**
     * Parse special data function
     *
     * This function takes a string value and a datatype or 'key' for the string, and attempts to find
     * a valid value for the key. The value is searched for in a finite collection of possible values.
     */
    function _parse_special_data($log_entry, $datatype)
    {
        global $user, $lang;
    
        $dataset = array(); // Holds the finite set of possible values for the data type
        $result = '';       // Holds the result of a successful match
        
        switch ($datatype)
        {
            case 'class':
                $dataset = $this->sql_classes();
            break;
            
            case 'race':
                $dataset = $this->sql_races();
            break;
        }

        // Now, for each valid data entry, we're going to see if we can make a match.    
        foreach ($dataset as $data_entry)
        {
            // Retrieve the correct string representation of the data, using the (english) name in the database as a fallback
            $lang_key = strtoupper($datatype . '_' . str_replace(' ', '_', $data_entry['key']));
            $match_value = isset($user->lang[$lang_key]) ? $user->lang[$lang_key] : $data_entry['name'];
            
            // TODO: If the parse string has mixed case and it's in an exotic language, 
            // this will fall down on case-(in)sensitive matches because we're not using UTF-8
            // NOTE: We can't just use an in_array call because of the above consideration, combined with the fact that 
            // the string most likely has trailing crap on it.
            // NOTE: Should this return the string for the race/class, or its actual ID within the system?
            
            // Try to find the required data at the beginning of the partial log entry
            // NOTE: stripos is a PHP5 function, so we have to make sure it's present before we use it.
            // If it isn't there, then there's some work for us to do to get this working right.
            if (function_exists('stripos'))
            {
                // TODO: utf-8 conversions of names to lowercase for comparison instead of using stripos
                if (stripos($log_entry, $match_value) === 0)
                {
                    $result = $match_value;
                    break;
                }
            }
            else
            {
                // TODO: utf-8 conversions of names to lowercase for comparison
                if (strpos($log_entry, $match_value) === 0)
                {
                    $result = $match_value;
                    break;
                }
            }
        }
        
        return $result;
    }
     
}
?>