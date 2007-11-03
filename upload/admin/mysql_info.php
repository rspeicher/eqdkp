<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        mysql_info.php
 * Began:       Sat Apr 5 2003
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

class MySQL_Info extends EQdkp_Admin
{
    var $mysql_version = '';
    var $table_size    = 0;
    var $index_size    = 0;
    var $num_tables    = 0;
    
    function mysql_info()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;
        
        parent::eqdkp_admin();
        
        $this->assoc_buttons(array(
            'form' => array(
                'name'    => '',
                'process' => 'display_info',
                'check'   => 'a_'
            )
        ));
        
        $result = $db->query('SELECT VERSION() AS mysql_version');
        if ( $row = $db->fetch_record($result) )
        {
            $this->mysql_version = $row['mysql_version'];
        }
    }
    
    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_info()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID, $dbname, $table_prefix;
		
//		$mysql_version = preg_replace('/^((?:\d+\.?)+?)[^\d]*?$/', '\1', $mysql_version);

		// Get ourselves a comparable database version number
		$ver = $this->mysql_version;
		if (strpos($ver, 'community') !== false)
		{
			$ver = substr($ver, 0, strpos($ver, '-'));
		}
		
        if ( version_compare($ver, '4.1.3', '<') )
        {
            $db_name = ( version_compare($ver, '3.23.6', '>=') ) ? "`$dbname`" : $dbname;
            $dbsize = 0;
            
            // Get table status
            $sql = 'SHOW TABLE STATUS
                    FROM ' . $db_name;
            $result = $db->query($sql);
                
            while ( $row = $db->fetch_record($result) )
            {
/*                if ( isset($row['Type']) && $row['Type'] == 'MRG_MyISAM' )
                {
					continue;
				}
*/
				if ( empty($table_prefix) )
				{
					continue;
				}
				
				// Current row is an EQdkp table, get info for it
				if ( preg_match('/^' . $table_prefix . '.+/', $row['Name']) )
				{
					$tpl->assign_block_vars('table_row', array(
						'ROW_CLASS'  => $eqdkp->switch_row_class(),
						'TABLE_NAME' => $row['Name'],
						'ROWS'       => number_format($row['Rows'], ','),
						'TABLE_SIZE' => db_size($row['Data_length']),
						'INDEX_SIZE' => db_size($row['Index_length'])
					));
					
					$this->num_tables++;
					$this->table_size += $row['Data_length'];
					$this->index_size += $row['Index_length'];
				} // name match

            } // while
        }
		// MySQL >= 4.1.3
		else
		{
            $db_name = "`$dbname`";
            $dbsize = 0;
			
            // Get table status
            $sql = 'SHOW TABLE STATUS
                    FROM ' . $db_name;
            $result = $db->query($sql);

            while ( $row = $db->fetch_record($result) )
            {
                if ( (isset($row['Engine']) && $row['Engine'] == 'MRG_MyISAM') || empty($table_prefix) )
                {
					continue;
				}
				
				$total_rows = number_format($row['Rows'], ',');
				
				// Current row is an EQdkp table, get info for it
				if ( preg_match('/^' . $table_prefix . '.+/', $row['Name']) )
				{
					$tpl->assign_block_vars('table_row', array(
						'ROW_CLASS'  => $eqdkp->switch_row_class(),
						'TABLE_NAME' => $row['Name'],
						'ROWS'       => $total_rows,
						'TABLE_SIZE' => db_size($row['Data_length']),
						'INDEX_SIZE' => db_size($row['Index_length'])
					));
					
					$this->num_tables++;
					$this->table_size += $row['Data_length'];
					$this->index_size += $row['Index_length'];
				} // name match
			}
		}

		// Output the page
		$tpl->assign_vars(array(
			'DBNAME'    => str_replace('`', '', $db_name),
			'DBVERSION' => $this->mysql_version,
			
			'NUM_TABLES'       => sprintf($user->lang['num_tables'], $this->num_tables),
			'TOTAL_TABLE_SIZE' => db_size($this->table_size),
			'TOTAL_INDEX_SIZE' => db_size($this->index_size),
			'TOTAL_SIZE'       => db_size($this->table_size + $this->index_size),
			
			'L_DATABASE_INFO'    => $user->lang['database_info'],
			'L_DATABASE_VERSION' => $user->lang['database_version'],
			'L_DATABASE_NAME'    => $user->lang['database_name'],
			'L_DATABASE_SIZE'    => $user->lang['database_size'],
			
			'L_EQDKP_TABLES' => $user->lang['eqdkp_tables'],
			'L_TABLE_NAME'   => $user->lang['table_name'],
			'L_ROWS'         => $user->lang['rows'],
			'L_TABLE_SIZE'   => $user->lang['table_size'],
			'L_INDEX_SIZE'   => $user->lang['index_size'],
			'L_TOTALS'       => $user->lang['totals']
		));
		
		$eqdkp->set_vars(array(
			'page_title'    => $user->lang['mysql_info'],
			'template_file' => 'admin/mysql_info.html',
			'display'       => true
		));

    }
    
}

$info = new MySQL_Info;
$info->process();

/*
 * Helper function 
 * db_size
 */
function db_size($size)
{
	if ( $size >= 1048576 )
	{
		return sprintf('%.2f MB', ($size / 1048576));
	}
	elseif ( $size >= 1024 )
	{
		return sprintf('%.2f KB', ($size / 1024));
	}
	else
	{
		return sprintf('%.2f B', $size);
	}
}
