<?php
/******************************
 * EQdkp
 * Copyright 2002-2007
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * functions_install.php
 * Began: Wed August 1 2007
 * 
 * $Id$
 * 
 ******************************/


/**
* Returns an array of available DBMS with some data, if a DBMS is specified it will only
* return data for that DBMS and will load its extension if necessary.
*/
function get_available_dbms($dbms = false, $return_unavailable = false)
{
	global $lang;

	$available_dbms = array(
		'mysql'		=> array(
			'LABEL'			=> 'MySQL',
			'SCHEMA'		=> 'mysql',
			'MODULE'		=> 'mysql', 
			'DELIM'			=> ';',
			'COMMENTS'		=> 'remove_remarks',
			'DRIVER'		=> 'mysql',
			'AVAILABLE'		=> true,
		),
	);

	if ($dbms)
	{
		if (isset($available_dbms[$dbms]))
		{
			$available_dbms = array($dbms => $available_dbms[$dbms]);
		}
		else
		{
			return array();
		}
	}

	// now perform some checks whether they are really available
	foreach ($available_dbms as $db_name => $db_ary)
	{
		$dll = $db_ary['MODULE'];

		if (!@extension_loaded($dll))
		{
			if (!can_load_dll($dll))
			{
				if ($return_unavailable)
				{
					$available_dbms[$db_name]['AVAILABLE'] = false;
				}
				else
				{
					unset($available_dbms[$db_name]);
				}
				continue;
			}
		}
		$any_db_support = true;
	}

	if ($return_unavailable)
	{
		$available_dbms['ANY_DB_SUPPORT'] = $any_db_support;
	}
	return $available_dbms;
}


/**
* Generate the drop down of available database options
*/
function dbms_select($default = '')
{
	global $lang;
	
	$available_dbms = get_available_dbms(false, false);
	$dbms_options = '';
	foreach ($available_dbms as $dbms_name => $details)
	{
		$selected = ($dbms_name == $default) ? ' selected="selected"' : '';
		$dbms_options .= '<option value="' . $dbms_name . '"' . $selected .'>' . $lang['DLL_' . strtoupper($dbms_name)] . '</option>';
	}
	return $dbms_options;
}

/**
* Generate the drop down of available language packs
*/
function inst_language_select($default = '')
{
	global $eqdkp_root_path, $lang, $DEFAULTS;

	$lang_options = '';

	$lang_path = $eqdkp_root_path . 'language/';
	if ( $dir = @opendir($lang_path) )
	{
		while ( $file = @readdir($dir) )
		{
			if ( (!is_file($lang_path . $file)) && (!is_link($lang_path . $file)) && ($file != '.') && ($file != '..') && ($file != 'CVS') && ($file != '.svn') )
			{
				$selected = ( $DEFAULTS['default_lang'] == ucfirst(strtolower($file)) ) ? 'selected="selected"' : '';
				$lang_options .= '<option value="'. $file .'" '. $selected .'>'. ucfirst(strtolower($file)) .'</option>';
			}
		}
	}

	return $lang_options;
}


function inst_locale_select($default = '')
{
	global $eqdkp_root_path, $lang, $LOCALES, $DEFAULTS;

	$locale_options = '';

	foreach ( $LOCALES as $locale_type => $locale_desc )
	{
		$selected = $DEFAULTS['default_lang'] == $locale_type ? 'selected="selected"' : '';
		$locale_options .= '<option value="'. $locale_desc['type'] .'" '. $selected .'>'. $locale_type .'</option>';
	}

	return $locale_options;
}

/**
* Get tables of a database
*/
function get_tables($db)
{
	switch ($db->sql_layer)
	{
		case 'mysql':
		case 'mysql4':
		case 'mysqli':
			$sql = 'SHOW TABLES';
		break;

		case 'sqlite':
			$sql = 'SELECT name
				FROM sqlite_master
				WHERE type = "table"';
		break;

		case 'mssql':
		case 'mssql_odbc':
			$sql = "SELECT name 
				FROM sysobjects 
				WHERE type='U'";
		break;

		case 'postgres':
			$sql = 'SELECT relname
				FROM pg_stat_user_tables';
		break;

		case 'firebird':
			$sql = 'SELECT rdb$relation_name
				FROM rdb$relations
				WHERE rdb$view_source is null
					AND rdb$system_flag = 0';
		break;

		case 'oracle':
			$sql = 'SELECT table_name
				FROM USER_TABLES';
		break;
	}

	$result = $db->sql_query($sql);

	$tables = array();

	while ($row = $db->sql_fetchrow($result))
	{
		$tables[] = current($row);
	}

	$db->sql_freeresult($result);

	return $tables;
}

/**
* Used to test whether we are able to connect to the database the user has specified
* and identify any problems (eg there are already tables with the names we want to use
* @param	array	$dbms should be of the format of an element of the array returned by {@link get_available_dbms get_available_dbms()}
*					necessary extensions should be loaded already
*/
function connect_check_db($error_connect, &$error, $dbms, $table_prefix, $dbhost, $dbuser, $dbpasswd, $dbname, $dbport, $prefix_may_exist = false, $load_dbal = true, $unicode_check = true)
{
	global $eqdkp_root_path, $config, $lang;

	if ($load_dbal)
	{
		// Include the DB layer
		include($eqdkp_root_path . 'dbal/' . $dbms['DRIVER'] . '.php');
	}

	// Instantiate it and set return on error true
	// Note to self: If general dbal class made, and each subclass has prefix, add here.
	$sql_db = 'dbal_' . $dbms['DRIVER'];
#	$sql_db = 'SQL_DB';
	$db = new $sql_db();
	$db->sql_return_on_error(true);

	// Check that we actually have a database name before going any further.....
	if ($dbms['DRIVER'] != 'sqlite' && $dbms['DRIVER'] != 'oracle' && $dbname === '')
	{
		$error[] = $lang['INST_ERR_DB_NO_NAME'];
		return false;
	}

	// Make sure we don't have a daft user who thinks having the SQLite database in the forum directory is a good idea
/*	if ($dbms['DRIVER'] == 'sqlite' && stripos(eqdkp_realpath($dbhost), eqdkp_realpath('../')) === 0)
	{
		$error[] = $lang['INST_ERR_DB_FORUM_PATH'];
		return false;
	}
*/
	// Check the prefix length to ensure that index names are not too long and does not contain invalid characters
	switch ($dbms['DRIVER'])
	{
		case 'mysql':
		case 'mysqli':
			if (strpos($table_prefix, '-') !== false || strpos($table_prefix, '.') !== false)
			{
				$error[] = $lang['INST_ERR_PREFIX_INVALID'];
				return false;
			}

		// no break;

		case 'postgres':
			$prefix_length = 36;
		break;

		case 'mssql':
		case 'mssql_odbc':
			$prefix_length = 90;
		break;

		case 'sqlite':
			$prefix_length = 200;
		break;

		case 'firebird':
		case 'oracle':
			$prefix_length = 6;
		break;
	}

	if (strlen($table_prefix) > $prefix_length)
	{
		$error[] = sprintf($lang['INST_ERR_PREFIX_TOO_LONG'], $prefix_length);
		return false;
	}

	// Try and connect ...
	if (is_array($db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false, true)))
	{
		$db_error = $db->sql_error();
		$error[] = $lang['INST_ERR_DB_CONNECT'] . '<br />' . (($db_error['message']) ? $db_error['message'] : $lang['INST_ERR_DB_NO_ERROR']);
	}
	else
	{
		// Likely matches for an existing eqdkp installation
		if (!$prefix_may_exist)
		{
			$temp_prefix = strtolower($table_prefix);
			$table_ary = array($temp_prefix . 'raids', $temp_prefix . 'raid_attendees', $temp_prefix . 'config', $temp_prefix . 'sessions', $temp_prefix . 'users');

			$tables = get_tables($db);
			$tables = array_map('strtolower', $tables);
			$table_intersect = array_intersect($tables, $table_ary);

			if (sizeof($table_intersect))
			{
				$error[] = $lang['INST_ERR_PREFIX'];
			}
		}

		// Make sure that the user has selected a sensible DBAL for the DBMS actually installed
		switch ($dbms['DRIVER'])
		{
			case 'mysqli':
				if (version_compare(mysqli_get_server_info($db->db_connect_id), '4.1.3', '<'))
				{
					$error[] = $lang['INST_ERR_DB_NO_MYSQLI'];
				}
			break;

			case 'sqlite':
				if (version_compare(sqlite_libversion(), '2.8.2', '<'))
				{
					$error[] = $lang['INST_ERR_DB_NO_SQLITE'];
				}
			break;

			case 'firebird':
				// check the version of FB, use some hackery if we can't get access to the server info
				if ($db->service_handle !== false && function_exists('ibase_server_info'))
				{
					$val = @ibase_server_info($db->service_handle, IBASE_SVC_SERVER_VERSION);
					preg_match('#V([\d.]+)#', $val, $match);
					if ($match[1] < 2)
					{
						$error[] = $lang['INST_ERR_DB_NO_FIREBIRD'];
					}
					$db_info = @ibase_db_info($db->service_handle, $dbname, IBASE_STS_HDR_PAGES);

					preg_match('/^\\s*Page size\\s*(\\d+)/m', $db_info, $regs);
					$page_size = intval($regs[1]);
					if ($page_size < 8192)
					{
						$error[] = $lang['INST_ERR_DB_NO_FIREBIRD_PS'];
					}
				}
				else
				{
					$sql = "SELECT *
						FROM RDB$FUNCTIONS
						WHERE RDB$SYSTEM_FLAG IS NULL
							AND RDB$FUNCTION_NAME = 'CHAR_LENGTH'";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					// if its a UDF, its too old
					if ($row)
					{
						$error[] = $lang['INST_ERR_DB_NO_FIREBIRD'];
					}
					else
					{
						$sql = "SELECT FIRST 0 char_length('')
							FROM RDB\$DATABASE";
						$result = $db->sql_query($sql);
						if (!$result) // This can only fail if char_length is not defined
						{
							$error[] = $lang['INST_ERR_DB_NO_FIREBIRD'];
						}
						$db->sql_freeresult($result);
					}

					// Setup the stuff for our random table
					$char_array = array_merge(range('A', 'Z'), range('0', '9'));
					$char_len = mt_rand(7, 9);
					$char_array_len = sizeof($char_array) - 1;

					$final = '';

					for ($i = 0; $i < $char_len; $i++)
					{
						$final .= $char_array[mt_rand(0, $char_array_len)];
					}

					// Create some random table
					$sql = 'CREATE TABLE ' . $final . " (
						FIELD1 VARCHAR(255) CHARACTER SET UTF8 DEFAULT '' NOT NULL COLLATE UNICODE,
						FIELD2 INTEGER DEFAULT 0 NOT NULL);";
					$db->sql_query($sql);

					// Create an index that should fail if the page size is less than 8192
					$sql = 'CREATE INDEX ' . $final . ' ON ' . $final . '(FIELD1, FIELD2);';
					$db->sql_query($sql);

					if (ibase_errmsg() !== false)
					{
						$error[] = $lang['INST_ERR_DB_NO_FIREBIRD_PS'];
					}
					else
					{
						// Kill the old table
						$db->sql_query('DROP TABLE ' . $final . ';');
					}
					unset($final);
				}
			break;
			
			case 'oracle':
				if ($unicode_check)
				{
					$sql = "SELECT *
						FROM NLS_DATABASE_PARAMETERS
						WHERE PARAMETER = 'NLS_RDBMS_VERSION'
							OR PARAMETER = 'NLS_CHARACTERSET'";
					$result = $db->sql_query($sql);

					while ($row = $db->sql_fetchrow($result))
					{
						$stats[$row['parameter']] = $row['value'];
					}
					$db->sql_freeresult($result);

					if (version_compare($stats['NLS_RDBMS_VERSION'], '9.2', '<') && $stats['NLS_CHARACTERSET'] !== 'UTF8')
					{
						$error[] = $lang['INST_ERR_DB_NO_ORACLE'];
					}
				}
			break;
			
			case 'postgres':
				if ($unicode_check)
				{
					$sql = "SHOW server_encoding;";
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					if ($row['server_encoding'] !== 'UNICODE' && $row['server_encoding'] !== 'UTF8')
					{
						$error[] = $lang['INST_ERR_DB_NO_POSTGRES'];
					}
				}
			break;
		}

	}

	if ($error_connect && (!isset($error) || !sizeof($error)))
	{
		return true;
	}
	return false;
}

/**
* Applies addslashes() to the provided data
*
* @param    mixed   $data   Array of data or a single string
* @return   mixed           Array or string of data
*/
function slash_global_data(&$data)
{
    if ( is_array($data) )
    {
        foreach ( $data as $k => $v )
        {
            $data[$k] = ( is_array($v) ) ? slash_global_data($v) : addslashes($v);
        }
    }
    return $data;
}

/**
* Set $config_name to $config_value in CONFIG_TABLE
*
* @param    mixed   $config_name    Config name, or associative array of name => value pairs
* @param    string  $config_value
* @return   bool
*/
function config_set($config_name, $config_value='', $db = null)
{
    if ( is_null($db) )
    {
        global $db;
    }

    if ( is_object($db) )
    {
        if ( is_array($config_name) )
        {
            foreach ( $config_name as $d_name => $d_value )
            {
                config_set($d_name, $d_value);
            }
        }
        else
        {
            if ( $config_value == '' )
            {
                return false;
            }

            $sql = 'UPDATE ' . CONFIG_TABLE . "
                    SET config_value='" . strip_tags(htmlspecialchars($config_value)) . "'
                    WHERE config_name='" . $config_name . "'";
            $db->sql_query($sql);

            return true;
        }
    }

    return false;
}


/**
* set_var
*
* Set variable, used by {@link request_var the request_var function}
*
* @access private
*/
function set_var(&$result, $var, $type, $multibyte = false)
{
	settype($var, $type);
	$result = $var;

	if ($type == 'string')
	{
		$result = trim(htmlspecialchars(str_replace(array("\r\n", "\r"), array("\n", "\n"), $result), ENT_COMPAT, 'UTF-8'));

		if (!empty($result))
		{
			// Make sure multibyte characters are wellformed
			if ($multibyte)
			{
				if (!preg_match('/^./u', $result))
				{
					$result = '';
				}
			}
			else
			{
				// no multibyte, allow only ASCII (0-127)
				$result = preg_replace('/[\x80-\xFF]/', '?', $result);
			}
		}

		$result = (STRIP) ? stripslashes($result) : $result;
	}
}


/**
* request_var
*
* Used to get passed variable
*/
function request_var($var_name, $default, $multibyte = false, $cookie = false)
{
	if (!$cookie && isset($_COOKIE[$var_name]))
	{
		if (!isset($_GET[$var_name]) && !isset($_POST[$var_name]))
		{
			return (is_array($default)) ? array() : $default;
		}
		$_REQUEST[$var_name] = isset($_POST[$var_name]) ? $_POST[$var_name] : $_GET[$var_name];
	}

	if (!isset($_REQUEST[$var_name]) || (is_array($_REQUEST[$var_name]) && !is_array($default)) || (is_array($default) && !is_array($_REQUEST[$var_name])))
	{
		return (is_array($default)) ? array() : $default;
	}

	$var = $_REQUEST[$var_name];
	if (!is_array($default))
	{
		$type = gettype($default);
	}
	else
	{
		list($key_type, $type) = each($default);
		$type = gettype($type);
		$key_type = gettype($key_type);
		if ($type == 'array')
		{
			reset($default);
			list($sub_key_type, $sub_type) = each(current($default));
			$sub_type = gettype($sub_type);
			$sub_type = ($sub_type == 'array') ? 'NULL' : $sub_type;
			$sub_key_type = gettype($sub_key_type);
		}
	}

	if (is_array($var))
	{
		$_var = $var;
		$var = array();

		foreach ($_var as $k => $v)
		{
			set_var($k, $k, $key_type);
			if ($type == 'array' && is_array($v))
			{
				foreach ($v as $_k => $_v)
				{
					if (is_array($_v))
					{
						$_v = null;
					}
					set_var($_k, $_k, $sub_key_type);
					set_var($var[$k][$_k], $_v, $sub_type, $multibyte);
				}
			}
			else
			{
				if ($type == 'array' || is_array($v))
				{
					$v = null;
				}
				set_var($var[$k], $v, $type, $multibyte);
			}
		}
	}
	else
	{
		set_var($var, $var, $type, $multibyte);
	}

	return $var;
}


/**
* Removes comments from a SQL data file
*
* @param    string  $sql    SQL file contents
* @return   string
*/
function remove_remarks($sql)
{
    if ( $sql == '' )
    {
        die('Could not obtain SQL structure/data');
    }

    $retval = '';
    $lines  = explode("\n", $sql);
    unset($sql);

    foreach ( $lines as $line )
    {
        // Only parse this line if there's something on it, and we're not on the last line
        if ( strlen($line) > 0 )
        {
            // If '#' is the first character, strip the line
            $retval .= ( substr($line, 0, 1) != '#' ) ? $line . "\n" : "\n";
        }
    }
    unset($lines, $line);

    return $retval;
}

/**
* Parse multi-line SQL statements into a single line
*
* @param    string  $sql    SQL file contents
* @param    char    $delim  End-of-statement SQL delimiter
* @return   array
*/
function parse_sql($sql, $delim)
{
    if ( $sql == '' )
    {
        die('Could not obtain SQL structure/data');
    }

    $retval     = array();
    $statements = explode($delim, $sql);
    unset($sql);

    $linecount = count($statements);
    for ( $i = 0; $i < $linecount; $i++ )
    {
        if ( ($i != $linecount - 1) || (strlen($statements[$i]) > 0) )
        {
            $statements[$i] = trim($statements[$i]);
            $statements[$i] = str_replace("\r\n", '', $statements[$i]) . "\n";

            // Remove 2 or more spaces
            $statements[$i] = preg_replace('#\s{2,}#', ' ', $statements[$i]);

            $retval[] = trim($statements[$i]);
        }
    }
    unset($statements);

    return $retval;
}

/**
* Generate an HTTP/1.1 header to redirect the user to another page
* This is used during the installation when we do not have a database available to call the normal redirect function
* @param string $page The page to redirect to relative to the installer root path
*/
function redirect($page)
{
	$server_name = (!empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : getenv('SERVER_NAME');
	$server_port = (!empty($_SERVER['SERVER_PORT'])) ? (int) $_SERVER['SERVER_PORT'] : (int) getenv('SERVER_PORT');
	$secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 1 : 0;

	$script_name = (!empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : getenv('PHP_SELF');
	if (!$script_name)
	{
		$script_name = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
	}

	// Replace backslashes and doubled slashes (could happen on some proxy setups)
	$script_name = str_replace(array('\\', '//'), '/', $script_name);
	$script_path = trim(dirname($script_name));

	$url = (($secure) ? 'https://' : 'http://') . $server_name;

	if ($server_port && (($secure && $server_port <> 443) || (!$secure && $server_port <> 80)))
	{
		$url .= ':' . $server_port;
	}

	$url .= $script_path . '/' . $page;
	header('Location: ' . $url);
	exit;
}


/**
* Output an error message
* If skip is true, return and continue execution, else exit
*/
function error($error, $line, $file, $skip = false)
{
	global $eqdkp_root_path, $lang, $db, $template;

	if ($skip)
	{
		$template->assign_block_vars('checks', array(
			'S_LEGEND'	=> true,
			'LEGEND'	=> $lang['INST_ERR'],
		));

		$template->assign_block_vars('checks', array(
			'TITLE'		=> basename($file) . ' [ ' . $line . ' ]',
			'RESULT'	=> '<b style="color:red">' . $error . '</b>',
		));

		return;
	}

	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
	echo '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">';
	echo '<head>';
	echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
	echo '<title>' . $lang['INST_ERR_FATAL'] . '</title>';
	echo '<link href="' . $eqdkp_root_path . 'templates/install/admin.css" rel="stylesheet" type="text/css" media="screen" />';
	echo '</head>';
	echo '<body id="errorpage">';
	echo '<div id="wrap">';
	echo '	<div id="page-header">';
	echo '	</div>';
	echo '	<div id="page-body">';
	echo '		<div id="acp">';
	echo '		<div class="panel">';
	echo '			<span class="corners-top"><span></span></span>';
	echo '			<div id="content">';
	echo '				<h1>' . $lang['INST_ERR_FATAL'] . '</h1>';
	echo '		<p>' . $lang['INST_ERR_FATAL'] . "</p>\n";
	echo '		<p>' . basename($file) . ' [ ' . $line . " ]</p>\n";
	echo '		<p><b>' . $error . "</b></p>\n";
	echo '			</div>';
	echo '			<span class="corners-bottom"><span></span></span>';
	echo '		</div>';
	echo '		</div>';
	echo '	</div>';
	echo '	<div id="page-footer">';
	echo '		Powered by phpBB &copy; 2000, 2002, 2005, 2007 <a href="http://www.phpbb.com/">phpBB Group</a>';
	echo '	</div>';
	echo '</div>';
	echo '</body>';
	echo '</html>';

	if (!empty($db) && is_object($db))
	{
		$db->sql_close();
	}

	exit;
}

/**
* Output an error message for a database related problem
* If skip is true, return and continue execution, else exit
*/
function db_error($error, $sql, $line, $file, $skip = false)
{
	global $lang, $db, $template;

	if ($skip)
	{
		$template->assign_block_vars('checks', array(
			'S_LEGEND'	=> true,
			'LEGEND'	=> $lang['INST_ERR_FATAL'],
		));

		$template->assign_block_vars('checks', array(
			'TITLE'		=> basename($file) . ' [ ' . $line . ' ]',
			'RESULT'	=> '<b style="color:red">' . $error . '</b><br />&#187; SQL:' . $sql,
		));

		return;
	}

	$template->set_filenames(array(
		'body' => 'install_error.html')
	);
	$this->page_header();
	$this->generate_navigation();

	$template->assign_vars(array(
		'MESSAGE_TITLE'		=> $lang['INST_ERR_FATAL_DB'],
		'MESSAGE_TEXT'		=> '<p>' . basename($file) . ' [ ' . $line . ' ]</p><p>SQL : ' . $sql . '</p><p><b>' . $error . '</b></p>',
	));

	// Rollback if in transaction
	if ($db->transaction)
	{
		$db->sql_transaction('rollback');
	}

	$this->page_footer();
}


/**
* Generate the relevant HTML for an input field and the associated label and explanatory text
*/
function input_field($name, $type, $value='', $options='')
{
	global $lang;
	$tpl_type = explode(':', $type);
	$tpl = '';

	switch ($tpl_type[0])
	{
		case 'text':
		case 'password':
			$size = (int) $tpl_type[1];
			$maxlength = (int) $tpl_type[2];

			$tpl = '<input id="' . $name . '" type="' . $tpl_type[0] . '"' . (($size) ? ' size="' . $size . '"' : '') . ' maxlength="' . (($maxlength) ? $maxlength : 255) . '" name="' . $name . '" value="' . $value . '" />';
		break;

		case 'textarea':
			$rows = (int) $tpl_type[1];
			$cols = (int) $tpl_type[2];

			$tpl = '<textarea id="' . $name . '" name="' . $name . '" rows="' . $rows . '" cols="' . $cols . '">' . $value . '</textarea>';
		break;

		case 'radio':
			$key_yes	= ($value) ? ' checked="checked" id="' . $name . '"' : '';
			$key_no		= (!$value) ? ' checked="checked" id="' . $name . '"' : '';

			$tpl_type_cond = explode('_', $tpl_type[1]);
			$type_no = ($tpl_type_cond[0] == 'disabled' || $tpl_type_cond[0] == 'enabled') ? false : true;

			$tpl_no = '<label><input type="radio" name="' . $name . '" value="0"' . $key_no . ' class="radio" /> ' . (($type_no) ? $lang['NO'] : $lang['DISABLED']) . '</label>';
			$tpl_yes = '<label><input type="radio" name="' . $name . '" value="1"' . $key_yes . ' class="radio" /> ' . (($type_no) ? $lang['YES'] : $lang['ENABLED']) . '</label>';

			$tpl = ($tpl_type_cond[0] == 'yes' || $tpl_type_cond[0] == 'enabled') ? $tpl_yes . '&nbsp;&nbsp;' . $tpl_no : $tpl_no . '&nbsp;&nbsp;' . $tpl_yes;
		break;

		case 'select':
			eval('$s_options = ' . str_replace('{VALUE}', $value, $options) . ';');
			$tpl = '<select id="' . $name . '" name="' . $name . '">' . $s_options . '</select>';
		break;

		case 'custom':
			eval('$tpl = ' . str_replace('{VALUE}', $value, $options) . ';');
		break;

		default:
		break;
	}

	return $tpl;
}



/**
* Little helper for the build_hidden_fields function
*/
function _build_hidden_fields($key, $value, $specialchar, $stripslashes)
{
	$hidden_fields = '';

	if (!is_array($value))
	{
		$value = ($stripslashes) ? stripslashes($value) : $value;
		$value = ($specialchar) ? htmlspecialchars($value, ENT_COMPAT, 'UTF-8') : $value;

		$hidden_fields .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
	}
	else
	{
		foreach ($value as $_key => $_value)
		{
			$_key = ($stripslashes) ? stripslashes($_key) : $_key;
			$_key = ($specialchar) ? htmlspecialchars($_key, ENT_COMPAT, 'UTF-8') : $_key;

			$hidden_fields .= _build_hidden_fields($key . '[' . $_key . ']', $_value, $specialchar, $stripslashes);
		}
	}

	return $hidden_fields;
}

/**
* Build simple hidden fields from array
*
* @param array $field_ary an array of values to build the hidden field from
* @param bool $specialchar if true, keys and values get specialchared
* @param bool $stripslashes if true, keys and values get stripslashed
*
* @return string the hidden fields
*/
function build_hidden_fields($field_ary, $specialchar = false, $stripslashes = false)
{
	$s_hidden_fields = '';

	foreach ($field_ary as $name => $vars)
	{
		$name = ($stripslashes) ? stripslashes($name) : $name;
		$name = ($specialchar) ? htmlspecialchars($name, ENT_COMPAT, 'UTF-8') : $name;

		$s_hidden_fields .= _build_hidden_fields($name, $vars, $specialchar, $stripslashes);
	}

	return $s_hidden_fields;
}


?>