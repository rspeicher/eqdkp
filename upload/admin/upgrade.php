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
define('IN_ADMIN', true);
$eqdkp_root_path = './../';
require_once($eqdkp_root_path . 'common.php');

$user->check_auth('a_');

// I require MySQL version 4.0.4 minimum.
// TODO: ^ You do? for what?
$version = mysql_get_server_info();
if ( version_compare($version, '4.0', '<') )
{
    message_die("MySQL version 4.0 or above is required for EQdkp. You are currently running {$version}.");
}

// As of 1.4.0a, we're checking against an eqdkp_version config value to
// determine what we need to upgrade. So if that doesn't exist, set it here
if ( !isset($eqdkp->config['eqdkp_version']) )
{
    $eqdkp->config_set('eqdkp_version', EQDKP_VERSION);
}
elseif ( EQDKP_VERSION == $eqdkp->config['eqdkp_version'] )
{
    if ( $in->exists('run') )
    {
        header('Location: ' . path_default('upgrade.php', true));
    }
    message_die($user->lang['upgrade_complete']);
}

class Upgrade extends EQdkp_Admin
{
    function upgrade()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;
        
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

        $db->error_die(false);
    }
    
    function upgrade_run()
    {
        global $eqdkp;
        
        $upgrade_files = $this->find_upgrade_files();
        
        foreach ( $upgrade_files as $file )
        {
            unset($VERSION);
            include_once("upgrade/{$file}");
        }
    }

    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        $tpl->assign_vars(array(
            'L_EQDKP_UPGRADE'       => $user->lang['eqdkp_upgrade'],
            'L_UPGRADE_INSTRUCTION' => sprintf($user->lang['upgrade_instruction'], $eqdkp->config['eqdkp_version'], EQDKP_VERSION),
            'L_UPGRADE'             => $user->lang['upgrade'],
        ));

        $eqdkp->set_vars(array(
            'page_title'    => $user->lang['eqdkp_upgrade'],
            'template_file' => 'admin/upgrade.html',
            'display'       => true
        ));
    }
    
    ## ############################################################################
    ## Helper methods
    ## ############################################################################
    
    function find_upgrade_files()
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
    
    /**
     * Display a progress report message to the user before redirecting them to upgrade.php to run the next process
     *
     * @param string $message Message to display
     * @return void
     * @static
     */
    function progress($message)
    {
        global $user;
        
        meta_refresh(3, path_default('upgrade.php', true) . path_params('run'));
        message_die($message . "<br /><br />" . sprintf($user->lang['upgrade_continue'], 3));
    }
    
    /**
     * Updates the database version to $version
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