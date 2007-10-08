<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * functions.php
 * begin: Tue December 17 2002
 *
 * $Id: functions.php 51 2007-06-24 09:05:13Z tsigo $
 *
 ******************************/

if ( !defined('EQDKP_INC') )
{
     die('Do not access this file directly.');
}

/**
 * Translate qoute characters to their HTML entities, and strip HTML tags.
 */
function sanitize($input)
{
    return htmlspecialchars(strip_tags($input), ENT_QUOTES);
}

function unsanitize($input)
{
    return htmlspecialchars_decode($input, ENT_QUOTES);
}

/**
* Checks if a POST field value exists;
* If it does, we use that one, otherwise we use the optional database field value,
* or return a null string if $db_row contains no data
*
* @param    string  $post_field POST field name
* @param    array   $db_row     Array of DB values
* @param    string  $db_field   DB field name
* @return   string
*/
function post_or_db($post_field, $db_row = array(), $db_field = '')
{
    global $in;
    
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

    // NOTE: post_or_db doesn't supply a default value to Input::get, and may be less secure than other uses
    return ( $in->get($post_field, '') != '' ) ? $in->get($post_field) : $db_value;
}

/**
* Outputs a message with debugging info if needed
* and ends output.  Clean replacement for die()
*
* @param $text Message text
* @param $title Message title
* @param $file File name
* @param $line File line
* @param $sql SQL code
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
* Returns the appropriate CSS class to use based on
* a number's range
*
* @param $item The number
* @param $percentage Treat the number like a percentage?
* @return mixed CSS Class / false
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

/*
* Switches the sorting order of a supplied array
* The array is in the format [number][0/1] (0 = the default, 1 = the opposite)
* Returns an array containing the code to use in an SQL query and the code to
* use to pass the sort value through the URI.  URI is in the format
* (number).(0/1)
*
* Also contains checks to make sure the first element is not larger than the
* sort_order array and that the second selement is either 0 or 1
*
* @param $sort_order Sorting order array
* @return array SQL/URI information
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
* @param $base_url The starting URL for each page link
* @param $num_items The number of items we're paging through
* @param $per_page How many items to display per page
* @param $start_item Which number are we starting on
* @param $start_variable In case you need to call your _GET var something other
*        than 'start'
* @return string Pages
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
* @param $url URL to redirect to
*/
function redirect($url)
{
    global $db, $eqdkp, $user;

    $protocol = 'http://';
    $server = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($eqdkp->config['server_name']));
    $port = ( $eqdkp->config['server_port'] != 80 ) ? ':' . trim($eqdkp->config['server_port']) . '/' : '/';
    $script = preg_replace('/^\/?(.*?)\/?$/', '\1', trim($eqdkp->config['server_path']));
    $url = (( $script == '' ) ? '' : '/') . preg_replace('/^\/?(.*?)\/?$/', '\1', trim($url));
    
    $location = $protocol . $server . $port . $script . $url;
    
    if ( @preg_match('/Microsoft|WebSTAR|Xitami/', getenv('SERVER_SOFTWARE')) )
    {
        header('Refresh: 0; URL=' . $location);
        echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><meta http-equiv="refresh" content="0; url=' . $location .'"><title>Redirect</title></head>';
        echo '<body><div align="center">If your browser does not support meta redirection, please click <a href="' . $location . '">here</a> to be redirected</div></body></html>';
        exit;
    }
 
    /*
    if ( sizeof(@file($location)) == 1 )
    {
        message_die('Redirect error: ' . $location . ' does not exist.');
    }
    */
    
    if ( isset($db) )
    {
        $db->close_db();
    }

    header('Location: ' . $location);
    exit;
}

// TODO: This method seems out of place here, and may be more comfortable in EQdkp_Admin
/**
* Outputs a message asking the user if they're sure they want to delete something
*
* @param $confirm_text Confirm message
* @param $uri_parameter URI_RAID, URI_NAME, etc.
* @param $parameter_value Value of the parameter
* @param $action Form action
*/
function confirm_delete($confirm_text, $uri_parameter, $parameter_value, $action = '')
{
    global $db, $tpl, $eqdkp, $user, $pm;
    global $gen_simple_header, $eqdkp_root_path;

    if ( !defined('HEADER_INC') )
    {
        $eqdkp->set_vars(array(
            'page_title' => sprintf($user->lang['admin_title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']),
            'gen_simple_header' => $gen_simple_header,
            'template_file' => 'admin/confirm_delete.html')
        );
        
        $eqdkp->page_header();
    }

    $tpl->assign_vars(array(
        'F_CONFIRM_DELETE_ACTION' => ( !empty($action) ) ? $action : $_SERVER['PHP_SELF'],

        'URI_PARAMETER' => $uri_parameter,
        'PARAMETER_VALUE' => $parameter_value,

        'L_DELETE_CONFIRMATION' => $user->lang['delete_confirmation'],
        'L_CONFIRM_TEXT' => $confirm_text,
        'L_YES' => $user->lang['yes'],
        'L_NO' => $user->lang['no'])
    );

    $eqdkp->page_tail();

    exit;
}

/*
function stripmultslashes($string)
{
    $string = preg_replace("#(\\\){1,}(\"|\&quot;)#", '"', $string);
    $string = preg_replace("#(\\\){1,}(\'|\&\#039)#", "'", $string);
    
    return $string;
}
*/

// TODO: To be replaced by sanitize() and unsanitize()
function sanitize_tags($data)
{
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

// TODO: To be replaced by sanitize() and unsanitize()
function undo_sanitize_tags($data)
{
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

// TODO: Remove this once we remove the few calls to it.
/**
* Applies htmlspecialchars to an array of data
* 
* @deprec sanitize_tags
* @param $data
* @return array
*/
function htmlspecialchars_array($data)
{
    if ( is_array($data) )
    {
        foreach ( $data as $k => $v )
        {
            $data[$k] = ( is_array($v) ) ? htmlspecialchars_array($v) : htmlspecialchars($v);
        }
    }
    
    return $data;
}

// TODO: Remove this once we remove the few calls to it.
function htmlspecialchars_remove($data)
{
    $find    = array('#&amp;#', '#&quot;#', '#&\#039;#', '#&lt;#', '#&gt;#');
    $replace = array('&', '"', '\'', '<', '>');
    
    $data = preg_replace($find, $replace, $data);
    
    return $data;
}

/**
* Create a bar graph
* 
* @param $width     int     Width of the bar
* @param $show_text string  Text to show
* @return string Bar HTML
*/
function create_bar($width, $show_text = '')
{
    if ( strstr($width, '%') )
    {
        $width = intval(str_replace('%', '', $width));
        if ( $width > 0 )
        {
            $width = ( intval($width) <= 100 ) ? $width . '%' : '100%';
        }
    }
    
    $show_text = ( $show_text == '' ) ? $width . '%' : $show_text;
    
    return "<div class=\"graph\"><strong class=\"bar\" style=\"width: {$width}%;\">{$show_text}</strong></div>\n";
}
?>
