<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        game_installer.php
 * Began:       Tue Nov 20 2007
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

include_once('game_manager.php');

class Game_Installer extends Game_Manager
{
    
	/**
	 * Mappings from old game data to new game data
	 * 
	 * This information is necessary in order to ensure referential integrity for foreign keys in the database
	 */
	// NOTE: Where should these mappings be entered and how?
	var $mappings = array(
		'factions'      => array(),
		'races'         => array(),
		'armor_types'   => array(),
		'classes'       => array(),
		'armor_classes' => array(),
	);


    function Game_Installer()
    {
        $this->Game_Manager();
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
        // TODO: ID Validation + correcting / assigning

        // TODO: Mapping between old game data and new game data (WoW class IDs -> EQ class IDs etc.)
        
        $result = $this->_create_database_tables();
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
	// FIXME: Remove the echo_sql parameter when we're ready to release. It's useful for testing rather than doing.
    function _create_database_tables($echo_sql = false)
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
                'faction_id'   => intval($info['id']),
                'faction_name' => $info['name'],
            );
            $game_sql['factions'][] = $db->sql_build_query('INSERT',$sql_data);
        }
        
        // Races
        $race_sql = array();
        foreach ($data['races'] as $race => $info)
        {
            $sql_data = array(
                'race_id'         => intval($info['id']),
                'race_name'       => $info['name'],
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
                    'class_name'      => $info['name'],
                    'class_armor_type'=> $armor_name,
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
#        $db->sql_query("TRUNCATE TABLE __classes");
#        $db->sql_query("TRUNCATE TABLE __races");
#        $db->sql_query("TRUNCATE TABLE __factions");
        
        // Execute the INSERTs for the new information
        foreach ($game_sql as $table => $tabledata)
        {
            foreach ($tabledata as $sqldata)

            {
				// FIXME: Remove the echos once we're ready to release. Still kind of useful for testing.
				if ($echo_sql)
				{
					echo("REPLACE INTO __" . $table . $sqldata);
					echo "\n";
				}
				else
				{
					$sql = "REPLACE INTO __{$table}" . $sqldata;
					$db->sql_query($sql);
				}
            }
        }        
        
        // Other game-related information updates
        // FIXME: Remove echo_sql calls when we're ready to release.
		// Max level update
        $sql = "UPDATE __members 
                SET member_level = {$max_level} 
                WHERE member_level > {$max_level};";
		if ($echo_sql)
		{
			echo $sql . "<br />\n";
		}
		else 
		{
        	$db->sql_query($sql);		
		}
        
        $sql = "ALTER TABLE __members 
                MODIFY member_level tinyint(2) NOT NULL 
                default '{$max_level}'";
		if ($echo_sql)
		{
			echo $sql . "<br />\n";
		}
		else 
		{
        	$db->sql_query($sql);
		}
		
        // NOTE: The script which called install_game() should update the config table.
        // TODO: Commit changes if no errors occured up to this point

        return true;
    }
}
?>