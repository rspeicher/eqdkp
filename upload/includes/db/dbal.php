<?php
/****************************** 
* EQdkp * Copyright 2002-2003 
* Licensed under the GNU GPL.  See COPYING for full terms. 
* ------------------ 
* dbal.php 
* begin: Tue December 17 2002 
*  
* $Id: dbal.php 46 2007-06-19 07:29:11Z tsigo $ 
* 
 ******************************/

if ( !defined('EQDKP_INC') )
{
     die('Do not access this file directly.');
}

switch ( $dbtype )
{
    case 'mysql':
        $dbms = 'mysql';
        break;
    default:
        $dbms = 'mysql';
        break;
}

/**
* This variable holds the class name to use later
*/
$sql_db = 'dbal_' . $dbms;
