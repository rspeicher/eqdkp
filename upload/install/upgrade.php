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
 * @package     eqdkp
 * @version     $Rev$
 */

define('EQDKP_INC', true);
define('IN_INSTALL', true);
$eqdkp_root_path = './../';
require_once($eqdkp_root_path . 'common.php');

// I require MySQL version 4.0.4 minimum.
// TODO: ^ You do? for what?
$mysql_version = mysql_get_server_info();
if ( version_compare($mysql_version, '4.0', '<') )
{
    message_die("MySQL version 4.0 or above is required for EQdkp. You are currently running {$mysql_version}.");
}
unset($mysql_version);

// If our database version is already at the script version, bounce them back 
// to the entrance with a notification
if ( isset($eqdkp->config['eqdkp_version']) && EQDKP_VERSION == $eqdkp->config['eqdkp_version'] )
{
    if ( $in->exists('run') )
    {
        header('Location: ' . path_default('upgrade.php', true));
        exit;
    }
    message_die(sprintf($user->lang['upgrade_complete'], EQDKP_VERSION));
}

class Upgrade extends EQdkp_Admin
{
    function upgrade()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        
        parent::eqdkp_admin();
        
        $this->assoc_buttons(array(
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_'
            ),
        ));
        
        $this->assoc_params(array(
            'run' => array(
                'name'    => 'run',
                'value'   => '',
                'process' => 'upgrade_run',
                'check'   => 'a_'
            ),
        ));
    }
    
    function upgrade_run()
    {
        global $eqdkp, $in, $user;
        
        if ( $in->exists('eqdkp_version') )
        {
            // We're coming from the version selection drop-down
            // Set the database value to the input value and run as normal
            $version = preg_replace('/[^\w\.]/', '', $in->get('eqdkp_version'));
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

    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        
        if ( !isset($eqdkp->config['eqdkp_version']) )
        {
            // No version configuration variable, meaning we're 1.3.2 or lower
            // So have the user select their version, just this once
            $tpl->assign_var('S_SELECTVERSION', true);
            
            // We can hard-code these version numbers because they'll never change
            $versions = array(
                '1.3.2', '1.3.1', '1.3.0', '1.2.0', '1.2.0RC2', '1.2.0RC1', 
                '1.2.0B2', '1.2.0B1', '1.1.0', '1.0.0'
            );
            foreach ( $versions as $version )
            {
                $tpl->assign_block_vars('version_row', array(
                    'VERSION' => $version
                ));
            }
            
            $instructions = $user->lang['upgrade_selversion'];
        }
        else
        {
            $tpl->assign_var('S_SELECTVERSION', false);
            $instructions = sprintf($user->lang['upgrade_instruction'], $eqdkp->config['eqdkp_version'], EQDKP_VERSION);
        }
        
        $tpl->assign_vars(array(
            'L_EQDKP_UPGRADE'       => $user->lang['eqdkp_upgrade'],
            'L_UPGRADE_INSTRUCTION' => $instructions,
            'L_UPGRADE'             => $user->lang['upgrade'],
        ));

        $eqdkp->set_vars(array(
            'page_title'    => $user->lang['eqdkp_upgrade'],
            'template_file' => 'admin/upgrade.html',
            'display'       => true
        ));
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
     * @return void
     * @static
     */
    function progress($message)
    {
        global $user;
        
        if ( preg_match('/^[\w\.]+$/', $message) )
        {
            $message = sprintf($user->lang['upgrade_progress'], $message);
        }
        
        $delay = 2;
        meta_refresh($delay, path_default('upgrade.php', true) . path_params('run'));
        message_die($message . "<br /><br />" . sprintf($user->lang['upgrade_continue'], $delay));
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
        global $eqdkp;
        $eqdkp->config_set('eqdkp_version', $version);
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
        global $eqdkp, $in;
        
        if ( !isset($eqdkp->config['eqdkp_version']) )
        {
            // If we included an upgrade file and we don't have a prior version set,
            // something went wrong. Bounce them back to the selection page.
            header('Location: ' . path_default('upgrade.php', true));
            exit;
        }
        
        if ( $in->exists('run') && isset($version) && version_compare($eqdkp->config['eqdkp_version'], $version, '<') )
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

$upgrade = new Upgrade();
$upgrade->process();