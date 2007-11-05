<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        backup.php
 * Began:       Fri Mar 2 2007
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */

define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = './../';
require_once($eqdkp_root_path . 'common.php');

class Backup extends EQdkp_Admin
{
    function backup()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;
        
        parent::eqdkp_admin();
        
        $this->assoc_buttons(array(
            'form' => array(
                'name'    => '',
                'process' => 'display_menu',
                'check'   => 'a_backup'
            ),
            'backup' => array(
                'name'    => 'backup',
                'process' => 'do_backup',
                'check'   => 'a_backup'
            )
        ));
    }
    
    function error_check()
    {
        return $this->fv->is_error();
    }
    
    // ---------------------------------------------------------
    // Display menu
    // ---------------------------------------------------------
    function display_menu()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;
		
		// 'Dynamic' offering of backup format types
		$available_methods = array('gzip' => 'zlib');

		foreach ($available_methods as $type => $module)
		{
			if (!@extension_loaded($module))
			{
				continue;
			}

			$tpl->assign_block_vars('methods', array(
				'TYPE'   => $type
			));
		}

		$tpl->assign_block_vars('methods', array(
			'TYPE'	=> 'text'
		));

		// Assign the rest of the variables.
        $tpl->assign_vars(array(
            'F_BACKUP'            => 'backup.php' . $SID,
            'L_BACKUP_DATABASE'   => $user->lang['backup_database'],
            'L_BACKUP_TITLE'      => $user->lang['backup_title'],
            'L_BACKUP_TYPE'       => $user->lang['backup_type'],
            'L_CREATE_TABLE'      => $user->lang['create_table'],
            'L_SKIP_NONESSENTIAL' => $user->lang['skip_nonessential'],
            'L_YES'               => $user->lang['yes'],
            'L_NO'                => $user->lang['no']
        ));
        
        $eqdkp->set_vars(array(
            'page_title'    => page_title($user->lang['backup']),
            'template_file' => 'admin/backup.html',
            'display'       => true
        ));
    }
    
    // ---------------------------------------------------------
    // Main Backup Script
    // ---------------------------------------------------------
    function do_backup()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $dbhost, $in;
        global $SID;
        
        $tables = array(
            '__adjustments',
            '__auth_options',
            '__auth_users',
            '__config',
            '__events',
            '__items',
            '__logs',
            '__members',
            '__member_ranks',
            '__member_user',
            '__news',
            '__plugins',
            '__raids',
            '__raid_attendees',
            '__sessions',
            '__styles',
            '__style_config',
            '__users',
            // Game-specific tables
            '__classes',
            '__races',
            '__factions',
        );
          
		$time = time();
		$run_comp = false;

        $format = $in->get('method','');

		// NOTE: Right now, we're not using a temporary file to create the backup. 
		// However, you could use $open, $write and $close as vars for function names to create a temp file.
		switch ($format)
		{
			case 'gzip':
				$ext = '.sql.gz';
				$open = 'gzopen';
				$write = 'gzwrite';
				$close = 'gzclose';
				$mimetype = 'application/x-gzip';
			break;
			
			case 'text':
			default:
				$ext = '.sql';
				$open = 'fopen';
				$write = 'fwrite';
				$close = 'fclose';
				$mimetype = 'text/x-sql';
			break;
		}
		
		// Set the backup filename
		$filename = 'eqdkp-backup_' . date('Y-m-d_Hi', $time);
		$name = $filename . $ext;
		
		// Set the page headers for a file download
		header('Pragma: no-cache');
		header("Content-Type: $mimetype; name=\"$name\"");
		header("Content-disposition: attachment; filename=$name");


		// Start the format type object (if possible)
		switch ($format)
		{
			case 'gzip':
				if ((isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'msie') === false)
				{
					ob_start('ob_gzhandler');
				}
				else
				{
					$run_comp = true;
				}
			break;
		}

		// 
		// Generate the backup
		//
		
        //Lets write our header
		$data = '';
        $data .= "-- EQDKP SQL Dump " . "\n";
        $data .= "-- version " . EQDKP_VERSION . "\n";
        $data .= "-- http://www.eqdkp.com" . "\n";
        $data .= "-- \n";
        $data .= "-- Host: " . (!empty($dbhost) ? $dbhost : 'localhost') . "\n";
        $data .= "-- Generation Time: " . date('M d, Y \a\t g:iA', $time) . "\n";
        $data .= "-- \n";
        $data .= "-- --------------------------------------------------------" . "\n";
        $data .= "\n";
        
        foreach ( $tables as $table )
        {
            $tablename        = $this->_generate_table_name($table);
            $table_sql_string = $this->_create_table_sql_string($tablename);
            $data_sql_string  = $this->_create_data_sql_string($tablename);
        
            // NOTE: Error checking for table or data sql strings here?
			        
            if ( $_POST['create_table'] == 'Y' )
            {
                $data .= "\n" . "-- \n";
                $data .= "-- Table structure for table `{$tablename}`" . "\n";
                $data .= "-- \n\n";
                $data .= $table_sql_string . "\n";
            }

            if ( $table != '__sessions' ) 
            {
                $data .= "\n" . "-- \n";
                $data .= "-- Dumping data for table `{$tablename}`" . "\n";
                $data .= "-- \n\n";
                $data .= (($data_sql_string) ? $data_sql_string : "-- No data available.") . "\n";
            }
        
        }
        unset($tablename, $table_sql_string, $data_sql_string);
        
		
		// Output the backup data
		switch($format)
		{
			case 'gzip':
				if( $run_comp )
				{
					echo gzencode($data);
				}
				else
				{
					ob_flush();
					flush();
					echo $data;
				}
			break;
			
			case 'text':
			default:
				echo $data;
			break;
		}
    }
    
    function _create_table_sql_string($tablename)
    {
        global $db;
        // Generate the SQL string for this table
        // NOTE: SHOW CREATE TABLE was added to MySQL version 3.23.20, so I think it's safe to use that instead of doing it all manually.

		$sql = 'SHOW CREATE TABLE ' . $tablename;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);

        $sql_string  = "DROP TABLE IF EXISTS `{$tablename}`;" . "\n";

		$sql_string .= $row['Create Table'];
		$sql_string .= ";\n\n";

		$db->sql_freeresult($result);
        
        return $sql_string;
    }
    
	//This sql data construction method is thanks to phpBB3.
    function _create_data_sql_string($tablename)
    {
        global $db;
        
        // Initialise the sql string
        $sql_string = "";
        
        // Get field names from MySQL and output to a string in the correct MySQL syntax
        $sql = "SELECT * FROM $tablename";
		$result = mysql_unbuffered_query($sql, $db->link_id);

		if ($result != false)
		{
			$fields_cnt = mysql_num_fields($result);
	
			// Get field information
			$field = array();
			for ($i = 0; $i < $fields_cnt; $i++)
			{
				$field[] = mysql_fetch_field($result, $i);
			}
			$field_set = array();
			
			for ($j = 0; $j < $fields_cnt; $j++)
			{
				$field_set[] = $field[$j]->name;
			}

			// Set some constant values for the table
			$search         = array("\\", "'", "\x00", "\x0a", "\x0d", "\x1a", '"');
			$replace        = array("\\\\", "\\'", '\0', '\n', '\r', '\Z', '\\"');
			$fields         = implode(', ', $field_set);
			$field_string   = 'INSERT INTO ' . $tablename . ' (' . $fields . ') VALUES ';

			// Generate the data for the table. 
			// Note that the data dump is done without multi-values.
			while ($row = mysql_fetch_row($result))
			{
				$values = array();

				$query = $field_string . '(';

				for ($j = 0; $j < $fields_cnt; $j++)
				{
					if (!isset($row[$j]) || is_null($row[$j]))
					{
						$values[$j] = 'NULL';
					}
					else if ($field[$j]->numeric && ($field[$j]->type !== 'timestamp'))
					{
						$values[$j] = $row[$j];
					}
					else
					{
						$values[$j] = "'" . str_replace($search, $replace, $row[$j]) . "'";
					}
				}
				$query .= implode(', ', $values) . ')';

				$sql_string .= $field_string . $query . ";\n";
			}
			mysql_free_result($result);
		}
		
        return $sql_string;
    }
      
    function _generate_table_name($val)
    {
        global $table_prefix;
        
        $val = preg_replace('#__([^\s]+)#', $table_prefix . '\1', $val);
        return $val;
    }
}

$backup = new Backup;
$backup->process();