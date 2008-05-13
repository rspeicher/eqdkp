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
    function _create_database_tables()
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
         * FIXME: This method will probably fall down on account of foreign key constraints for classes and races and such.
         *
         * TODO: Use $games[$game_id]['available'] information to only bother working with what we have.
         * TODO: Replace use of $info['name'] with the keys themselves. Then upon retrieval from the db, the 'name' can be replaced with the language string.
         * FIXME: properly escape input.
         *
         * FIXME: ID information. Right now, if ID isn't provided in the game info file OR the IDs aren't unique, this will all fail horribly. 
         *        A new method is going to have to be added somewhere (perhaps in the install_game method, before this one is called) where the IDs 
         *        are checked, and if they aren't provided or valid, simply rewrite all of them. Hell, we have to make mappings between IDs, so
         *        it won't matter so much for gameA->gameB installs. However, it might matter for gameA->gameA (upgrading EQdkp or similar).
         */
        $game_sql = array(
            'factions'      => array(),
            'races'         => array(),
            'armor_types'   => array(),
            'classes'       => array(),
            'armor_classes' => array(),
        );

        // Generics for all games
        $game_sql['factions'][]      = $db->sql_build_query('INSERT',array('faction_id' => 0, 'faction_name' => 'Unknown', 'faction_key' => 'unknown'));
        $game_sql['races'][]         = $db->sql_build_query('INSERT',array('race_id' => 0, 'race_name' => 'Unknown', 'race_key' => 'unknown', 'race_faction_id' => 0));
        $game_sql['armor_types'][]   = $db->sql_build_query('INSERT',array('armor_type_id' => 0, 'armor_type_name' => 'None', 'armor_type_key' => 'none'));
        $game_sql['classes'][]       = $db->sql_build_query('INSERT',array('class_id' => 0, 'class_name' => 'Unknown', 'class_key' => 'unknown'));
        $game_sql['class_armor'][]   = $db->sql_build_query('INSERT',array('class_id' => 0, 'armor_type_id' => 0)); // Default value for armor_min_level = 0. Default value for armor_max_level is null.
        
        // Factions
        foreach ($data['factions'] as $faction => $info)
        {
            $sql_data = array(
                'faction_id'   => intval($info['id']),
                'faction_name' => $info['name'],
                'faction_key'  => str_replace(' ','_',$faction),
            );
            $game_sql['factions'][] = $db->sql_build_query('INSERT',$sql_data);
        }
        
        // Races
        foreach ($data['races'] as $race => $info)
        {
            $sql_data = array(
                'race_id'         => intval($info['id']),
                'race_name'       => $info['name'],
                'race_key'        => str_replace(' ','_',$race),
                'race_faction_id' => (is_numeric($info['faction'])) ? intval($info['faction']) : intval($data['factions'][$info['faction']]['id']),
            );
            $game_sql['races'][] = $db->sql_build_query('INSERT',$sql_data);
        }
        
        // Armor Types
        foreach ($data['armor_types'] as $armor_type => $info)
        {
            $sql_data = array(
                'armor_type_id'   => intval($info['id']),
                'armor_type_name' => $info['name'],
                'armor_type_key'  => str_replace(' ','_',$armor_type),
            );
            $game_sql['armor_types'][] = $db->sql_build_query('INSERT',$sql_data);
        }

        // Classes
        foreach ($data['classes'] as $class => $info)
        {
            $sql_data = array(
                'class_id'        => intval($info['id']),
                'class_name'      => $info['name'],
                'class_key'       => str_replace(' ','_',$class),
            );
            $game_sql['classes'][] = $db->sql_build_query('INSERT',$sql_data);
        }
            
        // Class to Armor mappings have to be done a little later on.
        
        //
        // Time to start assaulting the database!
        //
        // TODO: Being able to rollback a database transaction would be *really* useful about here

        // Discard the old table information
        $db->sql_query("TRUNCATE TABLE __class_armor");

        // NOTE: TRUNCATE TABLE will not work if there are foreign key dependencies in the table.
        //       In other words, REPLACE INTO statements are required.
        //       To map the old-to-new game information, there is the option of creating temporary tables, 
        //       adding the old + new classes to that, then replacing back to the values in the real tables.
        //       That way would probably take a hell of a lot longer though. Still, at some point there needs to be one additional 
        //       'swap' id row so you can switch from the old class ID to the new ones without accidentally losing class values.
        
        // Execute the database entries for the new information
        foreach ($game_sql as $table => $tabledata)
        {
            foreach ($tabledata as $sqldata)
            {
                $sql = "REPLACE INTO __{$table}" . $sqldata;
                $db->sql_query($sql);
            }
        }
        
        // Method of choice - strip all old class IDs.
        // At this point, user classes and such might start to go haywire (foreign key associations and such).
        // FIXME: It is currently assumed the class IDs are incremental. They have to be checked up above still, so it makes life easier for me down here.
        $class_count = count($game_sql['classes']);
        $faction_count = count($game_sql['factions']);
        $race_count = count($game_sql['races']);
        $armor_type_count = count($game_sql['armor_types']);
        
        // Remove old classes
        $sql = "DELETE FROM __classes
                WHERE class_id > '" . intval($class_count - 1) . "'";
        $db->sql_query($sql);

        // Remove old factions
        $sql = "DELETE FROM __factions
                WHERE faction_id > '" . intval($faction_count - 1) . "'";
        $db->sql_query($sql);

        // Remove old classes
        $sql = "DELETE FROM __races
                WHERE race_id > '" . intval($race_count - 1) . "'";
        $db->sql_query($sql);
        
        // Remove old armor
        $sql = "DELETE FROM __armor_types
                WHERE armor_type_id > '" . intval($armor_type_count - 1) . "'";
        $db->sql_query($sql);
        
        
        // Class-Armor mappings
        foreach ($data['class_armor'] as $class_armor => $info)
        {
            // NOTE: There is the option of retrieving this info from the database here to ensure valid FKs are attained,
            // but the data held in the $game_data array should be valid enough.
            
            $sql_data = array(
                'class_id' => $data['classes'][$info['class']]['id'],
                'armor_type_id' => $data['armor_types'][$info['armor']]['id'],
            );
            
            if (isset($info['min']))
            {
                $sql_data['armor_min_level'] = intval($info['min']);
            }
            
            if (isset($info['max']))
            {
                $sql_data['armor_max_level'] = intval($info['max']);
            }
            
            $sql = $db->sql_build_query('INSERT',$sql_data);
            
            $db->sql_query("INSERT INTO __class_armor" . $sql);
        }


        
        // Other game-related information updates
        // Max level update
        $sql = "UPDATE __members 
                SET member_level = {$max_level} 
                WHERE member_level > {$max_level};";
        $db->sql_query($sql);
        
        $sql = "ALTER TABLE __members 
                MODIFY member_level tinyint(2) NOT NULL 
                default '{$max_level}'";
        $db->sql_query($sql);
        
        // NOTE: The script which called install_game() should update the config table.
        // TODO: Commit changes if no errors occured up to this point

        return true;
    }
}
?>