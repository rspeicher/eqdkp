<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        parse_log.php
 * Began:       Sat Mar 05 2005
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

class Parse_Log extends EQdkp_Admin
{
    function parse_log()
    {
        parent::eqdkp_admin();
        
        $this->assoc_buttons(array(
            'parse' => array(
                'name'    => 'parse',
                'process' => 'process_parse',
                'check'   => 'a_raid_'
            ),
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_raid_'
            )
        ));
    }
    
    // ---------------------------------------------------------
    // Process Parse
    // ---------------------------------------------------------
    function process_parse()
    {
        global $eqdkp, $gm, $in, $tpl, $user;
        
        $log = $in->get('log');
        $log = explode("\n", $log);
        
        $members = array();
        foreach ( $log as $line )
        {
            $result = $gm->parse_log_entry($line);
            
            // TODO: Need to Session-store the other values around here
            // TODO: Need to ignore certain entries based on the form options
            $members[] = $result['name'];
        }
        
        $members = array_unique($members);
        sort($members);
        
        $tpl->assign_vars(array(
            'S_STEP1'         => false,
            'L_FOUND_MEMBERS' => sprintf($user->lang['found_members'], count($log), count($members)),
            'L_LOG_DATE_TIME' => $user->lang['log_date_time'],
            'L_LOG_ADD_DATA'  => $user->lang['log_add_data'],
            
            'FOUND_MEMBERS' => implode("\n", $members),
            
            // TODO: Need to parse timestamps if available
            // 'MO'            => $this->M_to_n($date['mo']),
            // 'D'             => $date['d'],
            // 'Y'             => $date['y'],
            // 'H'             => $date['h'],
            // 'MI'            => $date['mi'],
            // 'S'             => $date['s']
        ));
        
        $eqdkp->set_vars(array(
            'page_title'        => page_title($user->lang['parselog_title']),
            'gen_simple_header' => true,
            'template_file'     => 'admin/parse_log.html',
            'display'           => true
        ));
    }

    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        
        $log_columns = ( preg_match("/Mozilla\/4\.[1-9]{1}.+/", $_SERVER['HTTP_USER_AGENT']) ) ? '50' : '90';
        
        // Options to parse
        $options = array(
            0 => array(
                'CBNAME'    => 'findall',
                'CBVALUE'   => '1',
                'CBCHECKED' => '',
                'OPTION'    => $user->lang['log_find_all']
            ),
            1 => array(
                'CBNAME'    => 'findrole',
                'CBVALUE'   => '1',
                'CBCHECKED' => ' checked="checked"',
                'OPTION'    => $user->lang['include_roleplay']
            )
        );
        
        // Guildtags to parse
        if ( !empty($eqdkp->config['parsetags']) )
        {
            $parsetags = explode("\n", $eqdkp->config['parsetags']);
            foreach ( $parsetags as $index => $guildtag )
            {
                $guildtag = trim($guildtag);
                $tagoptions[] = array(
                    'CBNAME'    => 'g_' . str_replace(' ', '_', preg_replace('/[^\w]/', '', $guildtag)),
                    'CBVALUE'   => '1',
                    'CBCHECKED' => ' checked="checked"',
                    'OPTION'    => '&lt;' . sanitize($guildtag, ENT) . '&gt;'
                );
            }
            $options = array_merge($options, $tagoptions);
        }
        
        foreach ( $options as $row )
        {
            $tpl->assign_block_vars('options_row', $row);
        }
        
        // Member tags to parse
        // Find out how many members have each rank
        $rank_counts = array();
        $sql = 'SELECT `member_rank_id`, COUNT(`member_rank_id`) as `rank_count`
                FROM __members
                GROUP BY `member_rank_id`';
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $rank_counts[ $row['member_rank_id'] ] = $row['rank_count'];
        }
        $db->free_result($result);
        
        $ranks = array();
        $sql = 'SELECT `rank_id`, `rank_name`, `rank_prefix`, `rank_suffix`
                FROM __member_ranks
                ORDER BY `rank_name`';
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            // Make sure there's not a guildtag with the same name as the rank
            if ( !in_array($row['rank_name'], $options) )
            {
                $rank_count = ( isset($rank_counts[ $row['rank_id'] ]) ) ? $rank_counts[ $row['rank_id'] ] : 0;
                $format = ( $rank_count == 1 ) ? $user->lang['x_members_s'] : $user->lang['x_members_p'];
                
                $ranks[] = array(
                    'CBNAME'    => 'r_' . str_replace(' ', '_', trim($row['rank_name'])),
                    'CBVALUE'   => intval($row['rank_id']),
                    'CBCHECKED' => ' checked="checked"',
                    'OPTION'    => $user->lang['rank'] . ': ' . (( empty($row['rank_name']) ) ? '(None)' : $row['rank_prefix'] . $row['rank_name'] . $row['rank_suffix'])
                                   . ' <span class="small">(' . sprintf($format, $rank_count) . ')</span>'
                );
            }
        }
        $db->free_result($result);
        
        foreach ( $ranks as $row )
        {
            $tpl->assign_block_vars('ranks_row', $row);
        }
        
        $tpl->assign_vars(array(
            'F_PARSE_LOG'    => path_default('admin/parse_log.php'),
            
            'S_STEP1'        => true,
            'L_PASTE_LOG'    => $user->lang['paste_log'],
            'L_OPTIONS'      => $user->lang['options'],
            'L_PARSE_LOG'    => $user->lang['parse_log'],
            'L_CLOSE_WINDOW' => $user->lang['close_window'],
            
            'LOG_COLS' => $log_columns
        ));
        
        $eqdkp->set_vars(array(
            'page_title'        => page_title($user->lang['parselog_title']),
            // 'gen_simple_header' => true,
            'template_file'     => 'admin/parse_Everquest.html',
            'display'           => true
        ));
    }
}

$parse_log = new Parse_Log;
$parse_log->process();