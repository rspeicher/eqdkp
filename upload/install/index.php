<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        install/index.php
 * Began:       Sun Jul 27 2007
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */

// ---------------------------------------------------------
// Set up environment
// ---------------------------------------------------------
define('EQDKP_INC', true);
define('IN_INSTALL', true);

//Report all errors
error_reporting(E_ALL ^ E_NOTICE);

$eqdkp_root_path = './../';

/*
* Remove variables created by register_globals from the global scope
* Thanks to Matt Kavanagh and phpBB3
*/
function deregister_globals()
{
    $not_unset = array(
        'GLOBALS' => true,
        '_GET' => true,
        '_POST' => true,
        '_COOKIE' => true,
        '_REQUEST' => true,
        '_SERVER' => true,
        '_SESSION' => true,
        '_ENV' => true,
        '_FILES' => true,
        'eqdkp_root_path' => true
    );

    // Not only will array_merge and array_keys give a warning if
    // a parameter is not an array, array_merge will actually fail.
    // So we check if _SESSION has been initialised.
    if (!isset($_SESSION) || !is_array($_SESSION))
    {
        $_SESSION = array();
    }

    // Merge all into one extremely huge array; unset
    // this later
    $input = array_merge(
        array_keys($_GET),
        array_keys($_POST),
        array_keys($_COOKIE),
        array_keys($_SERVER),
        array_keys($_SESSION),
        array_keys($_ENV),
        array_keys($_FILES)
    );

    foreach ($input as $varname)
    {
        if (isset($not_unset[$varname]))
        {
            // Hacking attempt. No point in continuing.
            exit;
        }

        unset($GLOBALS[$varname]);
    }

    unset($input);
}

set_magic_quotes_runtime(0);

// Be paranoid with passed vars
if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on')
{
    deregister_globals();
}

define('STRIP', (get_magic_quotes_gpc()) ? true : false);

// Include essential scripts
require($eqdkp_root_path . 'includes/functions.php');

include($eqdkp_root_path . 'includes/class_template.php');
include($eqdkp_root_path . 'includes/session.php');
include($eqdkp_root_path . 'includes/input.php');
include($eqdkp_root_path . 'includes/functions_admin.php');
include($eqdkp_root_path . 'includes/functions_paths.php');
require($eqdkp_root_path . 'includes/functions_install.php');


// System defaults / available database abstraction layers
$DEFAULTS = array(
    'version'       => '1.4.0 B1',
    'default_lang'  => 'English',
    'default_style' => '1',
    'default_game'  => 'wow',
    'dkp_name'      => 'DKP',
    'table_prefix'  => 'eqdkp_',
    'dbal'          => 'mysql'
);
$LOCALES = array(
    'English' => array(
        'label'   => 'English',
        'type'    => 'en_US'
    ),
    'German'  => array(
        'label'   => 'German',
        'type'    => 'de_DE'
    ),
    'French'  => array(
        'label'   => 'French',
        'type'    => 'fr_FR'
    ),
);

// Modes
// NOTE: As we probably won't be adding any of these any time soon, these are hard-coded
$MODES = array(
	'start'   => array('name' => 'Start'),
	'install' => array('name' => 'Install'),
	'upgrade' => array('name' => 'Upgrade'),
);

// NOTE: the language includes should be changed eventually so that they can be set dynamically
$language = strtolower($DEFAULTS['default_lang']);

include($eqdkp_root_path . 'language/' . $language . '/lang_install.php');
include($eqdkp_root_path . 'language/' . $language . '/lang_main.php');


// ---------------------------------------------------------
// Template Wrap class
// ---------------------------------------------------------
if ( !class_exists('Template') )
{
    die('Could not include the template file! Check to make sure that "' . $eqdkp_root_path . 'includes/class_template.php" exists!');
}

/**
 * Template_Wrap class
 * This class extends the default template to supply some install-specific templating functionality
 *
 * TODO: Check how much of this stuff is actually being used anymore, and remove the stuff that isn't used.
 */
class Template_Wrap extends Template
{
    var $error_message   = array();           // Array of errors      @var $error_message
    var $install_message = array();           // Array of messages    @var $install_message
    var $header_inc      = false;             // Printed header?      @var $header_inc
    var $tail_inc        = false;             // Printed footer?      @var $tail_inc
    var $template_file   = '';                // Template filename    @var $template_file

    function template_wrap($template_file)
    {
        $this->template_file = $template_file;

        $this->set_template('install', '');

        $this->assign_vars(array(
            'MSG_TITLE' => '',
            'MSG_TEXT'  => '',
            )
        );

        $this->set_filenames(array(
            'body' => $this->template_file
        ));
    }

    function message_die($text = '', $title = '')
    {
        $this->set_filenames(array(
            'body' => 'install_message.html'
        ));

        $this->assign_vars(array(
            'MSG_TITLE' => ( $title != '' ) ? $title : '&nbsp;',
            'MSG_TEXT'  => ( $text  != '' ) ? $text  : '&nbsp;',
            )
        );

		$this->generate_navigation('',array());

        if ( !$this->header_inc )
        {
            $this->page_header();
        }

        $this->page_tail();
    }

    function message_append($message)
    {
        $this->install_message[ sizeof($this->install_message) + 1 ] = $message;
    }

    function message_out($die = false)
    {
        sort($this->install_message);
        reset($this->install_message);

        $install_message = implode('<br /><br />', $this->install_message);

        if ( $die )
        {
            $this->message_die($install_message, 'Installation ' . (( sizeof($this->install_message) == 1 ) ? 'Note' : 'Notes'));
        }
        else
        {
            $this->assign_vars(array(
                'MSG_TITLE' => 'Installation ' . (( sizeof($this->install_message) == 1 ) ? 'Note' : 'Notes'),
                'MSG_TEXT'  => $install_message,
                )
            );
        }
    }

    function error_append($error)
    {
        $this->error_message[ (sizeof($this->error_message) + 1) ] = $error;
    }

    function error_out($die = false)
    {
        sort($this->error_message);
        reset($this->error_message);

        $error_message = implode('<br /><br />', $this->error_message);

        if ( $die )
        {
            $this->message_die($error_message, 'Installation ' . (( sizeof($this->error_message) == 1 ) ? 'Error' : 'Errors'));
        }
        else
        {
            $this->assign_vars(array(
                'MSG_TITLE' => 'Installation ' . (( sizeof($this->error_message) == 1 ) ? 'Error' : 'Errors'),
                'MSG_TEXT'  => $error_message)
            );
        }
    }

    function page_header()
    {
        global $eqdkp_root_path, $STEP;

        $this->header_inc = true;

        /*
        $now = gmdate('D, d M Y H:i:s', time()) . ' GMT';
        @header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        @header('Last-Modified: ' . $now);
        @header('Cache-Control: no-store, no-cache, must-revalidate');
        @header('Cache-Control: post-check=0, pre-check=0', false);
        @header('Pragma: no-cache');
        @header('Content-Type: text/html; charset=iso-8859-1');
        */

        $this->assign_vars(array(
            'EQDKP_ROOT_PATH' => $eqdkp_root_path,
            'INSTALL_STEP' => $STEP,
            )
        );
    }

    function page_tail()
    {
        global $DEFAULTS, $db;

        if ( sizeof($this->install_message) > 0 )
        {
            $this->message_out(false);
        }

        if ( sizeof($this->error_message) > 0 )
        {
            $this->error_message[0] = '<span style="font-weight: bold; font-size: 14px;" class="negative">NOTICE</span>';
            $this->error_out(false);
        }

        $this->assign_var('EQDKP_VERSION', $DEFAULTS['version']);

        if ( is_object($db) )
        {
            $db->sql_close();
        }

        $this->display('body');
        $this->destroy();

        exit;
    }

    /**
    * Generate the navigation tabs
    */
    function generate_navigation($mode, $subs, $selected = 'intro')
    {
        global $lang, $MODES;
    	
		// Create the menu tabs
		foreach ($MODES as $key => $option)
		{
			$cat   = $key;
			$l_cat = $option['name'];
			$url   = "index.php?mode={$cat}";

			$this->assign_block_vars('t_block1',array(
				'L_TITLE'		=> $l_cat,
				'S_SELECTED'	=> ($mode == $cat) ? true : false,
				'U_TITLE'		=> $url,
			));
		}

        $matched = false;
        foreach ($subs as $option)
        {
            $l_option = (!empty($lang['STAGE_' . $option])) ? $lang['STAGE_' . $option] : preg_replace('#_#', ' ', $option);
            $option = strtolower($option);
            $matched = ($selected == $option) ? true : $matched;

            $this->assign_block_vars('l_block2', array(
                'L_TITLE'        => $l_option,
                'S_SELECTED'    => ($selected == $option),
                'S_COMPLETE'    => !$matched,
            ));
        }
    }
}

// Let's find out what we're doing.
$in = new Input();

include($eqdkp_root_path . 'install/install.php');
include($eqdkp_root_path . 'install/upgrade.php');

$mode = $in->get('mode','start');
$sub  = $in->get('sub','');

switch ($mode)
{
	case 'start':
		$tpl = new Template_Wrap('install_install.html');
		$tpl->assign_vars(array(
			'TITLE'   => 'Let\'s Get Started',
			'BODY'    => 'Please choose an option above.',
		));

		$tpl->generate_navigation($mode, array());

        $tpl->page_header();
        $tpl->page_tail();
		break;

	case 'install':
		// If EQdkp is already installed, don't let them install it again
		if (@file_exists($eqdkp_root_path . 'config.php') && !file_exists($eqdkp_root_path . 'templates/cache/install_lock'))
		{
			include_once($eqdkp_root_path . 'config.php');
		
			if ( defined('EQDKP_INSTALLED') )
			{
				$tpl = new Template_Wrap('install_message.html');
				$tpl->message_die('EQdkp is already installed - please remove the <b>install</b> directory.', 'Installation Error');
				exit;
			}
		}

		$install = new installer("index.php");
		$install->main($mode, $sub);
		break;
	
	case 'upgrade':
		// We can't upgrade if there's no configuration file.
		if (@file_exists($eqdkp_root_path . 'config.php'))
		{
			include_once($eqdkp_root_path . 'config.php');
			
			if ( !defined('EQDKP_INSTALLED') )
			{
				$tpl = new Template_Wrap('install_message.html');
				$tpl->message_die($lang['error_upgrade_no_config'], 'Upgrade Error');
				exit;
			}
		}

		// Retrieve the appropriate database abstraction layer
        $dbal_file = $eqdkp_root_path . 'includes/db/' . $dbms . '.php';
        if ( !file_exists($dbal_file) )
        {
            $tpl->message_die('Unable to find the database abstraction layer for <b>' . $dbms . '</b>, check to make sure ' . $dbal_file . ' exists.');
        }
        include($dbal_file);

        // Connect to our database
        $sql_db = 'dbal_' . $dbms;
        $db = new $sql_db();
        $db->sql_connect($dbhost, $dbname, $dbuser, $dbpass, false);
	
		$install = new Upgrade("index.php");
		$install->main($mode, $sub);
		break;

	default:
		$tpl = new Template_Wrap('install_message.html');
		$tpl->message_die('Invalid mode specified.', 'Installation Error');
		break;
}