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
    
    /**
     * Returns a Game_Manager class instance for a specific game
     *
     * @param     string     $game             The game to create the game manager for.
     * @return    Game_Manager
     * @static
     * @deprecated
     */
    function factory($game)
    {
        $game = str_replace(' ', '', $game);
        
        $file = basedir(__FILE__) . 'gm_' . strtolower($game);
        $class = 'GM_' . ucfirst(strtolower($game));
        
        $retval = null;
        
        if ( file_exists($file) )
        {
            include_once($file);
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
     * 
     * @return array
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
            trigger_error("Unable to access the <b>games</b> directory", E_USER_WARNING);
        }

        // Look for game packages
        while (false !== ($entry = readdir($handle)))
        {
            // Retrieve the game information only
            $gameinfo = $this->_get_game_data($entry, true);
            // If the file wasn't a valid game package, or no valid data was found for the game
            if ($gameinfo === false || !count($gameinfo))
            {
                continue;
            }
            
            // TODO: Check for a duplicate game info entry?
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
     * 
     * @param     string     $game_id          The package name for the game. This must correspond with a folder name in the games folder.
     * @return    mixed                        Returns false if the game doesn't exist. Otherwise, returns the current game name 
     *                                         (if no valid game data was found, $current_game remains the same as it was before).
     */
    function set_current_game($game_id)
    {
        /* At the moment I've commented this out because I don't like the idea of relying on the cached data. *shrug*
        if (isset($this->games[$game_id]))
        {
            $this_game = $this->games[$game_id];
            if (isset($this_game['name'] && isset($this_game['data']) && count($this_game['data']))
            {
                return $this_game['name'];
            }
        }
        */
        
        // Retrieve the game data for the specified game
        $gamedata = $this->get_game_data($game_id);
        
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
     * Retrieves and returns the game data for the specified game. The game data will be held in $games
     * 
     * @param     string     $game_id          The package name for the game. This must correspond with a folder name in the games folder.
     * @return    mixed                        False if the game id is invalid. Empty array if no data was found. Filled array if data existed.
     */
    function get_game_data($game_id)
    {
        // If we didn't get a valid game package name, there's no point in continuing.
        // TODO: Perhaps add code here to accept $game_id as the array values returned from list_games() / get_game_data() ? Probably not.
        if (!is_string($game_id) || !strlen($game_id))
        {
            return false;
        }
        
        // Retrieve the game data for the specified game
        $gamedata = $this->_get_game_data($game_id);
        
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
    function _get_game_data($game_id, $info_only = false)
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
            if (file_exists($path . "/$classname.php"))
            {
                include($path . "/$classname.php");
                
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
     * Processes all the necessary information to install the current game
     *
     * NOTE: In order to enforce nice order of operations with installation, usage of this method 
     *       is limited to the current game ONLY.
     *
     * @access   public
     */
    function install_game()
    {
		// TODO: Game file data validation
	
        // TODO: Mapping between old game data and new game data (WoW class IDs -> EQ class IDs etc.)
		// NOTE: Where should these mappings be entered and how?
		/**
		 * Mappings from old game data to new game data
		 * 
		 * This information is necessary in order to ensure referential integrity for foreign keys in the database
		 */
        $mappings = array(
            'factions'      => array(),
            'races'         => array(),
            'armor_types'   => array(),
            'classes'       => array(),
            'armor_classes' => array(),
        );
		
        $result = $this->_install_game();
    }
    
    /**
     * Builds and runs the SQL to install the current game
     * 
     * NOTE: In order to enforce nice order of operations with installation, usage of this method 
     *       is limited to the current game ONLY.
     *
     * @access   private
     */
    // TODO: Provide an array of mappings from the old game settings to the new ones (eg: WoW class ID -> EQ class ID)
	// TODO: Take into account the need to UPDATE instead of INSERT for any IDs that already exist in the database.
    function _install_game()
    {
        global $db;
        
        // If the current game hasn't been set, we don't want to do this.
        if( $this->current_game == false || !strlen($this->current_game))
        {
            //trigger_error('NO_CURRENT_GAME');
            return false;
        }
        
        // Retrieve the game data for the current game
        $game_name = $this->games[$this->current_game]['name'];
        $max_level = intval($this->games[$this->current_game]['max_level']);
        $data      = $this->games[$this->current_game]['data'];

        /** Build the SQL for the new game data
         *
         * NOTE: The order of operations here is fairly important.
         * FIXME: This method will definitely fall down on account of foreign key constraints for classes and races and such.
         *
         * TODO: Use $games[$game_id]['available'] information to only bother working with what we have.
         * TODO: Replace use of $info['name'] with the keys themselves. Then upon retrieval from the db, the 'name' can be replaced with the language string.
         *
         * FIXME: ID information. Right now, if ID isn't provided in the game info file OR the IDs aren't unique, this will all fail horribly. 
		 *        A new method is going to have to be added somewhere (perhaps in the install_game method, before this one is called) where the IDs 
		 *        are checked, and if they aren't provided or valid, simply rewrite all of them. Hell, we have to make mappings between IDs, so
		 *        it won't matter so much for gameA->gameB installs. However, it might matter for gameA->gameA (upgrading EQdkp or similar).
		 *
		 * FIXME: Foreign key constraints will ruin this at the moment. UPDATEs are required for cases where the ID already exists.
		 *        Efficiency in determining whether an UPDATE or INSERT is required can be achieved by retrieving COUNT(id) and MAX(id) from the table in question.
		 *
         */
        $game_sql = array(
            'factions'      => array(),
            'races'         => array(),
            'armor_types'   => array(),
            'classes'       => array(),
            'armor_classes' => array(),
        );

        // Generics for all games
        $game_sql['factions'][]      = $db->sql_build_query('INSERT',array('faction_id' => 0, 'faction_name' => 'Unknown'));
        $game_sql['races'][]         = $db->sql_build_query('INSERT',array('race_id' => 0, 'race_name' => 'Unknown', 'race_faction_id' => 0));
#        $game_sql['armor_types'][]   = $db->sql_build_query('INSERT',array('armor_type_id' => 0, 'armor_type_name' => 'None'));
#        $game_sql['classes'][]       = $db->sql_build_query('INSERT',array('class_id' => 0, 'class_name' => 'Unknown'));
        $game_sql['classes'][]       = $db->sql_build_query('INSERT',array('class_id' => 0, 'class_name' => 'Unknown', 'class_armor_type' => 'Unknown', 'class_min_level' => 0, 'class_max_level' => $max_level));
        // No generics for armor_classes
        
        // Factions
        foreach ($data['factions'] as $faction => $info)
        {
            $sql_data = array(
                'faction_id'      => intval($info['id']),
                'faction_name'    => $db->sql_escape($info['name']),
            );
            $game_sql['factions'][] = $db->sql_build_query('INSERT',$sql_data);
        }
        
        // Races
        $race_sql = array();
        foreach ($data['races'] as $race => $info)
        {
            $sql_data = array(
                'race_id'         => intval($info['id']),
                'race_name'       => $db->escape($info['name']),
                'race_faction_id' => (is_numeric($info['faction'])) ? intval($info['faction']) : intval($data['factions'][$info['faction']]['id']),
            );
            $game_sql['races'][] = $db->sql_build_query('INSERT',$sql_data);
        }
        
        // Armor Types
        // TODO: Update database structure before this can be done explicitly

        // Classes
        $id_fix = 0;
        foreach ($data['classes'] as $class => $info)
        {
            // 1.3 compatibility (this makes baby jesus cry you know)
            // Search through the class to armor mappings, and if there's one for this class, add it to a short-list
            $class_armor_types = array();
            foreach ($data['class_armor'] as $mapping)
            {
                if (false !== strpos(strtolower($mapping['class']), strtolower($class)))
                {
                    $class_armor_types[] = $mapping;
                }
            }

            $num = count($class_armor_types);            
            $id_fix += ($num - 1);

            // Now, for every class-armor mapping for this class, we create a new 'class'
            foreach($class_armor_types as $key => $class_armor_type)
            {
                $armor_name = $data['armor_types'][$class_armor_type['armor']]['name']; // Get armor's default name from the armor_type data
                $armor_min  = isset($class_armor_type['min']) ? intval($class_armor_type['min']) : 0;
                $armor_max  = isset($class_armor_type['max']) ? intval($class_armor_type['max']) : $max_level;
                
                $sql_data = array(
                    'class_id'        => intval($info['id']) + ($id_fix - ($num - intval($key)) + 1),
                    'class_name'      => $db->sql_escape($info['name']),
                    'class_armor_type'=> $db->sql_escape($armor_name),
                    'class_min_level' => $armor_min,
                    'class_max_level' => $armor_max,
                );
                $game_sql['classes'][] = $db->sql_build_query('INSERT',$sql_data);
            }
        }

        // Armor-Class mappings
        // TODO: Update database structure before this can be done explicitly

        // Time to start assaulting the database!
        // TODO: Being able to rollback a database transaction would be *really* useful about here

        // Discard the old table information
		// FIXME: TRUNCATE TABLE will not work if there are foreign key dependencies in the table.
		//        In other words, UPDATE statements are required.
#        $db->sql_query("TRUNCATE TABLE __classes;");
#        $db->sql_query("TRUNCATE TABLE __races;");
#        $db->sql_query("TRUNCATE TABLE __factions;");
        
        // Execute the INSERTs for the new information
        foreach ($game_sql as $table => $tabledata)
        {
            foreach ($tabledata as $sql)
            {
                echo("INSERT INTO __" . $table . $sql . ";");
                echo "\n";
            }
        }        
        
        // Other game-related information updates
        // Max level update
        $sql = "UPDATE __members 
            SET member_level = {$max_level} 
            WHERE member_level > {$max_level};";
        $db->sql_query($sql);
        
        $sql = "ALTER TABLE __members 
            MODIFY member_level tinyint(2) NOT NULL 
            default '{$max_level}';";
        $db->sql_query($sql);

        // Current game name
        $db->sql_query("UPDATE __config SET config_value = '" . $db->sql_escape($game_name) . "' WHERE config_name = 'default_game';");

        // TODO: Commit changes if no errors occured up to this point

        return true;
    }
}
?>