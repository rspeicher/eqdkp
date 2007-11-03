<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        functions.php
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

// -----------------------------------------
// Template helpers
// -----------------------------------------

/**
 * Keep a consistent page title across the entire application
 *
 * @param     string     $title            The dynamic part of the page title, appears before " - Guild Name DKP"
 * @return    string
 */
function page_title($title = '')
{
    global $eqdkp, $user;
    
    $retval = '';
    
    $section = ( defined('IN_ADMIN') ) ? $user->lang['admin_title_prefix'] : $user->lang['title_prefix'];
    $global_title = sprintf($section, $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']);
    
    $retval = ( $title != '' ) ? "{$title} - " : '';
    $retval .= $global_title;
    
    return sanitize($retval, TAG);
}

/**
 * Option Checked value method
 * Returns ' selected="selected"' for use in <option> tags if $condition is true
 */
function option_checked($condition)
{
    return ( $condition ) ? ' checked="checked"' : '';
}

/**
 * Option Selected value method
 * Returns ' checked="checked"' for use in checkbox/radio <input> tags if $condition is true
 */
function option_selected($condition)
{
    return ( $condition ) ? ' selected="selected"' : '';
}

/**
 * Returns an array of valid Style ID options for use in populating a <select> tag
 *
 * @param     mixed      $comparison       Used with {@link option_selected} to determine the selected row in the drop-down
 * @return    array
 */
function select_style($comparison)
{
    global $db, $eqdkp;
    
    $retval = array();
    
    $sql = "SELECT style_id, style_name
            FROM __styles
            ORDER BY `style_name`";
    $result = $db->query($sql);
    while ( $row = $db->fetch_record($result) )
    {
        $retval[] = array(
            'VALUE'    => $row['style_id'],
            'SELECTED' => option_selected(intval($comparison) == intval($row['style_id'])),
            'OPTION'   => $row['style_name']
        );
    }
    $db->free_result($result);
    
    return $retval;
}

/**
 * Returns an array of valid template folder names for use in populating a <select> tag
 *
 * @param     mixed      $comparison       Used with {@link option_selected} to determine the selected row in the drop-down
 * @return    array
 */
function select_template($comparison)
{
    global $eqdkp;
    
    $retval = array();
    if ( $dir = @opendir($eqdkp->root_path . 'templates/') )
    {
        while ( $file = @readdir($dir) )
        {
            if ( valid_folder("{$eqdkp->root_path}templates/{$file}") )
            {
                $retval[] = array(
                    'VALUE'    => $file,
                    'SELECTED' => option_selected(strtolower($comparison) == strtolower($file)),
                    'OPTION'   => $file
                );
            }
        }
    }
    
    return $retval;
}

/**
 * Returns an array of valid language folders for use in populating a <select> tag
 *
 * @param     mixed      $comparison       Used with {@link option_selected} to determine the selected row in the drop-down
 * @return    array
 */
function select_language($comparison)
{
    global $eqdkp;
    
    $retval = array();
    if ( $dir = @opendir($eqdkp->root_path . 'language/') )
    {
        while ( $file = @readdir($dir) )
        {
            if ( valid_folder("{$eqdkp->root_path}language/{$file}") )
            {
                $retval[] = array(
                    'VALUE'    => $file,
                    'SELECTED' => option_selected(strtolower($comparison) == strtolower($file)),
                    'OPTION'   => ucfirst($file)
                );
            }
        }
    }
    
    return $retval;
}

/**
 * Determines if a folder path is valid. Ignores .svn, CVS, cache, etc.
 *
 * @param     string     $path             Path to check
 * @return    boolean
 */
function valid_folder($path)
{
    $ignore = array('.', '..', '.svn', 'CVS', 'cache', 'install');
    
    if ( !is_file($path) && !is_link($path) && !in_array(basename($path), $ignore) )
    {
        return true;
    }
    
    return false;
}

define('ENT', 1); // Escape HTML entities
define('TAG', 2); // Strip HTML tags

/**
 * Translate qoute characters to their HTML entities, and strip HTML tags. Calls
 * stripslashes() if magic quotes are enabled.
 * 
 * @param     string     $input            Input to sanitize
 * @param     int        $options          ENT | TAG
 * @return    string
 */
function sanitize($input, $options = 3, $ignore = null)
{
    if ( !is_null($ignore) )
    {
        trigger_error('Third parameter to sanitize is deprecated!', E_USER_WARNING);
    }
    
    $input = ( $options & TAG ) ? strip_tags($input) : $input;
    $input = ( $options & ENT )  ? htmlspecialchars($input, ENT_QUOTES) : $input;
    $input = ( get_magic_quotes_gpc() ) ? stripslashes($input) : $input;
    
    return $input;
}

/**
 * Reverse the effects of htmlspecialchars()
 *
 * @param     string     $input            Input to reverse
 * @return    string
 */
function unsanitize($input)
{
    //return htmlspecialchars_decode($input, ENT_QUOTES); // PHP >= 5.1.0
    
    $retval = $input;
    $retval = str_replace('&amp;', '&', $retval);
    $retval = str_replace('&#039;', '\'', $retval);
    $retval = str_replace('&quot;', '"', $retval);
    $retval = str_replace('&lt;', '<', $retval);
    $retval = str_replace('&gt;', '>', $retval);
    
    return $retval;
}

/**
 * Create a CSS bar graph
 * 
 * @param     int        $width            Width of the bar
 * @param     string     $text             Text to show
 * @return    string
 */
function create_bar($width, $text = '')
{
    if ( strstr($width, '%') )
    {
        $width = intval(str_replace('%', '', $width));
        if ( $width > 0 )
        {
            $width = ( intval($width) <= 100 ) ? $width . '%' : '100%';
        }
    }
    
    $text = ( $text == '' ) ? $width . '%' : $text;
    
    return "<div class=\"graph\"><strong class=\"bar\" style=\"width: {$width}%;\">{$text}</strong></div>\n";
}

// -----------------------------------------
// Other stuff?
// -----------------------------------------

/**
 * Outputs a message with debugging info if needed and ends output.  
 * Clean replacement for die()
 *
 * @param     string     $text             Message text
 * @param     string     $title            Message title
 * @param     string     $file             File name
 * @param     int        $line             File line
 * @param     string     $sql              SQL code
 */
function message_die($text = '', $title = '', $file = '', $line = '', $sql = '')
{
    global $db, $tpl, $eqdkp, $user, $pm;
    global $gen_simple_header, $start_time, $eqdkp_root_path;
    
    $error_text = '';
    if ( (DEBUG == 1) && ($db->error_die) )
    {
        $sql_error = $db->error();

        $error_text = '';

        if ( $sql_error['message'] != '' )
        {
            $error_text .= '<b>SQL error:</b> ' . $sql_error['message'] . '<br />';
        }

        if ( $sql_error['code'] != '' )
        {
            $error_text .= '<b>SQL error code:</b> ' . $sql_error['code'] . '<br />';
        }

        if ( $sql != '' )
        {
            $error_text .= '<b>SQL:</b> ' . $sql . '<br />';
        }

        if ( ($line != '') && ($file != '') )
        {
            $error_text .= '<b>File:</b> ' . $file . '<br />';
            $error_text .= '<b>Line:</b> ' . $line . '<br />';
        }
    }

    // Add the debug info if we need it
    if ( (DEBUG == 1) && ($db->error_die) )
    {
        if ( $error_text != '' )
        {
            $text .= '<br /><br /><b>Debug Mode</b><br />' . $error_text;
        }
    }
    
    if ( !is_object($tpl) )
    {
        die($text);
    }
    
    $tpl->assign_vars(array(
        'MSG_TITLE'  => ( $title != '' ) ? $title : '&nbsp;',
        'MSG_TEXT'   => ( $text  != '' ) ? $text  : '&nbsp;')
    );
    
    if ( !defined('HEADER_INC') )
    {
        if ( (is_object($user)) && (is_object($eqdkp)) && (@is_array($eqdkp->config)) && (isset($user->lang['title_prefix'])) )
        {
            $page_title = sprintf($user->lang['title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']) . ': ' 
                . (( !empty($title) ) ? $title : ' Message');
        }
        else
        {
            $page_title = $user->lang['message_title'];
        }
        
        $eqdkp->set_vars(array(
            'gen_simple_header' => $gen_simple_header,
            'page_title'        => $page_title,
            'template_file'     => 'message.html')
        );
        
        $eqdkp->page_header();
    }
    $eqdkp->page_tail();
    exit;
}

/**
 * Returns the appropriate CSS class to use based on a number's range
 *
 * @param     string     $item             The number
 * @param     boolean    $percentage       Treat the number like a percentage?
 * @return    mixed                        CSS Class / false
 */
function color_item($item, $percentage = false)
{
    if ( !is_numeric($item) )
    {
        return false;
    }

    if ( !$percentage )
    {
        if ( $item < 0 )
        {
            $class = 'negative';
        }
        elseif ( $item > 0)
        {
            $class = 'positive';
        }
        else
        {
            $class = 'neutral';
        }
    }
    elseif ( $percentage )
    {
        if ( ($item >= 0) && ($item <= 34) )
        {
            $class = 'negative';
        }
        elseif ( ($item >= 35) && ($item <= 66) )
        {
            $class = 'neutral';
        }
        elseif ( ($item >= 67) && ($item <= 100) )
        {
            $class = 'positive';
        }
        else
        {
            $class = 'neutral';
        }
    }

    return $class;
}

/**
 * Switches the sorting order of a supplied array
 * The array is in the format [number][0/1] (0 = the default, 1 = the opposite)
 * Returns an array containing the code to use in an SQL query and the code to
 * use to pass the sort value through the URI.  URI is in the format
 * (number).(0/1)
 *
 * Also contains checks to make sure the first element is not larger than the
 * sort_order array and that the second selement is either 0 or 1
 *
 * @param     array      $sort_order       Sorting order array
 * @return    array
 */
function switch_order($sort_order)
{
    global $in;
    
    $uri_order = $in->get(URI_ORDER, 0.0);
    $uri_order = explode('.', $uri_order);
    $element1 = ( isset($uri_order[0]) ) ? $uri_order[0] : 0;
    $element2 = ( isset($uri_order[1]) ) ? $uri_order[1] : 0;

    $array_size = count($sort_order);
    if ( $element1 > $array_size - 1 )
    {
        $element1 = $array_size - 1;
    }
    if ( $element2 > 1 )
    {
        $element2 = 0;
    }

    for ( $i = 0; $i < $array_size; $i++ )
    {
        if ( $element1 == $i )
        {
            $uri_element2 = ( $element2 == 0 ) ? 1 : 0;
        }
        else
        {
            $uri_element2 = 0;
        }
        $current_order['uri'][$i] = $i . '.' . $uri_element2;
    }

    $current_order['uri']['current'] = $element1.'.'.$element2;
    $current_order['sql'] = $sort_order[$element1][$element2];

    return $current_order;
}

/**
 * Returns a string with a list of available pages
 *
 * @param     string     $base_url         The starting URL for each page link
 * @param     int        $num_items        The number of items we're paging through
 * @param     int        $per_page         How many items to display per page
 * @param     int        $start_item       Which number are we starting on
 * @param     string     $start_variable   In case you need to call your _GET var something other than 'start'
 * @return    string
 */
function generate_pagination($base_url, $num_items, $per_page, $start_item, $start_variable='start')
{
    global $user;

    $total_pages = ceil($num_items / $per_page);

    if ( ($total_pages == 1) || (!$num_items) )
    {
        return '';
    }

    $uri_symbol = ( strpos($base_url, '?') ) ? '&amp;' : '?';

    $on_page = floor($start_item / $per_page) + 1;

    //«»

    $pagination = '';
    $pagination = ( $on_page == 1 ) ? '<b>1</b>' : '<a href="'.$base_url . $uri_symbol . $start_variable.'='.( ($on_page - 2) * $per_page).'" title="'.$user->lang['previous_page'].'" class="copy">&lt;</a>&nbsp;&nbsp;<a href="'.$base_url.'" class="copy">1</a>';

    if ( $total_pages > 5 )
    {
        $start_count = min(max(1, $on_page - 6), $total_pages - 5);
        $end_count = max(min($total_pages, $on_page + 6), 5);

        $pagination .= ( $start_count > 1 ) ? ' ... ' : ' ';

        for ( $i = $start_count + 1; $i < $end_count; $i++ )
        {
            $pagination .= ($i == $on_page) ? '<b>'.$i.'</b> ' : '<a href="'.$base_url . $uri_symbol . $start_variable.'='.( ($i - 1) * $per_page).
                           '" title="'.$user->lang['page'].' '.$i.'" class="copy">'.$i.'</a>';
            if ( $i < $end_count - 1 )
            {
                $pagination .= ' ';
            }
        }

        $pagination .= ($end_count < $total_pages ) ? ' ... ' : ' ';
    }
    else
    {
        $pagination .= ' ';

        for ( $i = 2; $i < $total_pages; $i++ )
        {
            $pagination .= ($i == $on_page) ? '<b>'.$i.'</b> ' : '<a href="'.$base_url . $uri_symbol . $start_variable.'='.( ($i - 1) * $per_page).
                           '" title="'.$user->lang['page'].' '.$i.'" class="copy">'.$i.'</a> ';
            if ( $i < $total_pages )
            {
                $pagination .= ' ';
            }
        }
    }

    $pagination .= ( $on_page == $total_pages ) ? '<b>'.$total_pages.'</b>' : '<a href="'.$base_url . $uri_symbol . $start_variable.'='.(($total_pages - 1) * $per_page) . '" class="copy">'.$total_pages.'</a>&nbsp;&nbsp;<a href="'.$base_url.'&amp;'.$start_variable.'='.($on_page * $per_page).
                   '" title="'.$user->lang['next_page'].'" class="copy">&gt;</a>';

    return $pagination;
}

/**
 * Redirects the user to another page and exits cleanly
 *
 * @param     string     $url          URL to redirect to
 * @param     bool       $return       Whether to return the generated redirect url (true) or just redirect to the page (false)
 * @return    mixed                    null, else the parsed redirect url if return is true.
 */
function redirect($url, $return = false)
{
    global $db, $eqdkp, $user;

    $protocol = 'http://';
    $server   = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($eqdkp->config['server_name']));
    $port     = ($eqdkp->config['server_port'] != 80) ? trim($eqdkp->config['server_port']) : '';
    $script   = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($eqdkp->config['server_path']));

    $url      = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($url));
    $url      = str_replace('&amp;', '&', $url);

    if( $return )
    {
        return $url;
    }

    $location = $protocol . $server . $port . '/' . (!empty($script) ? $script . '/' : '') . $url;
    
    if ( @preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')) )
    {
        header('Refresh: 0; URL=' . $location);
        
        echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
        echo '<html>';
        echo '<head>';
        echo '<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">';
        echo '<meta http-equiv="refresh" content="0; url=' . str_replace('&', '&amp;', $location) .'">';
        echo '<title>Redirect</title>';
        echo '</head>';
        echo '<body>';
        echo '<div align="center">If your browser does not support meta redirection, please click <a href="' . str_replace('&', '&amp;', $location) . '">here</a> to be redirected</div>';
        echo '</body>';
        echo '</html>';
        
        exit;
    }
    
    if ( isset($db) )
    {
        $db->close_db();
    }

    header('Location: ' . $location);
    exit;
}


/**
* Meta refresh assignment
*/
function meta_refresh($time, $url)
{
    global $tpl;

//    $url = redirect($url, true);

    // For XHTML compatibility we change back & to &amp;
    $tpl->assign_vars(array(
        'META' => '<meta http-equiv="refresh" content="' . $time . ';url=' . str_replace('&', '&amp;', $url) . '" />'
    ));
}


// -----------------------------------------
// Deprecated
// -----------------------------------------

/**
 * Checks if a POST field value exists;
 * If it does, we use that one, otherwise we use the optional database field value,
 * or return a null string if $db_row contains no data
 *
 * @param     string     $post_field       POST field name
 * @param     array      $db_row           Array of DB values
 * @param     string     $db_field         DB field name
 * @return    string
 */
function post_or_db($post_field, $db_row = array(), $db_field = '')
{
    global $in;
    
    trigger_error("post_or_db is deprecated, use Input::get", E_USER_NOTICE);
    
    if ( @sizeof($db_row) > 0 )
    {
        if ( $db_field == '' )
        {
            $db_field = $post_field;
        }

        $db_value = $db_row[$db_field];
    }
    else
    {
        $db_value = '';
    }

    return ( $in->get($post_field, '') != '' ) ? $in->get($post_field) : $db_value;
}

function sanitize_tags($data)
{
    trigger_error("sanitize_tags is deprecated, use sanitize()", E_USER_NOTICE);
    if ( is_array($data) )
    {
        foreach ( $data as $k => $v )
        {
            $data[$k] = sanitize_tags($v);
        }
    }
    else
    {
        $data = str_replace('<', '&lt;', $data);
        $data = str_replace('>', '&gt;', $data);
    }

    return $data;
}

function undo_sanitize_tags($data)
{
    trigger_error("undo_sanitize_tags is deprecated, use unsanitize()", E_USER_NOTICE);
    if ( is_array($data) )
    {
        foreach ( $data as $k => $v )
        {
            $data[$k] = undo_sanitize_tags($v);
        }
    }
    else
    {
        $data = str_replace('&lt;', '<', $data);
        $data = str_replace('&gt;', '>', $data);
    }

    return $data;
}

/**
 * Applies htmlspecialchars to an array of data
 * 
 * @deprec sanitize_tags
 * @param $data
 * @return array
 */
function htmlspecialchars_array($data)
{
    trigger_error("htmlspecialchars_arary is deprecated", E_USER_NOTICE);
    if ( is_array($data) )
    {
        foreach ( $data as $k => $v )
        {
            $data[$k] = ( is_array($v) ) ? htmlspecialchars_array($v) : htmlspecialchars($v);
        }
    }
    
    return $data;
}

function htmlspecialchars_remove($data)
{
    trigger_error("htmlspecialchars_remove is deprecated, use sanitize()", E_USER_NOTICE);
    $find    = array('#&amp;#', '#&quot;#', '#&\#039;#', '#&lt;#', '#&gt;#');
    $replace = array('&', '"', '\'', '<', '>');
    
    $data = preg_replace($find, $replace, $data);
    
    return $data;
}