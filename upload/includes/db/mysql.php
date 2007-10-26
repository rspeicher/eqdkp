<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * mysql.php
 * begin: Tue December 17 2002
 * 
 * $Id: mysql.php 46 2007-06-19 07:29:11Z tsigo $
 * 
 ******************************/

if ( !defined('EQDKP_INC') )
{
     die('Do not access this file directly.');
}
 
/**
* SQL_DB class, MySQL version
* Abstracts MySQL database functions
*/
include_once($eqdkp_root_path . 'includes/db/dbal.php');

define('DBTYPE', 'mysql');

class dbal_mysql extends dbal
{
	var $mysql_version;

/*
    function dbal_mysql()
    {
        $this->dbal();
    }
*/
    
    /**
     * Connects to a MySQL database
     * 
     * @param string $dbhost Database server
     * @param string $dbname Database name
     * @param string $dbuser Database username
     * @param string $dbpass Database password
     * @param bool $pconnect Use persistent connection
     * @return resource|bool Link ID / false
     */
    function sql_connect($dbhost, $dbname, $dbuser, $dbpass='', $pconnect = false)
    {
        $this->pconnect = $pconnect;
        $this->dbhost = $dbhost;
        $this->dbname = $dbname;
        $this->dbuser = $dbuser;
        
		// FIXME: I don't think it should matter whether the password is empty or not; it has no bearing on how mysql_xconnect functions
        if ( $this->pconnect )
        {
            $this->link_id = (empty($dbpass)) ? @mysql_pconnect($this->dbhost, $this->dbuser) : @mysql_pconnect($this->dbhost, $this->dbuser, $dbpass);
        }
        else
        {
            $this->link_id = (empty($dbpass)) ? @mysql_connect($this->dbhost, $this->dbuser) : @mysql_connect($this->dbhost, $this->dbuser, $dbpass);
        }

		// NOTE: It doesn't matter if it's null or not - if it's null, then it's not a resource        
        if ( is_resource($this->link_id) && $this->dbname != '' )
        {
			// FIXME: I don't believe that it matters if the connection is closed or not here
            if ( !@mysql_select_db($this->dbname, $this->link_id) )
            {
//                @mysql_close($this->link_id);
//                $this->link_id = false;
				  return $this->error($this->link_id);
            }
            return $this->link_id;
        }

        return false;
    }

    
    // FIXME: Move close_db functionality into this method and dbal's sql_close() method; deprecate close_db
    function _sql_close()
    {
        return $this->close_db();
    }

    /**
     * Closes MySQL connection
     * 
     * @return bool
     */
    // FIXME: Deprecate me!
    function close_db()
    {
        if ( $this->link_id )
        {
            if ( $this->query_id )
            {
                @mysql_free_result($this->query_id);
            }
            return @mysql_close($this->link_id);
        }
        else
        {
            return false;
        }
    }
        
    /**
     * Get error information
     * 
     * @return array Error information
     */
    function error()
    {
		if (!$this->link_id)
		{
			return array(
				'message'	=> @mysql_error(),
				'code'		=> @mysql_errno()
			);
		}

		return array(
			'message'	=> @mysql_error($this->link_id),
			'code'		=> @mysql_errno($this->link_id)
		);
    }
    
    /**
     * Basic query function
     * 
     * @param string $query Query string
     * @param array $params If present, replce :params in $query with the value of {@link build_query}
     * @return int|string|bool Query ID, Error string or false
     */
     // TODO: Split out any of the generic query code and place it in dbal.php; 
     //       replace all db-specific parts with a single _sql_query() method, called by dbal's generic query method.
    function query($query, $params = null)
    {
        global $table_prefix, $debug;
        
        // Remove pre-existing query resources
        unset($this->query_id);
        
        $query = preg_replace('#__([^\s]+)#', $table_prefix . '\1', $query);
        
        if ( is_array($params) && count($params) > 0 )
        {
            $params = $this->build_query(preg_replace('/^(INSERT|UPDATE).+/', '\1', $query), $params);
            
            $query = str_replace(':params', $params, $query);
        }

        if ( $query != '' )
        {
            $this->query_count++;
            $this->query_id = @mysql_query($query, $this->link_id);
        }

        if ( !empty($this->query_id) )
        {
            if ( DEBUG == 2 )
            {
                $this->queries[$this->query_count] = $query;
            }
            
            unset($this->record[$this->query_id]);
            unset($this->record_set[$this->query_id]);
            return $this->query_id;
        }
        else
        {
            if ( DEBUG )
            {
                $error = $this->error();
                $message  = 'SQL query error<br /><br />';
                $message .= 'Query: '.$query.'<br />';
                $message .= 'Message: '.$error['message'].'<br />';
                $message .= 'Code: '.$error['code'];
                
                if ( $this->error_die )
                {
                    die($message);
                }
                
                return $message;
            }
            
            return false;
        }
    }
    
    /**
     * Return the first record (single column) in a query result
     * 
     * @param string $query Query string
     */
    function query_first($query)
    {
        $this->query($query);
        $record = $this->fetch_record($this->query_id, false);
        $this->free_result($this->query_id);
        
        return $record[0];
    }
    
    /**
     * Build query
     * Ikonboard -> phpBB -> EQdkp
     * 
     * @param string $query Type of query to build, either INSERT or UPDATE
     * @param array $array Array of field => value pairs
     * @return string
     */
    function build_query($query, $array = false)
    {
        if ( !is_array($array) )
        {
            return false;
        }
        
        $fields = array();
        $values = array();
        
        if ( $query == 'INSERT' )
        {
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
                    $values[] = "'" . $this->escape($value) . "'";
                }
                else
                {
                    $values[] = "'" . (( is_bool($value) ) ? intval($value) : $value) . "'";
                }
            }
            
            $query = ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
        }
        elseif ( $query == 'UPDATE' )
        {
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
                    $values[] = "$field = '" . $this->escape($value) . "'";
                }
                else
                {
                    $values[] = ( is_bool($value) ) ? "$field = '" . intval($value) . "'" : "{$field} = '{$value}'";
                }
            }
            
            $query = implode(', ', $values);
        }
        
        return $query;
    }
    
    /**
     * Fetch a record
     * 
     * @param int $query_id Query ID
     * @param int $assoc MYSQL_ASSOC if true, MYSQL_NUM if false
     * @return array|bool Record array or false
     */
    function fetch_record($query_id = 0, $assoc = true)
    {
        if ( !$query_id )
        {
            $query_id = $this->query_id;
        }
        
        $result_type = ( $assoc ) ? MYSQL_ASSOC : MYSQL_NUM;
        
        if ( $query_id )
        {
            $this->record[$query_id] = @mysql_fetch_array($query_id, $result_type);
            return $this->record[$query_id];
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Fetch a record set
     * 
     * @param int $query_id Query ID
     * @return array|bool Record set array or false
     */
    // FIXME: This isn't currently used anywhere. Delete it? There's probably a few places where it may be useful
    function fetch_record_set($query_id = 0)
    {
        if ( !$query_id )
        {
            $query_id = $this->query_id;
        }
        if ( $query_id )
        {
            unset($this->record_set[$query_id]);
            unset($this->record[$query_id]);
            while ( $this->record_set[$query_id] = @mysql_fetch_array($query_id) )
            {
                $result[] = $this->record_set[$query_id];
            }
            return $result;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Find the number of returned rows
     * 
     * @param int $query_id Query ID
     * @return int|bool Number of rows or false
     */
    function num_rows($query_id = 0)
    {
        if ( !$query_id )
        {
            $query_id = $this->query_id;
        }
        
        if ( $query_id )
        {
            $result = @mysql_num_rows($query_id);
            return $result;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Finds the number of rows affected by a query
     * 
     * @return int
     */
    function affected_rows()
    {
        return ( $this->link_id ) ? @mysql_affected_rows($this->link_id) : 0;
    }
    
    /**
     * Find the ID of the last row inserted
     * 
     * @return int|bool ID or false
     */
    function insert_id()
    {
        if ( $this->link_id )
        {
            $result = @mysql_insert_id($this->link_id);
            return intval($result);
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Free result data
     * 
     * @param int $query_id Query ID
     * @return bool
     */
    function free_result($query_id = 0)
    {
        if ( !$query_id )
        {
            $query_id = $this->query_id;
        }

        if ( $query_id )
        {
            unset($this->record[$query_id]);
            unset($this->record_set[$query_id]);

            @mysql_free_result($query_id);

            return true;
        }

        return false;
    }
    
}