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
function path_default($path, $admin = null)
{
    global $eqdkp_root_path;
    
    if ( !is_null($admin) )
    {
        trigger_error("Second parameter to path_default() is deprecated.", E_USER_NOTICE);
    }
    
    $path = $eqdkp_root_path . $path;
    
    return $path . '?s=';
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
## Adjustment Paths
## ############################################################################

/**
 * Return the appropriate path to a list of adjustments
 *
 * @return string
 */
function adjustment_path()
{
    return path_default('admin/listadj.php');
}

/**
 * Return the appropriate path to a list of individual adjustments
 *
 * @return string
 */
function iadjustment_path()
{
    return path_default('admin/listadj.php') . path_params(URI_PAGE, 'individual');
}

/**
 * Return the appropriate path to add or edit an adjustment
 *
 * @param int $id If present, returns the path to edit a specific adjustment
 * @return string
 */
function edit_adjustment_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('admin/addadj.php') . path_params(URI_ADJUSTMENT, $id);
    }
    
    return path_default('admin/addadj.php');
}

/**
 * Return the appropriate path to add or edit an individual adjustment
 *
 * @param int $id If present, returns the path to edit a specific individual adjustment
 * @return string
 */
function edit_iadjustment_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('admin/addiadj.php') . path_params(URI_ADJUSTMENT, $id);
    }
    
    return path_default('admin/addiadj.php');
}

## ############################################################################
## Event Paths
## ############################################################################

/**
 * Return the appropriate path to add or edit an event
 *
 * @param int $id If present, returns the path to edit a specific event
 * @return string
 */
function edit_event_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('admin/addevent.php') . path_params(URI_EVENT, $id);
    }
    
    return path_default('admin/addevent.php');
}

/**
 * Return the appropriate path to an event or list of events
 *
 * @param int $id If present, returns the path to a specific event
 * @return string
 */
function event_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('viewevent.php') . path_params(URI_EVENT, $id);
    }
    
    $admin = ( defined('IN_ADMIN') ) ? 'admin/' : '';
    return path_default("{$admin}listevents.php");
}

## ############################################################################
## Item Paths
## ############################################################################

/**
 * Return the appropriate path to add or edit an item
 *
 * @param int $id If present, returns the path to edit a specific item
 * @return string
 */
function edit_item_path($id = null)
{
    if ( !is_null($id) && $id > 0 )
    {
        $id = intval($id);
        return path_default('admin/additem.php') . path_params(URI_ITEM, $id);
    }
    
    return path_default('admin/additem.php');
}

/**
 * Return the appropriate path to an item or list of items.
 *
 * @param int $id If present, returns the path to a specific item
 * @return string
 */
function item_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('viewitem.php') . path_params(URI_ITEM, $id);
    }
    
    $admin = ( defined('IN_ADMIN') ) ? 'admin/' : '';
    return path_default("{$admin}listitems.php");
}

## ############################################################################
## Log Paths
## ############################################################################

/**
 * Return the appropriate path to a log or list of logs
 *
 * @param int $id If present, returns the path to a specific log
 * @return string
 */
function log_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('admin/logs.php') . path_params(URI_LOG, $id);
    }
    
    return path_default('admin/logs.php');
}

## ############################################################################
## Member Paths
## ############################################################################

/**
 * Return the appropriate path to add or edit a member
 *
 * @param string $id If present, returns the path to edit a specific member
 * @return string
 */
function edit_member_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = sanitize($id, ENT);
        return path_default('admin/manage_members.php') . path_params(array('mode' => 'addmember', URI_NAME => $id));
    }
    
    return path_default('admin/manage_members.php') . path_params('mode', 'addmember');
}

/**
 * Return the appropriate path to a member or list of members
 *
 * @param string $id If present, returns the path to a specific member
 * @return string
 */
function member_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = sanitize($id, ENT);
        return path_default('viewmember.php') . path_params(URI_NAME, $id);
    }
    
    return path_default('listmembers.php');
}

## ############################################################################
## News Paths
## ############################################################################

/**
 * Return the appropriate path to add or edit a news entry
 *
 * @param int $id If present, returns the path to a specific news entry
 * @return string
 */
function edit_news_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('admin/addnews.php') . path_params(URI_NEWS, $id);
    }
    
    return path_default('admin/addnews.php');
}

/**
 * Return the appropriate path to a list of news items
 *
 * @param bool $admin If true, returns the path to a list of news entries for editing
 * @return string
 */
function news_path($admin = false)
{
    if ( $admin )
    {
        return path_default('admin/listnews.php');
    }
    
    return path_default('viewnews.php');
}

## ############################################################################
## Raid Paths
## ############################################################################

/**
 * Return the appropriate path to add or edit a raid
 *
 * @param int $id If present, returns the path to a specific raid
 * @return string
 */
function edit_raid_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('admin/addraid.php') . path_params(URI_RAID, $id);
    }
    
    return path_default('admin/addraid.php');
}

/**
 * Return the appropriate path to a raid or list of raids
 *
 * @param int $id If present, returns the path to a specific raid
 * @return string
 */
function raid_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('viewraid.php') . path_params(URI_RAID, $id);
    }
    
    $admin = ( defined('IN_ADMIN') ) ? 'admin/' : '';
    return path_default("{$admin}listraids.php");
}

## ############################################################################
## User Paths
## ############################################################################

/**
 * Return the appropriate path to add or edit a user
 *
 * @param string $id If present, returns the path to edit a specific user
 * @return string
 */
function edit_user_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = sanitize($id, ENT);
        return path_default('admin/manage_users.php') . path_params(URI_NAME, $id);
    }
    
    return path_default('admin/manage_users.php');
}