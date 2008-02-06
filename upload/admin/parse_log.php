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
    var $_ranks = array();
    
    var $input_guilds;
    var $input_ranks;
    
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
        
        $gm->set_current_game($eqdkp->config['current_game']);
        
        // Store our input options and cache member->rank values
        $this->_prepare_parse();
        
        $log = $in->get('log');
        $log = explode("\n", $log);
        
        // Prepare session storage
        session_start();
        $_SESSION['log'] = array();
        
        // Loop through each line of the log, performing a log parse on each and
        // adding it to our array of member names if it matches the input options
        $results = array();
        $members = array();
        foreach ( $log as $line )
        {
            $result = $gm->parse_log_entry($line);
            
            if ( $this->_session_store($result) )
            {
                $members[] = $result['name'];                
            }
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
    
    /**
     * Populate the values of {@link $input_ranks}, {@link $input_guilds}
     * and {@link $_ranks} to reduce overhead.
     *
     * @return void
     * @access private
     */
    function _prepare_parse()
    {
        global $db, $in;
        
        $this->input_ranks  = $in->getArray('ranks', 'int', 2);
        $this->input_guilds = $in->getArray('guilds', 'string', 2);
        
        $sql = "SELECT member_name, member_rank_id
                FROM __members
                ORDER BY member_name";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $this->_ranks[$row['member_name']] = $row['member_rank_id'];
        }
        $db->free_result($result);
    }
    
    /**
     * Checks if a resulting entry from a log parse matches the user's specified
     * options in order to be stored for data updating via AddRaid, and if so,
     * stores the member's data in the current session.
     *
     * @param array $result Resulting data from Game_Manager::parse_log_entry()
     * @return bool true if stored, false if not
     * @access private
     */
    function _session_store($result)
    {
        global $in;
        
        if ( empty($result['name']) )
        {
            return false;
        }
        
        $find_all    = $in->exists('findall');
        $guild_check = false;
        $rank_check  = false;
        
        $name  = $result['name'];
        $guild = $result['guild'];
        $rank  = ( isset($this->_ranks[$name]) ) ? $this->_ranks[$name] : 0;
        
        // See if this member's guild tag is enabled in our options
        // NOTE: A member without a guild will never be included unless 'Find all' is checked. Do we want to change that?
        foreach ( $this->input_guilds as $input_guild )
        {
            if ( unsanitize($input_guild) == unsanitize($guild) )
            {
                $guild_check = true;
            }
        }
        
        // See if this member's rank ID is enabled in our options
        $rank_check = in_array($rank, $this->input_ranks);
        
        // 'Find all' overrides guild checks, but NOT rank checks
        if ( ($find_all || $guild_check) && $rank_check )
        {
            $_SESSION['log'][$name] = array(
                'name'  => $name,
                'class' => $result['class'],
                'race'  => $result['race'],
                'level' => $result['level']
            );
            
            return true;
        }
        else
        {
            return false;
        }
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
                    'CBNAME'    => 'guilds[]',
                    'CBVALUE'   => sanitize($guildtag, ENT),
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
                    'CBNAME'    => 'ranks[]',
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
            // TODO: Remove after debug
            // 'gen_simple_header' => true,
            'template_file'     => 'admin/parse_Everquest.html',
            'display'           => true
        ));
    }
}

$parse_log = new Parse_Log;
$parse_log->process();