<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        common.php
 * Began:       Tue Dec 17 2002
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */

if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

//error_reporting (E_ALL ^ E_NOTICE);
error_reporting (E_ALL);

// Default the site-wide variables
$gen_simple_header = false;
$eqdkp_root_path   = ( isset($eqdkp_root_path) ) ? preg_replace('/[^a-z\.\/]/', '', $eqdkp_root_path) : './';

if ( !is_file($eqdkp_root_path . 'config.php') )
{
    die('Error: could not locate configuration file.');
}

require_once($eqdkp_root_path . 'config.php');

if ( !defined('EQDKP_INSTALLED') )
{
    header('Location: ' . $eqdkp_root_path . 'install.php');
}

// Constants
define('EQDKP_VERSION', '1.4.0');
define('NO_CACHE', true);

define('DEBUG', 2);

// User Levels
define('ANONYMOUS', -1);

// User activation
define('USER_ACTIVATION_NONE',  0);
define('USER_ACTIVATION_SELF',  1);
define('USER_ACTIVATION_ADMIN', 2);

// Auth Options
define('A_EVENT_ADD',    1);
define('A_EVENT_UPD',    2);
define('A_EVENT_DEL',    3);
define('A_GROUPADJ_ADD', 4);
define('A_GROUPADJ_UPD', 5);
define('A_GROUPADJ_DEL', 6);
define('A_INDIVADJ_ADD', 7);
define('A_INDIVADJ_UPD', 8);
define('A_INDIVADJ_DEL', 9);
define('A_ITEM_ADD',    10);
define('A_ITEM_UPD',    11);
define('A_ITEM_DEL',    12);
define('A_NEWS_ADD',    13);
define('A_NEWS_UPD',    14);
define('A_NEWS_DEL',    15);
define('A_RAID_ADD',    16);
define('A_RAID_UPD',    17);
define('A_RAID_DEL',    18);
define('A_TURNIN_ADD',  19);
define('A_CONFIG_MAN',  20);
define('A_MEMBERS_MAN', 21);
define('A_USERS_MAN',   22);
define('A_LOGS_VIEW',   23);
define('U_EVENT_LIST',  24);
define('U_EVENT_VIEW',  25);
define('U_ITEM_LIST',   26);
define('U_ITEM_VIEW',   27);
define('U_MEMBER_LIST', 28);
define('U_MEMBER_VIEW', 29);
define('U_RAID_LIST',   30);
define('U_RAID_VIEW',   31);
define('A_PLUGINS_MAN', 32);
define('A_STYLES_MAN',  33);
define('A_BACKUP',      36);

// Backwards compatibility for pre-1.4
$dbms = ( !isset($dbms) && isset($dbtype) ) ? $dbtype : $dbms;

require($eqdkp_root_path . 'includes/functions.php');
require($eqdkp_root_path . 'includes/functions_paths.php');
require($eqdkp_root_path . 'includes/db/' . $dbms . '.php');
require($eqdkp_root_path . 'includes/eqdkp.php');
require($eqdkp_root_path . 'includes/session.php');
require($eqdkp_root_path . 'includes/class_template.php');
require($eqdkp_root_path . 'includes/eqdkp_plugins.php');
require($eqdkp_root_path . 'includes/input.php');
require($eqdkp_root_path . 'games/game_manager.php');

$tpl  = new Template();
$in   = new Input();
$user = new Session();
$db   = new $sql_db();

// Connect to the database
$db->sql_connect($dbhost, $dbname, $dbuser, $dbpass, false);

// Initialize the eqdkp module
$eqdkp = new EQdkp($eqdkp_root_path);

// Set the locale
// TODO: Shouldn't this be per-user? That was rhetorical. It should.
$cur_locale = $eqdkp->config['default_locale'];
setlocale(LC_ALL, $cur_locale);

// TODO: Remove this, it's for legacy only
// $SID = '?s=';

// Start up the user/session management
$user->start();
$user->setup($in->get('style', 0));


// Initialize the Game Manager
$gm = new Game_Manager();

// Start plugin management
$pm = new EQdkp_Plugin_Manager(true, DEBUG);

// Ensure that, if we're not upgrading, the install folder is gone or unreadable
install_check();

// Populate the admin menu if we're in an admin page, they have admin permissions, and $gen_simple_header is false
if ( (defined('IN_ADMIN')) && (IN_ADMIN === true) )
{
    if ( $user->check_auth('a_', false) )
    {
        require_once($eqdkp_root_path . 'includes/functions_admin.php');
        if ( !$gen_simple_header )
        {
            include($eqdkp_root_path . 'admin/index.php');
        }
    }
}

/**
 * Ensure that the install folder is deleted or unreadable
 *
 * @ignore
 */
function install_check()
{
	global $user;

    $path = dirname(__FILE__);

    // Let the page go through if we're performing an upgrade
    if ( preg_match('/upgrade|login\.php$/', $_SERVER['PHP_SELF']) )
    {
        return;
    }
    
    if ( file_exists($path . '/install/') && is_readable($path . '/install/') )
    {
		if (!$user->check_auth('a_'))
		{
			// The site should not stop working in the event of the install folder still being there. At the very least we need a message page.
			// Also, let's not tell the average joe that the install directory is still there...
	        message_die($user->lang['EQDKP_DISABLED_EXPLAIN'], $user->lang['EQDKP_DISABLED']);
		}
		else 
		{
			// FIXME: I don't like this way. I want to put this message in the admin panel somewhere as well.
			// If the admin is in the admin panel, put a (nice) warning here saying EQdkp is 'disabled' for admin users?
			$install_dir = sprintf("{$path}%sinstall%s", DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
			
			if (!defined('IN_ADMIN'))
			{
				message_die(sprintf($user->lang['REMOVE_INSTALL_FOLDER_EXPLAIN'], $install_dir), $user->lang['EQDKP_DISABLED']);
			}
		}
    }
}