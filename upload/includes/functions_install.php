<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        functions_install.php
 * Began:       Wed Aug 1 2007
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */


/**
* Returns an array of available DBMS with some data, if a DBMS is specified it will only
* return data for that DBMS and will load its extension if necessary.
*/
function get_available_dbms($dbms = false, $return_unavailable = false)
{
    global $lang;

    $available_dbms = array(
        'mysql'       => array(
            'LABEL'         => 'MySQL',
            'SCHEMA'        => 'mysql',
            'MODULE'        => 'mysql', 
            'DELIM'         => ';',
            'COMMENTS'      => 'remove_remarks',
            'DRIVER'        => 'mysql',
            'AVAILABLE'     => true,
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
        $selected = (strpos(strtolower($DEFAULTS['default_lang']), strtolower($locale_type)) === 0) ? 'selected="selected"' : '';
        $locale_options .= '<option value="'. $locale_desc['type'] .'" '. $selected .'>'. $locale_type .'</option>';
    }

    return $locale_options;
}


function game_select($default = '')
{
	global $eqdkp_root_path, $lang, $LOCALES, $DEFAULTS;
	
	if (empty($default))
	{
		$default = $DEFAULTS['default_game'];
	}
	
	if (!class_exists('Game_Installer'))
	{
		include($eqdkp_root_path . 'games/game_installer.php');
	}
	$gm = new Game_Installer();	

    $available_games = $gm->list_games();

    $game_options = '';
    foreach ($available_games as $game_id => $details)
    {
        $selected = ($game_id == $default) ? ' selected="selected"' : '';
        $game_options .= '<option value="' . $game_id . '"' . $selected .'>' . $details['name'] . '</option>';
    }

    return $game_options;
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
    }

    $result = $db->query($sql);

    $tables = array();

    while ($row = $db->fetch_record($result))
    {
        $tables[] = current($row);
    }

    $db->free_result($result);

    return $tables;
}

/**
* Used to test whether we are able to connect to the database the user has specified
* and identify any problems (eg there are already tables with the names we want to use
* @param    array    $dbms should be of the format of an element of the array returned by {@link get_available_dbms get_available_dbms()}
*                    necessary extensions should be loaded already
*/
function connect_check_db($error_connect, &$error, $dbms, $table_prefix, $dbhost, $dbuser, $dbpasswd, $dbname, $dbport, $prefix_may_exist = false, $load_dbal = true, $unicode_check = true)
{
    global $eqdkp_root_path, $config, $lang;


    if ($load_dbal)
    {
        // Include the DB layer
        include_once($eqdkp_root_path . 'includes/db/' . $dbms['DRIVER'] . '.php');
    }

    // Instantiate it and set return on error true
    // Note to self: If general dbal class made, and each subclass has prefix, add here.
    $sql_db = 'dbal_' . $dbms['DRIVER'];
    $db = new $sql_db();
    $db->error_die(false);

    // Check that we actually have a database name before going any further.....
    if ($dbms['DRIVER'] != 'sqlite' && $dbms['DRIVER'] != 'oracle' && $dbname === '')
    {
        $error[] = $lang['INST_ERR_DB_NO_NAME'];
        return false;
    }

    // Make sure we don't have a daft user who thinks having the SQLite database in the forum directory is a good idea
/*    if ($dbms['DRIVER'] == 'sqlite' && stripos(eqdkp_realpath($dbhost), eqdkp_realpath('../')) === 0)
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

            $prefix_length = 36;
        break;
    }

    if (strlen($table_prefix) > $prefix_length)
    {
        $error[] = sprintf($lang['INST_ERR_PREFIX_TOO_LONG'], $prefix_length);
        return false;
    }

    // Try and connect ...
    // NOTE: EQdkp's sql_connect function returns false if the connection was invalid.
    $connect_test = $db->sql_connect($dbhost, $dbname, $dbuser, $dbpasswd, false);
    if ($connect_test === false || is_array($connect_test))
    {
        $db_error = $db->error();
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
function config_set($config_name, $config_value='', $db = null, $config_table = false)
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
			if ( $config_table == false )
			{
				if ( defined('CONFIG_TABLE') )
				{
					$config_table = CONFIG_TABLE;
				}
				else
				{
					return false;
				}
			}

            $sql = 'UPDATE ' . $config_table . "
                    SET config_value='" . $db->escape($config_value) . "'
                    WHERE config_name='" . $config_name . "'";
            $db->query($sql);

            return true;
        }
    }

    return false;
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
function auto_redirect($page)
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
            'S_LEGEND'    => true,
            'LEGEND'    => $lang['INST_ERR'],
        ));

        $template->assign_block_vars('checks', array(
            'TITLE'        => basename($file) . ' [ ' . $line . ' ]',
            'RESULT'    => '<b style="color:red">' . $error . '</b>',
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
    echo '    <div id="page-header">';
    echo '    </div>';
    echo '    <div id="page-body">';
    echo '        <div id="acp">';
    echo '        <div class="panel">';
    echo '            <span class="corners-top"><span></span></span>';
    echo '            <div id="content">';
    echo '                <h1>' . $lang['INST_ERR_FATAL'] . '</h1>';
    echo '        <p>' . $lang['INST_ERR_FATAL'] . "</p>\n";
    echo '        <p>' . basename($file) . ' [ ' . $line . " ]</p>\n";
    echo '        <p><b>' . $error . "</b></p>\n";
    echo '            </div>';
    echo '            <span class="corners-bottom"><span></span></span>';
    echo '        </div>';
    echo '        </div>';
    echo '    </div>';
    echo '    <div id="page-footer">';
    echo '        Powered by phpBB &copy; 2000, 2002, 2005, 2007 <a href="http://www.phpbb.com/">phpBB Group</a>';
    echo '    </div>';
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
            'S_LEGEND'    => true,
            'LEGEND'    => $lang['INST_ERR_FATAL'],
        ));

        $template->assign_block_vars('checks', array(
            'TITLE'        => basename($file) . ' [ ' . $line . ' ]',
            'RESULT'    => '<b style="color:red">' . $error . '</b><br />&#187; SQL:' . $sql,
        ));

        return;
    }

    $template->set_filenames(array(
        'body' => 'install_error.html')
    );
    $this->page_header();
    $this->generate_navigation();

    $template->assign_vars(array(
        'MESSAGE_TITLE'        => $lang['INST_ERR_FATAL_DB'],
        'MESSAGE_TEXT'        => '<p>' . basename($file) . ' [ ' . $line . ' ]</p><p>SQL : ' . $sql . '</p><p><b>' . $error . '</b></p>',
    ));

    // Rollback if in transaction
    if ($db->transaction)
    {
//        $db->sql_transaction('rollback');
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
            $key_yes    = ($value)  ? ' checked="checked" id="' . $name . '"' : '';
            $key_no     = (!$value) ? ' checked="checked" id="' . $name . '"' : '';

            $tpl_type_cond = explode('_', $tpl_type[1]);
            $type_no    = ($tpl_type_cond[0] == 'disabled' || $tpl_type_cond[0] == 'enabled') ? false : true;

            $tpl_no     = '<label><input type="radio" name="' . $name . '" value="0"' . $key_no . ' class="radio" /> ' . (($type_no) ? $lang['NO'] : $lang['DISABLED']) . '</label>';
            $tpl_yes    = '<label><input type="radio" name="' . $name . '" value="1"' . $key_yes . ' class="radio" /> ' . (($type_no) ? $lang['YES'] : $lang['ENABLED']) . '</label>';

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