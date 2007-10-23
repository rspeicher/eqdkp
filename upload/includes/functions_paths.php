<?php
if ( !defined('EQDKP_INC') )
{
     die('Do not access this file directly.');
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

function default_path($path, $admin = false)
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

## ############################################################################
## Member Paths
## ############################################################################

// TODO: These methods return paths with unescaped '&' characters, which invalidates our XHTML
// A move to a template engine like Smarty would let the template files themselves dictate which paths need to be escaped

function member_path($member = null)
{
    if ( !is_null($member) )
    {
        return default_path('viewmember.php') . '&' . URI_NAME . '=' . urlencode($member);
    }
    
    return default_path('listmembers.php');
}

function edit_member_path($member = null)
{
    if ( !is_null($member) )
    {
        return default_path('manage_members.php', true) . '&mode=addmember&' . URI_NAME . '=' . urlencode($member);
    }
    
    return default_path('manage_members.php', true) . '&mode=addmember';
}