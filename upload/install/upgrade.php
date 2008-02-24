<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        upgrade.php
 * Began:       Tue Jul 1 2003
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     upgrade
 * @version     $Rev$
 */

if ( !defined('IN_INSTALL') )
{
    exit;
}

class Upgrade
{
	
	var $submenu_ary = array('INTRO', 'UPGRADE', 'FINAL');
	var $install_url = '';

	function upgrader($url)
	{
		$this->install_url = $url;
	}

    function main($mode, $sub)
    {
        // NOTE: If the sub isn't a valid installation step, throw them to the start page.
        $sub = (!in_array(strtoupper($sub), $this->submenu_ary)) ? 'intro' : $sub;
		
        switch($sub)
        {
			case 'intro':
				$this->introduction($mode, $sub);
				break;
				
			case 'upgrade':
				$this->upgrade_run($mode, $sub);
				break;
				
			case 'final':
				$this->finalize($mode, $sub);
				break;
		}
	}
    
	
    ## ########################################################################
    ## Upgrade methods
    ## ########################################################################
    
    /**
     * Introductory Step
     */
    function introduction($mode, $sub)
    {
        global $db, $in, $lang, $eqdkp_root_path, $DEFAULTS;
        
		$tpl = new Template_Wrap('install_install.html');
		
        $tpl->assign_vars(array(
            'TITLE'               => $lang['eqdkp_upgrade'],
            'BODY'                => $lang['eqdkp_upgrade_explain'],
        ));

        // Obtain any submitted data
		$data = $this->get_submitted_data();
				
        $result = get_latest_eqdkp_version();
        $tpl->assign_block_vars('checks', array(
            'TITLE'           => $lang['EQDKP_VER_LATEST'],
            'RESULT'          => $result,

            'S_EXPLAIN'       => false,
            'S_LEGEND'        => false,
        ));

		// Retrieve the installed version number of EQdkp
		// NOTE: This key will not exist in the database in versions earlier than 1.3.2
		$eqdkp_version = Upgrade::get_version();
		
        if ( $eqdkp_version == false )
        {
			// FIXME: If eqdkp_version doesn't exist in the database here, we need to do something about that now.
			// The upgrade process seems to call on that key a lot...
			// FIXME: Perhaps the current upgrade version shouldn't be retrieved from the database, and instead should
			// be passed as a hidden variable between pages using hidden form fields...
		
            // No version configuration variable, meaning the installed version is 
			// 1.3.2 or lower, so have the user select their version (just this once).
			$config_key = 'eqdkp_version';

			$tpl->assign_vars(array(
	            'S_OPTIONS'              => true,
			));

            // We can hard-code these version numbers because they'll never change.
            $versions = array(
                '1.3.2', '1.3.1', '1.3.0', '1.2.0', '1.2.0RC2', '1.2.0RC1', 
                '1.2.0B2', '1.2.0B1', '1.1.0', '1.0.0'
            );

			// Build a list of options for a select field
			$eqdkp_version_options = '';
			foreach ($versions as $version)
			{
				$selected = ($version == $data[$config_key]) ? ' selected="selected"' : '';
				$eqdkp_version_options .= '<option value="' . $version . '"' . $selected .'>' . $version . '</option>';
			}

			// Build the select field
			$tpl->assign_block_vars('options', array(
				'KEY'             => $config_key,
				'TITLE'           => $lang['EQDKP_VERSION'],
				'S_EXPLAIN'       => true,
				'S_LEGEND'        => false,
				'TITLE_EXPLAIN'   => $lang['EQDKP_VERSION_EXPLAIN'],
				'CONTENT'         => '<select id="' . $config_key . '" name="' . $config_key . '">' . $eqdkp_version_options . '</select>',
			));
        }
        else if ( strcasecmp($eqdkp_version, $DEFAULTS['version']) != 0)
		{
			// Upgrade from a version newer than 1.4.0 B1			
            $tpl->assign_block_vars('checks',array(
				'S_LEGEND'       => true,
				'LEGEND'         => $lang['upgrade'],
				'LEGEND_EXPLAIN' => sprintf($lang['upgrade_instruction'], $eqdkp_version, $DEFAULTS['version']),
			));
        }
		else
		{
			// The current version matches the upgrade version; no upgrade necessary.
			$message = sprintf($lang['upgrade_complete'], $eqdkp_version);
			$tpl->message_die($message,'');
		}

        // Figure out where we're bound for next
        $url    = $this->install_url . "?mode=$mode&amp;sub=upgrade";
        $submit = $lang['UPGRADE'];

        //
        // Output the page
        //
        $tpl->assign_vars(array(
            'L_SUBMIT'               => $submit,

			'S_CHECKS'               => true,
            'U_ACTION'               => $url,
        ));

        $tpl->generate_navigation($mode, $this->submenu_ary, $sub);

        $tpl->page_header();
        $tpl->page_tail();
    }

    function upgrade_run($mode, $sub)
    {
		$data = $this->get_submitted_data();
		
        if ( $data['eqdkp_version'] != '' )
        {
            // We're coming from the version selection drop-down
            // Set the database value to the input value and run as normal
            $version = preg_replace('/[^\w\.]/', '', $data['eqdkp_version']);
            Upgrade::set_version($version);
            Upgrade::progress(sprintf($user->lang['upgrade_started'], $version));
        }
        else
        {
            $upgrade_files = $this->_find_upgrade_files();
        
            foreach ( $upgrade_files as $file )
            {
                unset($VERSION);
                include_once("upgrade/{$file}");
            }
        }
    }

    
    ## ########################################################################
    ## Helper methods
    ## ########################################################################
    
    /**
     * Get an array of valid upgrade scripts.
     *
     * @return array
     * @access private
     */
    function _find_upgrade_files()
    {
        $retval = array();
        
        $path = dirname(__FILE__) . '/upgrade/';
        
        if ( $dir = opendir($path) )
        {
            while ( $file = readdir($dir) )
            {
                if ( is_file($path . $file) && preg_match('/^upgrade-[0-9_\.]+\.php$/', $file) )
                {
                    $retval[] = $file;
                }
            }
        }
        
        return $retval;
    }
    
    ## ########################################################################
    ## Static helper methods
    ## ########################################################################
    
    /**
     * Execute an array of query strings
     *
     * @param array $queries Array of SQL queries
     * @return void
     * @static
     */
    function execute($queries)
    {
        global $db;
        
        foreach ( $queries as $sql )
        {
            $db->query($sql);
        }
    }
    
    /**
     * Display a progress report message to the user before redirecting them to 
     * upgrade.php to run the next process
     * 
     * Note: If $message is nothing but a version string, it will automatically 
     * become "Completed upgrade to $VERSION."
     *
     * @param string $message Message to display
     * @param bool $auto_refresh Automatically refresh and continue the upgrade?
     * @return void
     * @static
     */
    function progress($message, $auto_refresh = true)
    {
        global $lang, $tpl;
        
		if (!is_object($tpl))
		{
			$tpl = new Template_Wrap('install_message.html');
		}
		
        if ( preg_match('/^[\w\.]+$/', $message) )
        {
            $message = sprintf($user->lang['upgrade_progress'], $message);
        }
        
		// FIXME: Because this is a static method, this may not be appropriate should we add more steps to the upgrade process...
		$url = path_default('install/index.php') . path_params(array('mode' => 'upgrade', 'sub' => 'upgrade', 'run' => ''));
		
        if ( $auto_refresh )
        {
            $delay = 2;
            $tpl->meta_refresh($delay, $url);
            $tpl->message_die($message . "<br /><br />" . sprintf($lang['upgrade_continuing'], $delay));
        }
        else
        {
            $message = $message . '<br /><a href="' . $url . '">' . $lang['upgrade_continue'] . '</a>';
            $tpl->message_die($message);
        }
    }
    
    /**
     * Updates the version configuration variable to $version
     *
     * @param string $version Version string
     * @return void
     * @static
     */
    function set_version($version)
    {
		global $db;
	
		// FIXME: This will not work in the event that EQdkp's version is 1.3.2 or lower.
        $db->sql_query("UPDATE __config SET config_value = '" . $version . "' WHERE config_name = 'eqdkp_version'");
    }
    
	/**
	 * Retrieves EQdkp's version from the database
	 */
	function get_version()
	{
		global $db;
		
		$version = $db->sql_query_first("SELECT config_value from __config WHERE config_name = 'eqdkp_version'");
		
		return $version;
	}
	
    /**
     * Determines if the upgrade file for $version should be executed
     *
     * @param string $version Version string
     * @return bool
     * @static
     */
    function should_run($version)
    {
        global $db, $in;
        
		// FIXME: In some versions of EQdkp, the version will not be set in the database.
		$eqdkp_version = Upgrade::get_version();
		
        if ( $eqdkp_version == false )
        {
            // If we included an upgrade file and we don't have a prior version set,
            // something went wrong. Bounce them back to the selection page.
			// FIXME: Need to redirect to a different location now... an upgrade error page perhaps.
            header('Location: ' . path_default('install/upgrade.php'));
            exit;
        }
        
        if ( $in->exists('run') && isset($version) && version_compare($eqdkp_version, $version, '<') )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Allows a version upgrade script to force the user to delete deprecated
     * files before continuing with that version's upgrade.
     *
     * @param array $files Paths to check, relative to EQdkp's root
     * @return void
     * @static
     */
    function assert_deleted($files)
    {
        global $lang;
        global $eqdkp_root_path;
        
        if ( !is_array($files) )
        {
            return;
        }
        
        $to_delete = array();
        foreach ( $files as $file )
        {
            $path       = $eqdkp_root_path . preg_replace('/^\//', '', $file);
            $type_check = ( preg_match('/\/$/', $file) ) ? 'is_dir' : 'is_file';
            
            if ( file_exists($path) && $type_check($path) )
            {
                $to_delete[] = $file;
            }
        }
        
        if ( count($to_delete) > 0 )
        {
            $message = $lang['upgrade_delete'] . "<ul>";
            foreach ( $to_delete as $v )
            {
                $message = $message . "<li>{$v}</li>";
            }
            $message = $message . "</ul>";
            
            Upgrade::progress($message, false);
        }
    }
    
    /**
     * Prepares a table to accept a UNIQUE index by deleting all but one of any
     * duplicate rows
     * 
     * <code>
     * // Prepare __raid_attendees for a raid_id-member_name key
     * Upgrade::prepare_uniquekey('__raid_attendees', array('raid_id', 'member_name'));
     * 
     * // Prepare __auth_users for a user_id-auth_id key
     * // Note that the '__' table name prefix isn't required
     * Upgrade::prepare_uniquekey('auth_users', array('user_id', auth_id'));
     * </code>
     *
     * @param string $table Table to prepare
     * @param array $fields Field(s) being made UNIQUE
     * @return void
     * @static
     */
    function prepare_uniquekey($table, $fields)
    {
        global $db;
        
        if ( !is_array($fields) )
        {
            return;
        }
        
        $table = preg_replace('/^__/', '', $table);
        
        $string_fields = implode(', ', $fields); // String field theory?
        $sql = "SELECT {$string_fields}, COUNT(*) as num
                FROM __{$table}
                GROUP BY {$string_fields}
                HAVING num > 1";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $clauses = array();
            
            // Perform the correct type of cleaning on each field
            foreach ( $fields as $field )
            {
                $int = preg_match('/_id$/', $field);
                if ( $int )
                {
                    $val = intval($row[$field]);
                }
                else
                {
                    $val = $db->escape($row[$field]);
                }
                
                $clauses[] = "(`{$field}` = '{$val}')";
            }
            $limit = $row['num'] - 1;

            $clauses = implode(' AND ', $clauses);
            
            // Delete all but 1 record from this group
            $sql = "DELETE FROM __{$table}
                    WHERE ({$clauses})
                    LIMIT {$limit}";
            $db->query($sql);
            
            unset($clauses, $val, $int);
        }
        $db->free_result($result);
    }


    /**
     * Get submitted data
     */
    function get_submitted_data()
    {
        global $in;
		
        return array(
            'language'        => basename($in->get('language', '')),
            'eqdkp_version'   => $in->get('eqdkp_version', ''),
		);
	}    

}
?>