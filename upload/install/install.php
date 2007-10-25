<?php
/******************************
 * EQdkp
 * Copyright 2002-2007
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * install.php
 * Began: Wed August 1 2007
 * 
 * $Id: install.php 1 2007-08-01 17:36:22 Dazza $
 * 
 ******************************/

if ( !defined('IN_INSTALL') )
{
	// Possible hacking attempt
	exit;
}

class installer 
{

	var $submenu_ary = array('INTRO', 'REQUIREMENTS', 'DATABASE', 'ADMINISTRATOR', 'CONFIG_FILE', 'ADVANCED', 'CREATE_TABLE', 'FINAL');

	function main($mode, $sub)
	{	
		switch($sub)
		{
			case 'intro':
				$this->introduction();
				break;
			
			case 'requirements':
				$this->requirements();
				break;
			
			case 'database':
				$this->obtain_database_settings();
				break;
			
			case 'administrator':
				$this->obtain_administrator_info();
				break;
			
			case 'config_file':
				$this->create_config_file();
				break;
			
			case 'create_table';
				$this->create_database_tables();
				break;
			
			case 'final':
				$this->finish_install();
				break;
		}
	}

	//
	// The Installation Methods
	//	
	
	function introduction()
	{
		global $eqdkp_root_path, $lang, $DEFAULTS;

		$tpl = new Template_Wrap('install_install.html');
		
		
		$tpl->assign_vars(array(
			'TITLE'			=> $lang['INSTALL_INTRO'],
			'BODY'			=> $lang['INSTALL_INTRO_BODY'],
			'L_SUBMIT'		=> $lang['NEXT_STEP'],
			'NEXT_STEP'     => 'requirements',
		));
	
		$tpl->page_header();
		$tpl->page_tail();
	}
	
	function requirements()
	{
		global $eqdkp_root_path, $lang, $DEFAULTS;
	
		define('DEBUG', 0);

		$tpl = new Template_Wrap('install_step1.html');
	
		$tpl->assign_vars(array(
			'TITLE'		=> $lang['REQUIREMENTS_TITLE'],
			'BODY'		=> $lang['REQUIREMENTS_EXPLAIN'],

			'S_CHECKS'	=> true,
		));

		$passed = array('php' => false, 'config' => false, 'db' => false,);

		// Check EQdkp Information
		$tpl->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['EQDKP_INFO'],
			'LEGEND_EXPLAIN'	=> $lang['EQDKP_INFO_EXPLAIN'],
		));

		// Current EQdkp version
		$tpl->assign_block_vars('checks', array(
			'TITLE'			=> $lang['EQDKP_VER_CURRENT'],
			'RESULT'		=> $DEFAULTS['version'],

			'S_EXPLAIN'		=> false,
			'S_LEGEND'		=> false,
		));

		// get_latest_eqdkp_version();

		// Test for basic PHP settings
		$php_version_reqd = '4.2.0';
		
		$tpl->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['PHP_SETTINGS'],
			'LEGEND_EXPLAIN'	=> sprintf($lang['PHP_SETTINGS_EXPLAIN'], $php_version_reqd),
		));

		// Check if the PHP version on the server is the minimum required to run EQdkp
		if ( phpversion() < $php_version_reqd )
		{
			$result = '<strong style="color:red">' . $lang['NO'] . ' [' . phpversion() . ']' . '</strong>';
		}
		else
		{
			$passed['php'] = true;

			// We also give feedback on whether we're running in safe mode
			$result = '<strong style="color:green">' . $lang['YES'] . ' [' . phpversion();
			if (@ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'on')
			{
				$result .= ', ' . $lang['PHP_SAFE_MODE'];
			}
			$result .= ']' . '</strong>';
		}

		$tpl->assign_block_vars('checks', array(
			'TITLE'			=> sprintf($lang['PHP_VERSION_REQD'], $php_version_reqd),
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> false,
			'S_LEGEND'		=> false,
		));

		// Check for register_globals being enabled
		if (@ini_get('register_globals') == '1' || strtolower(@ini_get('register_globals')) == 'on')
		{
			$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:green">' . $lang['YES'] . '</strong>';
		}

		$tpl->assign_block_vars('checks', array(
			'TITLE'			=> $lang['PHP_REGISTER_GLOBALS'],
			'TITLE_EXPLAIN'	=> $lang['PHP_REGISTER_GLOBALS_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
			'S_LEGEND'		=> false,
		));		

		
		// Check for available databases
		$tpl->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['PHP_SUPPORTED_DB'],
			'LEGEND_EXPLAIN'	=> $lang['PHP_SUPPORTED_DB_EXPLAIN'],
		));

		// Show of support for multiple databases should be added here
		$available_dbms = get_available_dbms(false, true);
		$passed['db'] = $available_dbms['ANY_DB_SUPPORT'];
		unset($available_dbms['ANY_DB_SUPPORT']);

		foreach ($available_dbms as $db_name => $db_ary)
		{
			if (!$db_ary['AVAILABLE'])
			{
				$tpl->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DLL_' . strtoupper($db_name)],
					'RESULT'	=> '<span style="color:red">' . $lang['UNAVAILABLE'] . '</span>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
			else
			{
				$tpl->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DLL_' . strtoupper($db_name)],
					'RESULT'	=> '<strong style="color:green">' . $lang['AVAILABLE'] . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
		}


		// Check for other modules
		$tpl->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['PHP_OPTIONAL_MODULE'],
			'LEGEND_EXPLAIN'	=> $lang['PHP_OPTIONAL_MODULE_EXPLAIN'],
		));

		// zLib Module
		$our_zlib    = ( extension_loaded('zlib') )  ? '<strong style="color:green">' . $lang['YES'] . '</strong>' : '<strong style="color:red">' . $lang['NO'] . '</strong>';
		$their_zlib  = 'No';
	
		clearstatcache();
	
		// Check for url_fopen 
		if (@ini_get('allow_url_fopen') == '1' || strtolower(@ini_get('allow_url_fopen')) == 'on')
		{
			$result = '<strong style="color:green">' . $lang['YES'] . '</strong>';
		}
		else
		{
			$result = '<strong style="color:red">' . $lang['NO'] . '</strong>';
		}

		$tpl->assign_block_vars('checks', array(
			'TITLE'			=> $lang['PHP_URL_FOPEN_SUPPORT'],
			'TITLE_EXPLAIN'	=> $lang['PHP_URL_FOPEN_SUPPORT_EXPLAIN'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> true,
			'S_LEGEND'		=> false,
		));

		// Check to make sure necessary directories exist and are writeable
		$tpl->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['FILES_REQUIRED'],
			'LEGEND_EXPLAIN'	=> $lang['FILES_REQUIRED_EXPLAIN'],
		));


		$directories = array('templates/cache/',);

		umask(0);

		$passed['files'] = true;
		foreach ($directories as $dir)
		{
			$exists = $write = false;

			// Try to create the directory if it does not exist
			if (!file_exists($eqdkp_root_path . $dir))
			{
				if( !@mkdir($eqdkp_root_path . $dir, 0777))
				{
					$tpl->error_append('The templates cache directory could not be created, please create one manually in the templates directory.
										<br />You can do this by changing to the EQdkp root directory and typing <b>mkdir -p templates/cache/</b>');
				}
				else
				{
					$tpl->message_append('A templates cache directory was created in your templates directory, removing this directory could interfere
										  with the operation of your EQdkp installation.');
				}
				@chmod($eqdkp_root_path . $dir, 0777);
			}

			// Now really check
			if (file_exists($eqdkp_root_path . $dir) && is_dir($eqdkp_root_path . $dir))
			{
				if (!@is_writable($eqdkp_root_path . $dir))
				{
					if( !@chmod($eqdkp_root_path . $dir, 0777))
					{
						$tpl->error_append('The templates cache directory exists, but is not set to be writeable and could not be changed automatically.
											<br />Please change the permissions to 0777 manually by executing <b>chmod 0777 templates/cache</b> on your server.');
					}
					else
					{
						$tpl->message_append('The templates cache directory ahs been set to be writeable in order to let the Templating engine create cached
											  versions of the compiled templates and speed up the displaying of EQdkp pages.');
					}
				}
				$exists = true;
			}

			// Now check if it is writable by storing a simple file
			$fp = @fopen($eqdkp_root_path . $dir . 'test_lock', 'wb');
			if ($fp !== false)
			{
				$write = true;
			}
			@fclose($fp);

			@unlink($eqdkp_root_path . $dir . 'test_lock');

			$passed['files'] = ($exists && $write && $passed['files']) ? true : false;

			$exists = ($exists) ? '<strong style="color:green">' . $lang['FOUND'] . '</strong>' : '<strong style="color:red">' . $lang['NOT_FOUND'] . '</strong>';
			$write = ($write) ? ', <strong style="color:green">' . $lang['WRITABLE'] . '</strong>' : (($exists) ? ', <strong style="color:red">' . $lang['UNWRITABLE'] . '</strong>' : '');

			$tpl->assign_block_vars('checks', array(
				'TITLE'		=> $dir,
				'RESULT'	=> $exists . $write,

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}	

		// Check permissions on files/directories it would be useful access to
		$tpl->assign_block_vars('checks', array(
			'S_LEGEND'			=> true,
			'LEGEND'			=> $lang['FILES_OPTIONAL'],
			'LEGEND_EXPLAIN'	=> $lang['FILES_OPTIONAL_EXPLAIN'],
		));

		$directories = array('config.php',);

		foreach ($directories as $dir)
		{
			$write = $exists = true;
			if (file_exists($eqdkp_root_path . $dir))
			{
				if (!@is_writable($eqdkp_root_path . $dir))
				{
					$write = false;
				}
			}
			else
			{
				$write = $exists = false;
			}

			$exists_str = ($exists) ? '<strong style="color:green">' . $lang['FOUND'] . '</strong>' : '<strong style="color:red">' . $lang['NOT_FOUND'] . '</strong>';
			$write_str = ($write) ? ', <strong style="color:green">' . $lang['WRITABLE'] . '</strong>' : (($exists) ? ', <strong style="color:red">' . $lang['UNWRITABLE'] . '</strong>' : '');

			$tpl->assign_block_vars('checks', array(
				'TITLE'		=> $dir,
				'RESULT'	=> $exists_str . $write_str,

				'S_EXPLAIN'	=> false,
				'S_LEGEND'	=> false,
			));
		}


		if ( !$passed['php'] || !$passed['db'] || !$passed['files'] )
		{
			$tpl->assign_vars(array(
				'NEXT_STEP' 	=> 'requirements',
				'L_SUBMIT' 		=> 'Check Requirements Again',
			));
			$tpl->error_append('<span style="font-weight: bold; font-size: 14px;">Sorry, your server does not meet the minimum requirements for EQdkp.</span>');
		}
		else
		{
			$tpl->assign_vars(array(
				'NEXT_STEP' 	=> 'database',
				'L_SUBMIT' 		=> 'Start Install',
			));
			$tpl->message_append('EQdkp has scanned your server and determined that it meets the minimum requirements in order to install.');
		}
	
		//
		// Output the page
		//
		$tpl->assign_vars(array(
			'EQDKP_ROOT_PATH' => $eqdkp_root_path,
		));

		$tpl->generate_navigation($this->submenu_ary, 'requirements');
			
		$tpl->page_header();
		$tpl->page_tail();
	}

	
	function obtain_database_settings()
	{
		global $eqdkp_root_path, $lang, $DEFAULTS, $DBALS, $LOCALES;
	
		define('DEBUG', 2);

		$tpl = new Template_Wrap('install_step2.html');

		$tpl->assign_vars(array(
			'TITLE' 	=> '',
			'BODY' 		=> '',
			
			'S_CHECKS'	=> false,
		));

		// Obtain any submitted data
		$data = $this->get_submitted_data();

		// Prepare for displaying database-related information
		$connect_test = false;
		$error = array();
		$available_dbms = get_available_dbms(false, true);

		// Has the user opted to test the connection?
		if (isset($_POST['testdb']))
		{
			if (!isset($available_dbms[$data['dbms']]) || !$available_dbms[$data['dbms']]['AVAILABLE'])
			{
				$error['db'][] = $lang['INST_ERR_NO_DB'];
				$connect_test = false;
			}
			else
			{
				$connect_test = connect_check_db(true, $error, $available_dbms[$data['dbms']], $data['table_prefix'], $data['dbhost'], $data['dbuser'], $data['dbpass'], $data['dbname'], $data['dbport']);
			}

			$tpl->assign_block_vars('checks', array(
				'S_LEGEND'			=> true,
				'LEGEND'			=> $lang['DB_CONNECTION'],
				'LEGEND_EXPLAIN'	=> false,
			));

			if ($connect_test)
			{
				$tpl->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DB_TEST'],
					'RESULT'	=> '<strong style="color:green">' . $lang['SUCCESSFUL_CONNECT'] . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
			else
			{
				$tpl->assign_block_vars('checks', array(
					'TITLE'		=> $lang['DB_TEST'],
					'RESULT'	=> '<strong style="color:red">' . implode('<br />', $error) . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
			
			$tpl->assign_vars(array(
				'S_CHECKS' => true,
			));
		}
	
		if (!$connect_test)
		{
			// Update the list of available DBMS modules to only contain those which can be used
			$available_dbms_temp = array();
			foreach ($available_dbms as $type => $dbms_ary)
			{
				if (!$dbms_ary['AVAILABLE'])
				{
					continue;
				}

				$available_dbms_temp[$type] = $dbms_ary;
			}

			$available_dbms = &$available_dbms_temp;

			//
			// Determine server settings
			//
			$server_name = ( !empty($_SERVER['HTTP_HOST']) ) ? $_SERVER['HTTP_HOST'] : $_ENV['HTTP_HOST'];
		
			if ( (!empty($_SERVER['SERVER_PORT'])) || (!empty($_ENV['SERVER_PORT'])) )
			{
				$server_port = ( !empty($_SERVER['SERVER_PORT']) ) ? $_SERVER['SERVER_PORT'] : $_ENV['SERVER_PORT'];
			}
			else
			{
				$server_port = '80';
			}

			// Note to self: Try to replace the server path input with an automatic generation of the path
			$script_name = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
			if (!$script_name)
			{
				$script_name = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
			}
			$server_path = trim(dirname($script_name));
			$server_path = preg_replace('#install$#', '', $server_path);
			$server_path = preg_replace('#[\\\\/]{2,}#', '/', $server_path);


			// And now for the main part of this page
			$data['table_prefix'] = (!empty($data['table_prefix']) ? $data['table_prefix'] : 'eqdkp_');
			$data['server_name']  = (!empty($data['server_name']) ? $data['server_name'] : $server_name);
			$data['server_port']  = (!empty($data['server_port']) ? $data['server_port'] : $server_port);
			$data['server_path']  = (!empty($data['server_path']) ? $data['server_path'] : $server_path);
			
			foreach ( array($this->default_config_options, $this->db_config_options, $this->server_config_options) as $option_groups)
			{
				foreach ( $option_groups as $config_key => $vars)
				{
					if (!is_array($vars) && strpos($config_key, 'legend') === false)
					{
						continue;
					}
	
					if (strpos($config_key, 'legend') !== false)
					{
						$tpl->assign_block_vars('options', array(
							'S_LEGEND'		=> true,
							'LEGEND'		=> $lang[$vars])
						);
	
						continue;
					}
	
					$options = isset($vars['options']) ? $vars['options'] : '';
	
					$tpl->assign_block_vars('options', array(
						'KEY'			=> $config_key,
						'TITLE'			=> $lang[$vars['lang']],
						'S_EXPLAIN'		=> $vars['explain'],
						'S_LEGEND'		=> false,
						'TITLE_EXPLAIN'	=> ($vars['explain']) ? $lang[$vars['lang'] . '_EXPLAIN'] : '',
						'CONTENT'		=> input_field($config_key, $vars['type'], $data[$config_key], $options),
						)
					);
				}
			}
		}

		// Set up the next place to go

		$s_hidden_fields = '';
		$s_hidden_fields .= '<input type="hidden" name="language" value="' . $data['language'] . '" />';
		$s_hidden_fields .= ($connect_test) ? '' : '<input type="hidden" name="testdb" value="true" />';

		if ($connect_test)
		{
			foreach (array_merge($this->default_config_options, $this->db_config_options, $this->server_config_options) as $config_key => $vars)
			{
				if (!is_array($vars))
				{
					continue;
				}
				$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
			}
		}

		if($connect_test)
		{
			$tpl->assign_vars(array(
				'NEXT_STEP' 	=> 'administrator',
				'L_SUBMIT' 		=> 'Proceed to Next Step',
			));
			$tpl->message_append('EQdkp has scanned your server and determined that it meets the minimum requirements in order to install.');
		}
		else
		{
			$tpl->assign_vars(array(
				'NEXT_STEP' 	=> 'database',
				'L_SUBMIT' 		=> 'Test Database Connection',
			));
			$tpl->message_append('Before proceeding, please verify that the database name you provided is already created and that the user you provided has permission to create tables in that database');
		}
	
		$tpl->assign_vars(array(
			'S_HIDDEN'			=> $s_hidden_fields,
			'S_OPTIONS' 		=> ($connect_test) ? false : true,
			'EQDKP_ROOT_PATH' 	=> $eqdkp_root_path,
		));

		$tpl->generate_navigation($this->submenu_ary, 'database');

		$tpl->page_header();
		$tpl->page_tail();
	}
	
	function obtain_administrator_info()
	{
		global $eqdkp_root_path, $lang, $DEFAULTS, $DBALS, $LOCALES;
	
		define('DEBUG', 2);

		$tpl = new Template_Wrap('install_step3.html');
	
		$tpl->assign_vars(array(
			'TITLE' 	=> '',
			'BODY' 		=> '',

			'S_CHECKS' => false,
		));
	
		// Obtain any submitted data
		$data = $this->get_submitted_data();

		if ($data['dbms'] == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			redirect("index.php");
		}
		
		$passed = false;
		$s_hidden_fields = '';

		$data['default_lang'] = ($data['default_lang'] !== '') ? $data['default_lang'] : $data['language'];

		if (isset($_POST['check']))
		{
			$error = array();

			// Check the entered email address and password
			if ($data['admin_name'] == '' || $data['admin_pass1'] == '' || $data['admin_pass2'] == '' || $data['admin_email1'] == '' || $data['admin_email2'] == '')
			{
				$error[] = $lang['INST_ERR_MISSING_DATA'];
			}

			if ($data['admin_pass1'] != $data['admin_pass2'] && $data['admin_pass1'] != '')
			{
				$error[] = $lang['INST_ERR_PASSWORD_MISMATCH'];
			}

			// Test against the default username rules
			if ($data['admin_name'] != '' && strlen($data['admin_name']) < 3)
			{
				$error[] = $lang['INST_ERR_USER_TOO_SHORT'];
			}

			if ($data['admin_name'] != '' && strlen($data['admin_name']) > 20)
			{
				$error[] = $lang['INST_ERR_USER_TOO_LONG'];
			}

			// Test against the default password rules
			if ($data['admin_pass1'] != '' && strlen($data['admin_pass1']) < 6)
			{
				$error[] = $lang['INST_ERR_PASSWORD_TOO_SHORT'];
			}

			if ($data['admin_pass1'] != '' && strlen($data['admin_pass1']) > 30)
			{
				$error[] = $lang['INST_ERR_PASSWORD_TOO_LONG'];
			}

			if ($data['admin_email1'] != $data['admin_email2'] && $data['admin_email1'] != '')
			{
				$error[] = $lang['INST_ERR_EMAIL_MISMATCH'];
			}

			if ($data['admin_email1'] != '' && !preg_match('/^[a-z0-9&\'\.\-_\+]+@(?:([a-z0-9\-]+\.([a-z0-9\-]+\.)*[a-z]+)|localhost)$/i', $data['admin_email1']))
			{
				$error[] = $lang['INST_ERR_EMAIL_INVALID'];
			}

			$tpl->assign_block_vars('checks', array(
				'S_LEGEND'			=> true,
				'LEGEND'			=> $lang['STAGE_ADMINISTRATOR'],
				'LEGEND_EXPLAIN'	=> false,
			));

			if (!sizeof($error))
			{
				$passed = true;
				$tpl->assign_block_vars('checks', array(
					'TITLE'		=> $lang['ADMIN_TEST'],
					'RESULT'	=> '<strong style="color:green">' . $lang['TESTS_PASSED'] . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
			else
			{
				$tpl->assign_block_vars('checks', array(
					'TITLE'		=> $lang['ADMIN_TEST'],
					'RESULT'	=> '<strong style="color:red">' . implode('<br />', $error) . '</strong>',

					'S_EXPLAIN'	=> false,
					'S_LEGEND'	=> false,
				));
			}
			
			$tpl->assign_vars(array(
				'S_CHECKS' => true,
			));
		}

		if (!$passed)
		{
			foreach ($this->admin_config_options as $config_key => $vars)
			{
				if (!is_array($vars) && strpos($config_key, 'legend') === false)
				{
					continue;
				}

				if (strpos($config_key, 'legend') !== false)
				{
					$tpl->assign_block_vars('options', array(
						'S_LEGEND'		=> true,
						'LEGEND'		=> $lang[$vars])
					);

					continue;
				}

				$options = isset($vars['options']) ? $vars['options'] : '';

				$tpl->assign_block_vars('options', array(
					'KEY'			=> $config_key,
					'TITLE'			=> $lang[$vars['lang']],
					'S_EXPLAIN'		=> $vars['explain'],
					'S_LEGEND'		=> false,
					'TITLE_EXPLAIN'	=> ($vars['explain']) ? $lang[$vars['lang'] . '_EXPLAIN'] : '',
					'CONTENT'		=> input_field($config_key, $vars['type'], $data[$config_key], $options),
					)
				);
			}
		}
		else
		{
			foreach ($this->admin_config_options as $config_key => $vars)
			{
				if (!is_array($vars))
				{
					continue;
				}
				$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
			}
		}
		
		foreach (array_merge($this->default_config_options, $this->db_config_options, $this->server_config_options) as $config_key => $vars)
		{
			if (!is_array($vars))
			{
				continue;
			}
			$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
		}

		$submit = $lang['NEXT_STEP'];

		$url = ($passed) ? "config_file" : "administrator";
		$s_hidden_fields .= ($passed) ? '' : '<input type="hidden" name="check" value="true" />';

		$tpl->assign_vars(array(
			'L_SUBMIT'	=> $submit,
			'S_HIDDEN'	=> $s_hidden_fields,
			'S_OPTIONS' => $passed ? false : true,
			'NEXT_STEP'	=> $url,
			'EQDKP_ROOT_PATH' 	=> $eqdkp_root_path,
		));

		$tpl->generate_navigation($this->submenu_ary, 'administrator');

		$tpl->page_header();
		$tpl->page_tail();
	}

	
	function create_config_file()
	{
		global $eqdkp_root_path, $lang, $DEFAULTS;
	
		define('DEBUG', 2);

		$tpl = new Template_Wrap('install_config.html');

		// Obtain any submitted data
		$data = $this->get_submitted_data();

		if ($data['dbms'] == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			redirect("index.php");
		}

		$s_hidden_fields = '<input type="hidden" name="language" value="' . $data['language'] . '" />';
		$written = false;

		// Create a lock file to indicate that there is an install in progress
		$fp = @fopen($eqdkp_root_path . 'templates/cache/install_lock', 'wb');
		if ($fp === false)
		{
			// We were unable to create the lock file - abort
			error($lang['UNABLE_WRITE_LOCK'], __LINE__, __FILE__);
		}
		@fclose($fp);

		@chmod($eqdkp_root_path . 'templates/cache/install_lock', 0666);

		// Write the config file information
		$config_file  = "";
		$config_file .= "<?php\n\n";
		$config_file .= "\$dbms         = '" . $data['dbms']        . "'; \n";
		$config_file .= "\$dbhost       = '" . $data['dbhost']        . "'; \n";
		$config_file .= "\$dbname       = '" . $data['dbname']        . "'; \n";
		$config_file .= "\$dbuser       = '" . $data['dbuser']        . "'; \n";
		$config_file .= "\$dbpass       = '" . $data['dbpass']        . "'; \n";
		$config_file .= "\$ns           = '" . $data['server_name']   . "'; \n";
		$config_file .= "\$table_prefix = '" . $data['table_prefix']  . "';\n\n";
		$config_file .= "\$debug        = '0'; \n";
		$config_file .= "\n" . 'define(\'EQDKP_INSTALLED\', true);' . "\n";
		$config_file .= "?" . ">";
	
		
		// Attempt to write out the config file directly. If it works, this is the easiest way to do it ...
		if ((file_exists($eqdkp_root_path . 'config.php') && is_writable($eqdkp_root_path . 'config.php')) || is_writable($eqdkp_root_path))
		{
			// Assume it will work ... if nothing goes wrong below
			$written = true;

			if (!($fp = @fopen($eqdkp_root_path . 'config.php', 'w')))
			{
				// Something went wrong ... so let's try another method
				$written = false;
			}

			if (!(@fwrite($fp, $config_file)))
			{
				// Something went wrong ... so let's try another method
				$written = false;
			}

			@fclose($fp);

			if ($written)
			{
				@chmod($eqdkp_root_path . 'config.php', 0644);
			}
		}

		if (isset($_POST['dldone']))
		{
			// Do a basic check to make sure that the file has been uploaded
			// Note that all we check is that the file has _something_ in it
			// We don't compare the contents exactly - if they can't upload
			// a single file correctly, it's likely they will have other problems....
			if (filesize($eqdkp_root_path . 'config.php') > 10)
			{
				$written = true;
			}
		}

		$config_options = array_merge($this->default_config_options, $this->db_config_options, $this->admin_config_options, $this->server_config_options);

		foreach ($config_options as $config_key => $vars)
		{
			if (!is_array($vars))
			{
				continue;
			}
			$s_hidden_fields .= '<input type="hidden" name="' . $config_key . '" value="' . $data[$config_key] . '" />';
		}

		if (!$written)
		{
			// OK, so it didn't work let's try the alternatives

			if (isset($_POST['dlconfig']))
			{
				// They want a copy of the file to download, so send the relevant headers and dump out the data
				header("Content-Type: text/x-delimtext; name=\"config.php\"");
				header("Content-disposition: attachment; filename=config.php");
				echo $config_file;
				exit;
			}

			// The option to download the config file is always available, so output it here
			$tpl->assign_vars(array(
				'TITLE' 				=> '',
				'BODY'					=> $lang['CONFIG_FILE_UNABLE_WRITE'],
				'L_DL_CONFIG'			=> $lang['DL_CONFIG'],
				'L_DL_CONFIG_EXPLAIN'	=> $lang['DL_CONFIG_EXPLAIN'],
				'L_DL_DONE'				=> $lang['DONE'],
				'L_DL_DOWNLOAD'			=> $lang['DL_DOWNLOAD'],
				'S_HIDDEN'				=> $s_hidden_fields,
				'S_SHOW_DOWNLOAD'		=> true,
				'S_CHECKS'  			=> false,
				'S_OPTIONS' 			=> false,
				'NEXT_STEP' 			=> 'config_file',
				'L_SUBMIT' 				=> false,
			));
		}
		else
		{
			$tpl->assign_vars(array(
				'TITLE' 				=> '',
				'BODY'					=> $lang['CONFIG_FILE_WRITTEN'],
				'L_SUBMIT'				=> $lang['NEXT_STEP'],
				'S_HIDDEN'				=> $s_hidden_fields,
				'S_SHOW_DOWNLOAD'		=> false,
				'S_CHECKS'  			=> false,
				'S_OPTIONS' 			=> false,
				'NEXT_STEP' 			=> 'create_table',
			));
		}

		$tpl->assign_vars(array(
			'EQDKP_ROOT_PATH' 			=> $eqdkp_root_path,
		));

		$tpl->generate_navigation($this->submenu_ary, 'config_file');

		$tpl->page_header();
		$tpl->page_tail();

	}

	
	function create_database_tables()
	{
		global $eqdkp_root_path, $lang, $db, $DEFAULTS, $LOCALES;

		define('DEBUG', 2);

		$tpl = new Template_Wrap('install_config.html');

		$s_hidden_fields = '';

		// Obtain any submitted data
		$data = $this->get_submitted_data();

		if ($data['dbms'] == '')
		{
			// Someone's been silly and tried calling this page direct
			// So we send them back to the start to do it again properly
			redirect("index.php");
		}

		include($eqdkp_root_path . 'config.php');
	

		define('CONFIG_TABLE', $data['table_prefix'] . 'config');
		define('USERS_TABLE',  $data['table_prefix'] . 'users');
		define('STYLES_TABLE', $data['table_prefix'] . 'styles');
	
		//
		// Database population
		//
		// If we get here and the extension isn't loaded it should be safe to just go ahead and load it 
		$available_dbms = get_available_dbms($data['dbms']);

		$dbal_file = $eqdkp_root_path . 'includes/db/' . $available_dbms[$data['dbms']]['DRIVER'] . '.php';
		if ( !file_exists($dbal_file) )
		{
			$tpl->message_die('Unable to find the database abstraction layer for <b>' . $available_dbms[$data['dbms']]['DRIVER'] . '</b>, check to make sure ' . $dbal_file . ' exists.');
		}
		include($dbal_file);

		// Connect to our database
		$sql_db = 'dbal_' . $available_dbms[$data['dbms']]['DRIVER'];
		$db = new $sql_db();
		$db->sql_connect($data['dbhost'], $data['dbname'], $data['dbuser'], $data['dbpass'], false);

		// Set some nice names for the sql files to use to populate the database
		$db_structure_file = 'schemas/' . $available_dbms[$data['dbms']]['SCHEMA'] . '_structure.sql';
		$db_data_file      = 'schemas/' . $available_dbms[$data['dbms']]['SCHEMA'] . '_data.sql';
	
		$remove_remarks_function = $available_dbms[$data['dbms']]['COMMENTS'];
		$delimiter = $available_dbms[$data['dbms']]['DELIM'];
		
		// Parse structure file and create database tables
		$sql = @fread(@fopen($db_structure_file, 'r'), @filesize($db_structure_file));
		$sql = preg_replace('#eqdkp\_(\S+?)([\s\.,]|$)#', $data['table_prefix'] . '\\1\\2', $sql);

		$sql = $remove_remarks_function($sql);
		$sql = parse_sql($sql, $available_dbms[$data['dbms']]['DELIM']);

	
		// FIXME: No way to roll back changes if any particular query fails.
		$sql_count = count($sql);
		$i = 0;
		
		while ( $i < $sql_count ) 
		{
			if (isset($sql[$i]) && $sql[$i] != "") 
			{
				if ( !($db->query($sql[$i]) )) 
				{
					$tpl->message_die('Failed to connect to database <b>' . $data['dbname'] . '</b> as <b>' . $data['dbuser'] . '@' . $data['dbhost'] . '</b>
							   <br /><br /><a href="install.php">Restart Installation</a>');
				}
			}

			$i++;
		}
		unset($sql);
	
		// Parse the data file and populate the database tables
		$sql = @fread(@fopen($db_data_file, 'r'), @filesize($db_data_file));
		$sql = preg_replace('#eqdkp\_(\S+?)([\s\.,]|$)#', $data['table_prefix'] . '\\1\\2', $sql);
	
		$sql = $remove_remarks_function($sql);
		$sql = parse_sql($sql, $available_dbms[$data['dbms']]['DELIM']);
	
		// FIXME: No way to roll back changes if any particular query fails.
		$sql_count = count($sql);
		$i = 0;
	
		while ( $i < $sql_count ) 
		{	
			if (isset($sql[$i]) && $sql[$i] != "") 
			{
				if ( !($db->query($sql[$i]) )) 
				{
					$tpl->message_die('Failed to connect to database <b>' . $data['dbname'] . '</b> as <b>' . $data['dbuser'] . '@' . $data['dbhost'] . '</b>
									   <br /><br /><a href="index.php">Restart Installation</a>');	
				}
			}
	
			$i++;
		}
		unset($sql);
		
		// Script path fix
		$data['server_path'] .= (substr($data['server_path'], strlen($data['server_path'])-1) == '/') ? '' : '/';
		
		//
		// Update some config settings
		//
		$db->query('UPDATE ' . CONFIG_TABLE . " SET config_name='eqdkp_start' WHERE config_name='" . $data['table_prefix'] . "start'");
		$db->query("UPDATE " . CONFIG_TABLE . " SET config_value='" . $data['server_name'] . "' WHERE config_name='server_name'");
		$db->query("UPDATE " . CONFIG_TABLE . " SET config_value='" . $data['server_port'] . "' WHERE config_name='server_port'");
		$db->query("UPDATE " . CONFIG_TABLE . " SET config_value='" . $data['server_path'] . "' WHERE config_name='server_path'");
		$db->query("UPDATE " . CONFIG_TABLE . " SET config_value='" . $data['default_lang'] . "' WHERE config_name='default_lang'");
		$db->query("UPDATE " . CONFIG_TABLE . " SET config_value='" . $data['default_locale'] . "' WHERE config_name='default_locale'");
		
		//
		// Update admin account
		//
		
		$admin_password = ( $data['admin_pass1'] == $data['admin_pass2'] ) ? md5($data['admin_pass1']) : md5('admin');
		
		// FIXME: add a random password generator thing.
		$query = $db->build_query('UPDATE', array(
			'username' 		=> $data['admin_name'],
			'user_password' => $admin_password,
			'user_lang'     => $data['default_lang'],
			'user_email' 	=> $data['admin_email1'],
			'user_active' 	=> '1',
		));

		$db->query('UPDATE ' . USERS_TABLE . ' SET ' . $query . " WHERE user_id='1'");
		$db->query("UPDATE " . CONFIG_TABLE . " SET config_value='" . $data['admin_email1'] . "' WHERE config_name='admin_email'");


		$submit = $lang['NEXT_STEP'];

		$s_hidden_fields = build_hidden_fields($data);

		$tpl->assign_vars(array(
			'TITLE' 				=> '',
			'BODY'					=> $lang['STAGE_CREATE_TABLE_EXPLAIN'],
			'L_SUBMIT'				=> $submit,
			'S_HIDDEN'				=> $s_hidden_fields,
			'S_SHOW_DOWNLOAD'		=> false,
			'S_CHECKS'  			=> false,
			'S_OPTIONS' 			=> false,

			'NEXT_STEP' 			=> 'final',
			'EQDKP_ROOT_PATH'		=> $eqdkp_root_path,
		));

		// NOTE: This shouldn't really ever happen, but just in case it does...
		if ( $data['admin_pass1'] != $data['admin_pass2'] )
		{
			$tpl->message_append('<p><span style="font-weight: bold; font-size: 14px; color: #990000;">NOTICE:</span></p>
				<p>The passwords you provided did not match, so a new password has been generated for you.<br />Your administrator account password is: <strong>' . $admin_password . '</strong>.</p>
				<p>Please take a moment and take note of this password! You can change it later by logging in and going to your account settings.</p>');
		}

		$tpl->generate_navigation($this->submenu_ary, 'create_table');

		$tpl->page_header();
		$tpl->page_tail();		
	}
	
	function finish_install()
	{
		global $eqdkp_root_path, $db, $lang, $DEFAULTS;
	
		define('DEBUG', 0);
	
		$tpl = new Template_Wrap('install_step4.html');
		
		$tpl->message_append('Your administrator account has been created, log in above to be taken to the EQdkp configuration page.');
	
		$tpl->assign_vars(array(
			'TITLE' 				=> $lang['INSTALL_CONGRATS'],
			'BODY'					=> $lang['INSTALL_CONGRATS_EXPLAIN'],
			'L_SUBMIT'				=> $lang['INSTALL_LOGIN'],
			'S_SHOW_DOWNLOAD'		=> false,
			'S_CHECKS'				=> false,
			'S_OPTIONS' 			=> false,

			'EQDKP_ROOT_PATH'		=> $eqdkp_root_path,
		));

		// Remove the lock file
		@unlink($nutron_root_path . 'templates/cache/install_lock');

	
		$tpl->generate_navigation($this->submenu_ary, 'final');

		$tpl->page_header();
		$tpl->page_tail();
	}


/*
 * Helper Functions
 */
 
 	/**
	* Get latest eqdkp version
	*/
	function get_latest_eqdkp_version()
	{
		$result = $lang['UNKNOWN'];
		$sh = @fsockopen('eqdkp.com', 80, $errno, $error, 5);
		if ( !$sh )
		{
			$result = $lang['EQDKP_VER_CHECK_CONN_FAIL'];
		}
		else
		{
			@fputs($sh, "GET /version.php HTTP/1.1\r\nHost: eqdkp.com\r\nConnection: close\r\n\r\n");
			while ( !@feof($sh) )
			{
				$content = @fgets($sh, 512);
				if ( preg_match('#<version>(.+)</version>#i', $content, $version) )
				{
					$result = $version[1];
					break;
				}
				else
				{
					$result = $lang['EQDKP_VER_CHECK_FAIL'];
				}
			}
		}
		@fclose($sh);

		$tpl->assign_block_vars('checks', array(
			'TITLE'			=> $lang['EQDKP_VER_LATEST'],
			'RESULT'		=> $result,

			'S_EXPLAIN'		=> false,
			'S_LEGEND'		=> false,
		));
		
		return $result;
	}
 
	/**
	* Get submitted data
	*/
	function get_submitted_data()
	{
		return array(
			'language'		=> basename(request_var('language', '')),

			'dbms'			=> request_var('dbms', ''),
			'dbhost'		=> request_var('dbhost', ''),
			'dbport'		=> request_var('dbport', ''),
			'dbuser'		=> request_var('dbuser', ''),
			'dbpass'		=> htmlspecialchars_decode(request_var('dbpass', '', true)),
			'dbname'		=> request_var('dbname', ''),
			'table_prefix'	=> request_var('table_prefix', ''),

			'default_lang'		=> basename(request_var('default_lang', '')),
			'default_locale'	=> basename(request_var('default_locale', '')),

			'admin_name'		=> request_var('admin_name', '', true),
			'admin_pass1'		=> request_var('admin_pass1', '', true),
			'admin_pass2'		=> request_var('admin_pass2', '', true),
			'admin_email1'		=> strtolower(request_var('admin_email1', '')),
			'admin_email2'		=> strtolower(request_var('admin_email2', '')),
			
			'server_name'		=> request_var('server_name', ''),
			'server_port'		=> request_var('server_port', ''),
			'server_path'		=> request_var('server_path', ''),
		);
	} 

	/**
	* The variables that we will be passing between pages
	* Used to retrieve data quickly on each page
	*/
	var $request_vars = array('language', 'dbhost', 'dbuser', 'dbpass', 'dbname', 'dbms', 'table_prefix', 'default_lang', 'default_locale', 'admin_name', 'admin_pass1', 'admin_pass2', 'admin_email', 'server_name', 'server_port', 'server_path');	

	var $default_config_options = array(
		'legend1'				=> 'DEFAULT_CONFIG',
		'default_lang'			=> array('lang' => 'DEFAULT_LANG',		'type' => 'select', 'options' => 'inst_language_select(\'{VALUE}\')', 'explain' => false),
		'default_locale'		=> array('lang' => 'DEFAULT_LOCALE',	'type' => 'select', 'options' => 'inst_locale_select(\'{VALUE}\')', 'explain' => false),
	);

	var $db_config_options = array(
		'legend1'				=> 'DB_CONFIG',
		'dbms'					=> array('lang' => 'DB_TYPE',		'type' => 'select', 'options' => 'dbms_select(\'{VALUE}\')', 'explain' => false),
		'dbhost'				=> array('lang' => 'DB_HOST',		'type' => 'text:25:100', 'explain' => false),
		'dbname'				=> array('lang' => 'DB_NAME',		'type' => 'text:25:100', 'explain' => false),
		'dbuser'				=> array('lang' => 'DB_USERNAME',	'type' => 'text:25:100', 'explain' => false),
		'dbpass'				=> array('lang' => 'DB_PASSWORD',	'type' => 'password:25:100', 'explain' => false),
		'table_prefix'			=> array('lang' => 'TABLE_PREFIX',	'type' => 'text:25:100', 'explain' => false),
	);

	var $server_config_options = array(
		'legend1'				=> 'SERVER_CONFIG',
		'server_name'			=> array('lang' => 'SERVER_NAME',		'type' => 'text:40:255', 'explain' => false),
		'server_port'			=> array('lang' => 'DB_PORT',			'type' => 'text:5:5', 'explain' => true),
		'server_path'			=> array('lang' => 'SERVER_PATH',		'type' => 'text::255', 'explain' => true),
	);

	var $admin_config_options = array(
		'legend1'				=> 'ADMIN_CONFIG',
		'admin_name'			=> array('lang' => 'ADMIN_USERNAME',			'type' => 'text:25:100', 'explain' => false),
		'admin_pass1'			=> array('lang' => 'ADMIN_PASSWORD',			'type' => 'password:25:100', 'explain' => false),
		'admin_pass2'			=> array('lang' => 'ADMIN_PASSWORD_CONFIRM',	'type' => 'password:25:100', 'explain' => false),
		'admin_email1'			=> array('lang' => 'ADMIN_EMAIL',				'type' => 'text:25:100', 'explain' => false),
		'admin_email2'			=> array('lang' => 'ADMIN_EMAIL',				'type' => 'text:25:100', 'explain' => false),
	);

}
?>