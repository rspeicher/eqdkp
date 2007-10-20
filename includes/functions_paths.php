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

function default_path($path)
{
    global $SID;
    
    return $path . $SID;
}

## ############################################################################
## Member Paths
## ############################################################################

function member_path($member = null)
{
    if ( !is_null($member) )
    {
        return default_path('viewmember.php') . '&' . URI_NAME . '=' . urlencode($member);
    }
    
    return default_path('listmembers.php');
}