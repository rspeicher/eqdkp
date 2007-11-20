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
    
    if ( !defined('IN_ADMIN') && $admin === true )
    {
        // Path is an admin page but we're not already in the admin folder, prefix it to the path
        $path = 'admin/' . $path;
    }
    elseif ( defined('IN_ADMIN') && $admin === false )
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
## Adjustment Paths
## ############################################################################

function adjustment_path()
{
    return path_default('listadj.php', true);
}

function iadjustment_path()
{
    return path_default('listadj.php', true) . path_params(URI_PAGE, 'individual');
}

function edit_adjustment_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('addadj.php', true) . path_params(URI_ADJUSTMENT, $id);
    }
    
    return path_default('addadj.php', true);
}

function edit_iadjustment_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('addiadj.php', true) . path_params(URI_ADJUSTMENT, $id);
    }
    
    return path_default('addiadj.php', true);
}

## ############################################################################
## Event Paths
## ############################################################################

function edit_event_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('addevent.php', true) . path_params(URI_EVENT, $id);
    }
    
    return path_default('addevent.php', true);
}

/**
 * Return the appropriate path to an event or list of events.
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
    
    return path_default('listevents.php', defined('IN_ADMIN'));
}

## ############################################################################
## Item Paths
## ############################################################################

function edit_item_path($id = null)
{
    if ( !is_null($id) && $id > 0 )
    {
        $id = intval($id);
        return path_default('additem.php', true) . path_params(URI_ITEM, $id);
    }
    
    return path_default('additem.php', true);
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
    
    return path_default('listitems.php', defined('IN_ADMIN'));
}

## ############################################################################
## Log Paths
## ############################################################################

function log_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('logs.php', true) . path_params(URI_LOG, $id);
    }
    
    return path_default('logs.php', true);
}

## ############################################################################
## Member Paths
## ############################################################################

/**
 * Return the appropriate path to add or edit a member.
 *
 * @param string $id If present, returns the path to edit a specific member
 * @return string
 */
function edit_member_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = sanitize($id, ENT);
        return path_default('manage_members.php', true) . path_params(array('mode' => 'addmember', URI_NAME => $id));
    }
    
    return path_default('manage_members.php', true) . path_params('mode', 'addmember');
}

/**
 * Return the appropriate path to a member or list of members.
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

function edit_news_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('addnews.php', true) . path_params(URI_NEWS, $id);
    }
    
    return path_default('addnews.php', true);
}

/**
 * Return the appropriate path to a list of news items.
 *
 * @param bool $admin If true, returns the path to a list of news entries for editing
 * @return string
 */
function news_path($admin = false)
{
    if ( $admin )
    {
        return path_default('listnews.php', true);
    }
    
    return path_default('viewnews.php', false);
}

## ############################################################################
## Raid Paths
## ############################################################################

function edit_raid_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = intval($id);
        return path_default('addraid.php', true) . path_params(URI_RAID, $id);
    }
    
    return path_default('addraid.php', true);
}

/**
 * Return the appropriate path to a raid or list of raids.
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
    
    return path_default('listraids.php', defined('IN_ADMIN'));
}

## ############################################################################
## User Paths
## ############################################################################

/**
 * Return the appropriate path to add or edit a user.
 *
 * @param string $id If present, returns the path to edit a specific user
 * @return string
 */
function edit_user_path($id = null)
{
    if ( !is_null($id) )
    {
        $id = sanitize($id, ENT);
        return path_default('manage_users.php', true) . path_params(URI_NAME, $id);
    }
    
    return path_default('manage_users.php', true);
}