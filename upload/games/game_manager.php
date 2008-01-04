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
		
		// If we have a valid parse string, let us begin
		if ($parse_string !== false)
		{
#			echo "Parse String: <pre>" . $parse_string . "</pre>\n\n<br />";
		
			/** 
			 * Match string segments in the following form: 
			 * pre-text__magic_tag__post-text
			 * 
			 * post-text may include 'optional' segments
			 * pre-text__magic_tag__post-?optional ?text
			 *
			 * NOTES:
			 *  - Magic tags are two and only two underscores either side of the tag name.
			 *  - There may be ONLY ONE optional component between two magic tags.
			 */
			 
			/**
			 * This match will return data in the following format:
			 * array(
			 *   [0] => array( all full string matches for entire regex )
			 *   [1] => array( name of magic tag that was captured in this string )
			 *   [2] => array( optional component included in the string )
			 * );
			 */
			preg_match_all('#[^_]*?__(\w+?)__(?:[^_?])*(?:(\?.*?\?))?(?:[^_?])*#', $parse_string, $parse_string_parts);

#			echo "<b>Parse string Components:</b> <pre>";
#			var_dump($parse_string_parts);
#			echo "</pre><br />";
			
			
			// TODO: Revise names of values in this method, they suck
			$ps_strings = $parse_string_parts[0];
			$ps_data = $parse_string_parts[1];
			
			$ps   = array();
			$data = '';

			$matched_pos = 0;
			$results = array();
			
			// For each component of the parse string, we try to get its value separately
			for($i = 0; $i < count($ps_strings); $i++)
			{
				$ps    = $ps_strings[$i];
				$data  = $ps_data[$i];
				$entry = substr($log_entry, $matched_pos);
				
				$results = $this->_parse_log_entry($entry, $ps, $data);

				// If the match was successful
				if (count($results) > 1)
				{
					// Merge in the data from a match
					$log_data = array_merge($log_data, array($data => $results[1]));
					
					// we'll create a substring starting from the end of the match.
					$matched_pos += strlen($results[0]);
				}
			}
		}
		
		return $log_data;
	}
	 
    function _parse_log_entry($log_entry, $parse_string, $datatype)
	{
		$results = array();
		
		// NOTE: Using regexes here may cause the whole process to slow down considerably...
		// NOTE: Is there a chance the parse string would have had slashes added to it already?
		// First thing's first - let's escape anything that isn't a special tag.
		$regex_string = mysql_escape_string($parse_string);
		$regex_string = preg_replace('#([-:;<>\[\]\(\)\{\}\^\$\'\"\#])#', '\\\\\1', $regex_string);

		// Now replace optional components from the parse string with optional regular expression groupings
		$regex_string = preg_replace('#\?(.*?)\?#', '(?:\1)?', $regex_string);

#		echo "<ul>";
#		echo "<li><b>Datatype:</b> " . $datatype . "</li>\n";
#		echo "<li><b>Log Entry:</b> " . $log_entry . "</li>\n";
#		echo "<li><b>Parse:</b> " . $parse_string . "</li>\n";
#		echo "</ul>";

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
						0 => preg_replace('#__.*?__#', $value, $parse_string),
						1 => $value,
					);
				}
			break;	
			
			// If the data type is invalid, we'll return an empty array
			default:
				$results = array();
			break;
		}

#		echo "Result: ";
#		echo "<pre>";		
#		print_r($results);
#		echo "</pre>";
#		echo "<hr />";
		
		return $results;
	}
	 
	function _parse_special_data($log_entry, $datatype)
	{
		global $user, $lang;
	
#		echo "Entering special method..." . "\n<br />\n<pre>";
	
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

#		var_dump($dataset);
	
		// Now, for each valid data entry, we're going to see if we can make a match.	
		foreach ($dataset as $data_entry)
		{
			// Retrieve the correct string representation of the data, using the (english) name in the database as a fallback
			// FIXME: Language implementation and use of 'name' instead of their key
			$lang_key = strtoupper($datatype . '_' . str_replace(' ', '_', $data_entry['name']));
			$match_value = isset($user->lang[$lang_key]) ? $user->lang[$lang_key] : $data_entry['name'];
			
#			echo "Value: ";
#			printf('%-20s', $match_value);
#			echo " [lang-key = '" . (isset($user->lang[$lang_key]) ? $lang_key : 'false') . "']";
			
			// FIXME: If the parse string has mixed case and it's in an exotic language, 
			// this will fall down on case-(in)sensitive matches because we're not using UTF-8
			// NOTE: We can't just use an in_array call because of the above consideration, combined with the fact that 
			// the string most likely has trailing crap on it.
			// FIXME: Should this return the string for the race/class, or its actual ID within the system?
			
			// Try to find the required data at the beginning of the partial log entry
			// NOTE: stripos is a PHP5 function, so we have to make sure it's present before we use it.
			// If it isn't there, then there's some work for us to do to get this working right.
			if (function_exists('stripos'))
			{
#				echo ' ...' . ((stripos($log_entry, $match_value) === false) ? 'no match' : 'match @ ' . stripos($log_entry, $match_value)) . "\n";

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
		
#		echo "</pre>\nreturning... '" . $result . "'\n<br /><br />\n";
		
		return $result;
	}
	 
}
?>