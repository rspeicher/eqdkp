<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * file.php
 * Began: Day January 1 2003
 *
 * $Id: lang_install.php 47 2007-10-15 01:48:40 dazza $
 *
 ******************************/

if ( !defined('EQDKP_INC') )
{
     die('Do not access this file directly.');
}

// Initialize the language array if it isn't already
if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// %1\$<type> prevents a possible error in strings caused
//      by another language re-ordering the variables
// $s is a string, $d is an integer, $f is a float

$lang = array_merge($lang, array(
	'inst_header'			=> 'EQdkp Installation',
	
	// ===========================================================
	// Step 1: PHP / Mysql Environment
	// ===========================================================
	
	'inst_eqdkp'			=> 'EQdkp',
		'inst_version'			=> 'Version',
		'inst_using'			=> 'Using',
		'inst_latest'			=> 'Latest',
	
	'inst_php'				=> 'PHP',
		'inst_view'				=> 'View phpinfo()',
		'inst_required'			=> 'Required',
		'inst_major_version'	=> 'Major Version',
		'inst_minor_version'	=> 'Minor Version',
		'inst_version_classification'	=> 'Version Classification',
		'inst_yes'				=> 'Yes',
		'inst_no'				=> 'No',
	
	'inst_php_modules'		=> 'PHP Modules',
		'inst_Supported'		=> 'Supported',
	
	'inst_step1'			=> 'Installation: Step 1',
		'inst_note1'			=> 'EQdkp has scanned your system and determined that you meet the minimum requirements for installation.',
		'inst_note1_error'		=> '<B><FONT SIZE="+1" COLOR="red">WARNING</font></B><BR>EQdkp has scanned your system and determined that you do not meet the minimum requirements for installation.<BR>Please upgrade to the minimum requirements.',
		'inst_button1'			=> 'Start Install',
	
	// ===========================================================
	//    Step 2: Server / Database
	// ===========================================================
	
	'inst_language_configuration'	=> 'Language Configuration',
		'inst_default_lang'				=> 'Default Language',
	
	'inst_database_configuration'	=> 'Database Configuration',
		'inst_dbtype'					=> 'Database Type',
		'inst_dbhost'					=> 'Database Host',
			'inst_default_dbhost'			=> 'localhost', // FIXME: This isn't a language-dependant variable
		'inst_dbname'					=> 'Database Name',
		'inst_dbuser'					=> 'Database Username',
		'inst_dbpass'					=> 'Database Password',
		'inst_table_prefix'				=> 'Prefix for EQdkp tables',
			'inst_default_table_prefix'		=> 'eqdkp_', // FIXME: This isn't a language-dependant variable
	
	'inst_server_configuration'		=> 'Server Configuration',
		'inst_server_name'				=> 'Domain Name',
		'inst_server_port'				=> 'Webserver Port',
		'inst_server_path'				=> 'Script path',
	
	'inst_step2'					=> 'Installation: Step 2',
		'inst_note2'					=> 'Before proceeding, please verify that your database is already created and that the username and password you provided have permission to create tables on that database',
		'inst_button2'					=> 'Install Database',
	
	
	// ===========================================================
	//    Step 3: Accounts
	// ===========================================================
	
	'inst_administrator_configuration'		=> 'Administrator Configuration',
		'inst_username'							=> 'Administrator Username',
		'inst_user_password'					=> 'Administrator Password',
		'inst_user_password_confirm'			=> 'Confrim Administrator Password',
		'inst_user_email'						=> 'Administrator Email Address',
	
	'inst_initial_accounts'	=> 'Initial Accounts',
		'inst_guild_members'	=> 'Guild Members',
	
	'inst_step3'			=> 'Installation: Step 3',
		'inst_note3'			=> 'Note: All initial accounts will be created with a password that matches the member name, please advise your members to change their passwords.',
		'inst_button3'			=> 'Install Accounts',
	
	
	// ===========================================================
	//    Step 4: EQdkp Preferences
	// ===========================================================
	
	'inst_general_settings'			=> 'General Settings',
		'inst_guildtag'					=> 'Guildtag / Alliance Name',
		'inst_guildtag_note'			=> 'Used in the title of nearly every page',
		'inst_parsetags'				=> 'Guildtags to Parse',
		'inst_parsetags_note'			=> 'Those listed will be available as options when parsing raid logs.',
		'inst_domain_name'				=> 'Domain Name',
		'inst_server_port'				=> 'Server Port',
		'inst_server_port_note'			=> 'Your webserver\'s port. Usually 80',
		'inst_script_path'				=> 'Script Path',
		'inst_script_path_note'			=> 'Path where EQdkp is located, relative to the domain name',
		'inst_site_name'				=> 'Site Name',
		'inst_site_description'			=> 'Site Description',
		'inst_point_name'				=> 'Point Name',
		'inst_point_name_note'			=> 'Ex: DKP, RP, etc.',
		'inst_enable_account_activation'		=> 'Enable Account Activation',
		'inst_none'						=> 'None',
		'inst_user'						=> 'User',
		'inst_admin'					=> 'Admin',
		'inst_default_language'			=> 'Default Language',
		'inst_default_style'			=> 'Default Style',
		'inst_default_page'				=> 'Default Index Page',
		'inst_hide_inactive'			=> 'Hide Inactive Members',
		'inst_hide_inactive_note'		=> 'Hide members that haven\'t attended a raid in [inactive period] days?',
		'inst_inactive_period'			=> 'Inactive Period',
		'inst_inactive_period_note'		=> 'Number of days a member can miss a raid and still be considered active',
		'inst_inactive_point_adj'		=> 'Inactive Point Adjustment',
		'inst_inactive_point_adj_note'			=> 'Point adjustment to make on a member when they become inactive.',
		'inst_active_point_adj'					=> 'Active Point Adjustment',
		'inst_active_point_adj_note'			=> 'Point Adjustment to make on a member when they become active.',
		'inst_enable_gzip'				=> 'Enable Gzip Compression',
	
		'inst_preview'					=> 'Preview',
		'inst_account_settings'			=> 'Account Settings',
		'inst_adjustments_per_page'		=> 'Adjustments per Page',
		'inst_basic'					=> 'Basic',
		'inst_events_per_page'			=> 'Events per Page',
		'inst_items_per_page'			=> 'Items per Page',
		'inst_news_per_page'			=> 'News Entries per Page',
		'inst_raids_per_page'			=> 'Raids per Page',
	
	'inst_step4'			=> 'Installation: Step 4',
		'inst_note4'			=> 'Note: All of these settings are configurable from within the system. Simply go to Administration Pannel > Configuration.',
		'inst_button4'			=> 'Save Preferences',
	
	
	// ===========================================================
	//    Step 5: Finish
	// ===========================================================
	
	'inst_step5'			=> 'Finished',
		'inst_note5'			=> 'Installation is now complete, you may log in below.',
	
	'login'					=> 'Login',
		'username'				=> 'Username',
		'password'				=> 'Password',
		'remember_password'		=> 'Remember me (cookie)',
	
		'lost_password'			=> 'Lost Password',
));