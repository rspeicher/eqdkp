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
	var $db_connect_id;
	var $query_result;
	var $return_on_error = false;
	var $transaction = false;
	var $sql_time = 0;
	var $num_queries = array();
	var $open_queries = array();

	var $curtime = 0;
	var $query_hold = '';
	var $html_hold = '';
	var $sql_report = '';
	
	var $persistency = false;
	var $user = '';
	var $server = '';
	var $dbname = '';

	// Set to true if error triggered
	var $sql_error_triggered = false;

	// Holding the last sql query on sql error
	var $sql_error_sql = '';

	// Holding transaction count
	var $transactions = 0;

	// Supports multi inserts?
	var $multi_insert = false;

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
		$this->num_queries = array(
			'cached'		=> 0,
			'normal'		=> 0,
			'total'			=> 0,
		);

		// Fill default sql layer based on the class being called.
		// This can be changed by the specified layer itself later if needed.
		$this->sql_layer = substr(get_class($this), 5);

		// Do not change this please! This variable is used to easy the use of it - and is hardcoded.
		$this->any_char = chr(0) . '%';
		$this->one_char = chr(0) . '_';
	}

	/**
	* return on error or display error message
	*/
	function sql_return_on_error($fail = false)
	{
		$this->sql_error_triggered = false;
		$this->sql_error_sql = '';

		$this->return_on_error = $fail;
	}

	/**
	* Return number of sql queries and cached sql queries used
	*/
	function sql_num_queries($cached = false)
	{
		return ($cached) ? $this->num_queries['cached'] : $this->num_queries['normal'];
	}

	/**
	* Add to query count
	*/
	function sql_add_num_queries($cached = false)
	{
		$this->num_queries['cached'] += ($cached) ? 1 : 0;
		$this->num_queries['normal'] += ($cached) ? 0 : 1;
		$this->num_queries['total'] += 1;
	}

	/**
	* DBAL garbage collection, close sql connection
	*/
	function sql_close()
	{
		if (!$this->db_connect_id)
		{
			return false;
		}

		if ($this->transaction)
		{
			$this->sql_transaction('commit');
		}

		foreach ($this->open_queries as $query_id)
		{
			$this->sql_freeresult($query_id);
		}
		
		return $this->_sql_close();
	}

	/**
	* SQL Transaction
	* @access private
	*/
	function sql_transaction($status = 'begin')
	{
		switch ($status)
		{
			case 'begin':
				// If we are within a transaction we will not open another one, but enclose the current one to not loose data (prevening auto commit)
				if ($this->transaction)
				{
					$this->transactions++;
					return true;
				}

				$result = $this->_sql_transaction('begin');

				if (!$result)
				{
					$this->sql_error();
				}

				$this->transaction = true;
			break;

			case 'commit':
				// If there was a previously opened transaction we do not commit yet... but count back the number of inner transactions
				if ($this->transaction && $this->transactions)
				{
					$this->transactions--;
					return true;
				}

				$result = $this->_sql_transaction('commit');

				if (!$result)
				{
					$this->sql_error();
				}

				$this->transaction = false;
				$this->transactions = 0;
			break;

			case 'rollback':
				$result = $this->_sql_transaction('rollback');
				$this->transaction = false;
				$this->transactions = 0;
			break;

			default:
				$result = $this->_sql_transaction($status);
			break;
		}

		return $result;
	}

}

/**
* This variable holds the class name to use later
*/
$sql_db = 'dbal_' . $dbms;
