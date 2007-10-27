<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        functions_paths.php
 * Began:       Sat Oct 20 2007
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

// URI Parameters
define('URI_ADJUSTMENT', 'a');
define('URI_EVENT',      'e');
define('URI_ITEM',       'i');
define('URI_LOG',        'l');
define('URI_NAME',       'name');
define('URI_NEWS',       'n');
define('URI_ORDER',      'o');
define('URI_PAGE',       'p');
define('URI_RAID',       'r');
define('URI_SESSION',    's');

/**
 * Given a filename, returns its path with the Session ID appended
 *
 * @param string $path Path
 * @param bool $admin Path is located in the admin folder
 * @return string
 */
function path_default($path, $admin = false)
{
    global $eqdkp_root_path, $SID;
    
    if ( !defined('IN_ADMIN') && $admin == true )
    {
        // Path is an admin page but we're not already in the admin folder, prefix it to the path
        $path = 'admin/' . $path;
    }
    elseif ( defined('IN_ADMIN') && $admin == false )
    {
        // We're in the admin folder but linking to a non-admin path, prefix the root traversal to the path
        $path = $eqdkp_root_path . $path;
    }
    
    return $path . $SID;
}

/**
 * Join parameter values to their keys for use in URL paths
 *
 * @param string|array $param_name Name, or an array of name => value pairs
 * @param string $param_value Value
 * @return string
 */
function path_params($param_name, $param_value = '')
{
    $retval = '';
    
    if ( is_array($param_name) )
    {
        foreach ( $param_name as $key => $val )
        {
            $retval .= "&amp;{$key}=" . urlencode($val);
        }
    }
    else
    {
        $retval = "&amp;{$param_name}=" . urlencode($param_value);
    }
    
    return $retval;
}

## ############################################################################
## Event Paths
## ############################################################################

function event_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('viewevent.php') . path_params(URI_EVENT, $id);
    }
    
    return path_default('listevents.php');
}

## ############################################################################
## Item Paths
## ############################################################################

function item_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('viewitem.php') . path_params(URI_ITEM, $id);
    }
    
    return path_default('listitems.php');
}

## ############################################################################
## Member Paths
## ############################################################################

function member_path($id = null)
{
    if ( !is_null($id) )
    {
        return path_default('viewmember.php') . path_params(URI_NAME, $id);
    }
    
    return path_default('listmembers.php');
}

function edit_member_path($id = null)
{
    if ( !is_null($id) )
    {
        return path_default('manage_members.php', true) . path_params(array('mode' => 'addmember', URI_NAME => $id));
    }
    
    return path_default('manage_members.php', true) . path_params('mode', 'addmember');
}

## ############################################################################
## News Paths
## ############################################################################

function news_path()
{
    return path_default('viewnews.php');
}

## ############################################################################
## Raid Paths
## ############################################################################

function raid_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('viewraid.php') . path_params(URI_RAID, $id);
    }
    
    return path_default('listraids.php');
}