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

/**
* Database Abstraction Layer
* @package dbal
*/
class dbal
{
    var $link_id     = 0;                   // Connection link ID       @var link_id
    var $query_id    = 0;                   // Query ID                 @var query_id
    var $record      = array();             // Record                   @var record
    var $record_set  = array();             // Record set               @var record_set
    var $query_count = 0;                   // Query count              @var query_count
    var $queries     = array();             // Queries                  @var queries
    var $error_die   = true;                // Die on errors?           @var error_die

    /**
    * Current sql layer
    */
    var $sql_layer = '';

    /**
    * Wildcards for matching any (%) or exactly one (_) character within LIKE expressions
    */
    var $any_char;
    var $one_char;


    /**
    * Constructor
    */
    function dbal()
    {
        $this->query_count = 0;

        // Fill default sql layer based on the class being called.
        // This can be changed by the specified layer itself later if needed.
        $this->sql_layer = substr(get_class($this), 5);

        // Do not change this please! This variable is used to easy the use of it - and is hardcoded.
        $this->any_char = chr(0) . '%';
        $this->one_char = chr(0) . '_';
    }

    /**
    * DBAL garbage collection, close sql connection
    */
    function sql_close()
    {
        return $this->_sql_close();
    }

    /**
    * Remove quote escape
    * 
    * @param $string    The string to escape, or the implode() delimiter if $array is set
    * @param $array     An array to pass to _implode(), escaping its values
    * @return string
    */
    function escape($string, $array = null)
    {
        if ( is_array($array) )
        {
            $string = $this->_implode($string, $array);
        }
        else
        {
            $string = mysql_real_escape_string($string);
        }
        
        return $string;
    }
    
    function _implode($delim, $array)
    {
        if ( !is_array($array) || count($array) == 0 )
        {
            return '';
        }
        
        foreach ( $array as $k => $v )
        {
            $array[$k] = $this->escape($v);
        }
        
        return implode($delim, $array);
    }

    /**
    * Set the error_die var
    * 
    * @param $setting
    */
    function error_die($setting = true)
    {
        $this->error_die = $setting;
    }

}

/**
* This variable holds the class name to use later
*/
$sql_db = 'dbal_' . $dbms;
