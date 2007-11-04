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
    var $num_rows      = 0;
    
    function mysql_info()
    {
        global $db;
        
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
        global $db, $eqdkp, $tpl, $user;
        global $dbname, $table_prefix;
        
//        $mysql_version = preg_replace('/^((?:\d+\.?)+?)[^\d]*?$/', '\1', $mysql_version);

        // Get ourselves a comparable database version number
        $ver     = preg_replace('/[^0-9\.]/', '', $this->mysql_version);
        $db_name = ( version_compare($ver, '3.23.6', '>=') ) ? "`$dbname`" : $dbname;
        $dbsize  = 0;
        
        // Get table status
        $sql = 'SHOW TABLE STATUS
                FROM ' . $db_name;
        $result = $db->query($sql);
        
        if ( version_compare($ver, '4.1.3', '<') )
        {
            while ( $row = $db->fetch_record($result) )
            {
                // FIXME: When would this ever happen?
                if ( empty($table_prefix) )
                {
                    continue;
                }
                
                $this->table_row($row);
            }
        }
        // MySQL >= 4.1.3
        else
        {
            while ( $row = $db->fetch_record($result) )
            {
                if ( (isset($row['Engine']) && $row['Engine'] == 'MRG_MyISAM') || empty($table_prefix) )
                {
                    continue;
                }
                
                $this->table_row($row);
            }
        }

        // Output the page
        $tpl->assign_vars(array(
            'DBNAME'    => str_replace('`', '', $db_name),
            'DBVERSION' => $this->mysql_version,
            
            'NUM_TABLES'       => sprintf($user->lang['num_tables'], $this->num_tables),
            'TOTAL_ROWS'       => number_format($this->num_rows),
            'TOTAL_TABLE_SIZE' => $this->db_size($this->table_size),
            'TOTAL_INDEX_SIZE' => $this->db_size($this->index_size),
            'TOTAL_SIZE'       => $this->db_size($this->table_size + $this->index_size),
            
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
    
    ## ########################################################################
    ## Helper Methods
    ## ########################################################################
    
    /**
     * Format a number into an appropriate byte size
     *
     * @param string $size Number to format
     * @return string
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
    
    /**
     * Append a table data row for output
     *
     * @param string $row 
     * @return void
     */
    function table_row($row)
    {
        global $eqdkp, $tpl;
        global $table_prefix;
        
        if ( preg_match('/^' . $table_prefix . '.+/', $row['Name']) )
        {
            // Current row is an EQdkp table, get info for it
            $tpl->assign_block_vars('table_row', array(
                'ROW_CLASS'  => $eqdkp->switch_row_class(),
                'TABLE_NAME' => $row['Name'],
                'ROWS'       => number_format($row['Rows']),
                'TABLE_SIZE' => $this->db_size($row['Data_length']),
                'INDEX_SIZE' => $this->db_size($row['Index_length'])
            ));
            
            $this->num_tables++;
            $this->table_size += $row['Data_length'];
            $this->index_size += $row['Index_length'];
            $this->num_rows   += $row['Rows'];
        }
    }
}

$info = new MySQL_Info;
$info->process();