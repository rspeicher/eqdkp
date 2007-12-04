<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        dbal.php
 * Began:       Tue Dec 17 2002
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     db
 * @version     $Rev$
 */


if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

/**
 * Database Abstraction Layer
 * 
 * @abstract
 * @package dbal
 */
class dbal
{
    var $link_id     = 0;                   // @var    int        $link_id         connection link ID for a database connection resource
    var $query_id    = 0;                   // @var    int        $query_id        query ID
    var $record      = array();             // @var    array      $record          Record
    var $record_set  = array();             // @var    array      $record_set      Record set
    var $queries     = array();             // @var    array      $queries         the result of all queries run
    var $error_die   = true;                // @var    bool       $error_die       die on errors (true) or allow the script to run (false)?

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
        // Fill default sql layer based on the class being called.
        // This can be changed by the specified layer itself later if needed.
        $this->sql_layer = substr(get_class($this), 5);

        // Do not change this please! This variable is used to easy the use of it - and is hardcoded.
        $this->any_char = chr(0) . '%';
        $this->one_char = chr(0) . '_';
    }

    /**
     * Determine whether execution should halt on an error (true) or continue (false)
     */
    function error_die($setting = true)
    {
        $this->error_die = $setting;
    }

    /**
     * DBAL garbage collection, close sql connection
     */
    function sql_close()
    {
        return $this->_sql_close();
    }

    /**
     * Build query
     * Ikonboard -> phpBB -> EQdkp
     * 
     * @param     string     $query        Type of query to build, either INSERT or UPDATE
     * @param     array      $array        Array of field => value pairs
     * @return    string                   A SQL string fragment
     */
    function sql_build_query($query, $array = false)
    {
        if ( !(is_array($array) && count($array) > 0) )
        {
            return false;
        }
        
        $fields = array();
        $values = array();
        
        switch ($query)
        {
            case 'REPLACE':
                // Fall through
            case 'INSERT':
                // Returns a string in the form: (<field1>, <field2> ...) VALUES ('<value1>', <(int)value2>, ...)
                foreach ( $array as $field => $value )
                {
                    // Hack to prevent assigning $array directly from a fetch_record call
                    // injecting number-based indices into the built query
                    if ( is_numeric($field) )
                    {
                        continue;
                    }
                    
                    $fields[] = $field;
                    
                    if ( is_null($value) )
                    {
                        $values[] = 'NULL';
                    }
                    elseif ( is_string($value) )
                    {
                        $values[] = "'" . $this->sql_escape($value) . "'";
                    }
                    else
                    {
                        $values[] = "'" . (( is_bool($value) ) ? intval($value) : $value) . "'";
                    }
                }
            
                $query = ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
            
            break;

            case 'UPDATE':
                // Returns a string in the form: <field1> = '<value1>', <field2> = <(int)value2>, ...
                foreach ( $array as $field => $value )
                {
                    // Hack to prevent assigning $array directly from a fetch_record call
                    // injecting number-based indices into the built query
                    if ( is_numeric($field) )
                    {
                        continue;
                    }
                    
                    if ( is_null($value) )
                    {
                        $values[] = "$field = NULL";
                    }
                    elseif ( is_string($value) )
                    {
                        $values[] = "$field = '" . $this->sql_escape($value) . "'";
                    }
                    else
                    {
                        $values[] = ( is_bool($value) ) ? "$field = '" . intval($value) . "'" : "{$field} = '{$value}'";
                    }
                }
                
                $query = implode(', ', $values);
            break;
            
            default:
                return false;
            break;
        }
        
        return $query;
    }


    /**
     * display sql error page
     */
    function sql_error($sql = '')
    {
        $error = $this->_sql_error();
        
        if ($this->error_die)
        {
            $message  = 'SQL ERROR<br /><br />';
            $message .= 'Query: '   . (($sql) ? $sql : 'null') . '<br />';
            $message .= 'Message: ' . $error['message'] . '<br />';
            $message .= 'Code: '    . $error['code'] . '<br />';

            die($message);
        }
        
        return $error;
    }


    /**
     * Implode an array of strings with a given delimiter after calling {@link escape} on each element
     *
     * @param     string     $delim        the delimiter to call implode() with
     * @param     array      $array        Array of strings to escape and join together
     * @return    array
     * @access    private
     */
    function _implode($delim, $array)
    {
        if ( !is_array($array) || count($array) == 0 )
        {
            return '';
        }
        
        foreach ( $array as $k => $v )
        {
            $array[$k] = $this->sql_escape($v);
        }
        
        return implode($delim, $array);
    }


/*
 * Deprecated Methods.
 *
 * These methods will disappear in a few versions' time. Please ensure your code uses the new method names!
 */

    // sql_build_query
    function build_query($query, $array = false)
    {
        return $this->sql_build_query($query, $array);
    }
}

// This variable holds the class name to use later
$sql_db = 'dbal_' . $dbms;