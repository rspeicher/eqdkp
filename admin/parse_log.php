<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * parse_Everquest.php
 * Began: Sat March 05 2005
 * 
 * $Id: parse_Everquest.php 46 2007-06-19 07:29:11Z tsigo $
 * 
 ******************************/
 
define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = './../';
include_once($eqdkp_root_path . 'common.php');

class Parse_Log extends EQdkp_Admin
{
    function parse_log()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;
        
        parent::eqdkp_admin();
        
        $this->assoc_buttons(array(
            'parse' => array(
                'name'    => 'parse',
                'process' => 'process_parse',
                'check'   => 'a_raid_'),
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_raid_'))
        );
    }
    
    // ---------------------------------------------------------
    // Process Parse
    // ---------------------------------------------------------
    function process_parse()
    {
        /*
        $tpl->assign_vars(array(
            'S_STEP1'         => false,
            'L_FOUND_MEMBERS' => sprintf($user->lang['found_members'], $line_count, sizeof($member_names)),
            'L_LOG_DATE_TIME' => $user->lang['log_date_time'],
            'L_LOG_ADD_DATA'  => $user->lang['log_add_data'],
            
            'FOUND_MEMBERS' => implode("\n", $member_names),
            'MO'            => $this->M_to_n($date['mo']),
            'D'             => $date['d'],
            'Y'             => $date['y'],
            'H'             => $date['h'],
            'MI'            => $date['mi'],
            'S'             => $date['s'])
        );
        */
        
        $eqdkp->set_vars(array(
            'page_title'        => page_title($user->lang['parselog_title']),
            'gen_simple_header' => true,
            'template_file'     => 'admin/parse_Everquest.html',
            'display'           => true
        ));
    }
    
    // ---------------------------------------------------------
    // Process helper methods
    // ---------------------------------------------------------
    function M_to_n($m)
    {
        switch($m)
        {
            case 'Jan':
                return '01';
                break;
            case 'Feb':
                return '02'; 
                break;
            case 'Mar':
                return '03'; 
                break;
            case 'Apr':
                return '04'; 
                break;
             case 'May': 
                return '05';
                break;
             case 'Jun':
                return '06';
                break;
             case 'Jul':
                return '07'; 
                break;
             case 'Aug': 
                return '08';
                break;
             case 'Sep':
                return '09'; 
                break;
             case 'Oct': 
                return '10';
                break;
             case 'Nov':
                return '11'; 
                break;
             case 'Dec':
                return '12';
                break;
        }
    }
    
    function original_class($class)
    {
        $classes = array(
            'Bard'          => array('Bard','Minstrel','Troubadour','Virtuoso','Maestro'),
            'Beastlord'     => array('Beastlord','Primalist','Animist','Savage Lord','Feral Lord'),
            'Berserker'     => array('Berserker','Brawler','Vehement','Rager','Fury'),
            'Cleric'        => array('Cleric','Vicar','Templar','High Priest','Archon'),
            'Druid'         => array('Druid','Wanderer','Preserver','Hierophant','Storm Warden'),
            'Enchanter'     => array('Enchanter','Illusionist','Beguiler','Phantasmist','Coercer'),
            'Magician'      => array('Magician','Elementalist','Conjurer','Arch Mage','Arch Convoker'),
            'Monk'          => array('Monk','Disciple','Master','Grandmaster','Transcendent'),
            'Necromancer'   => array('Necromancer','Heretic','Defiler','Warlock','Arch Lich'),
            'Paladin'       => array('Paladin','Cavalier','Knight','Crusader','Lord Protector'),
            'Ranger'        => array('Ranger','Pathfinder','Outrider','Warder','Hunter','Forest Stalker'),
            'Rogue'         => array('Rogue','Rake','Blackguard','Assassin','Deceiver'),
            'Shadow Knight' => array('Scourge Knight','Shadow Knight','Reaver','Revenant','Grave Lord','Dread Lord'),
            'Shaman'        => array('Shaman','Mystic','Luminary','Oracle','Prophet'),
            'Warrior'       => array('Warrior','Champion','Myrmidon','Warlord','Overlord'),
            'Wizard'        => array('Wizard','Channeler','Evoker','Sorcerer','Arcanist')
        );
        
        foreach ( $classes as $k => $v)
        {
            if ( in_array($class, $v) )
            {
                return $k;
            }
        }
        
        return false;
    }
    
    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;
        
        $log_columns = ( preg_match("/Mozilla\/4\.[1-9]{1}.+/", $_SERVER['HTTP_USER_AGENT']) ) ? '50' : '90';
        
        // Options to parse
        $options = array(
            0 => array(
                'CBNAME'    => 'findall',
                'CBVALUE'   => '1',
                'CBCHECKED' => '',
                'OPTION'    => $user->lang['log_find_all']),
            1 => array(
                'CBNAME'    => 'findrole',
                'CBVALUE'   => '1',
                'CBCHECKED' => ' checked="checked"',
                'OPTION'    => 'Include Roleplay')
        );
        
        // Guildtags to parse
        if ( !empty($eqdkp->config['parsetags']) )
        {
            $parsetags = explode("\n", $eqdkp->config['parsetags']);
            foreach ( $parsetags as $index => $guildtag )
            {
                $tagoptions[] = array(
                    'CBNAME'    => str_replace(' ', '_', trim($guildtag)),
                    'CBVALUE'   => '1',
                    'CBCHECKED' => ' checked="checked"',
                    'OPTION'    => '&lt;' . trim($guildtag) . '&gt;');
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
        $sql = 'SELECT `member_rank_id`, count(`member_rank_id`) as `count`
                FROM __members
                GROUP BY `member_rank_id`';
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $rank_counts[ $row['member_rank_id'] ] = $row['count'];
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
                                   . ' <span class="small">(' . sprintf($format, $rank_count) . ')</span>');
            }
        }
        $db->free_result($result);
        
        foreach ( $ranks as $row )
        {
            $tpl->assign_block_vars('ranks_row', $row);
        }
        
        $tpl->assign_vars(array(
            'F_PARSE_LOG'    => 'parse_Everquest.php' . $SID,
            
            'S_STEP1'        => true,
            'L_PASTE_LOG'    => $user->lang['paste_log'],
            'L_OPTIONS'      => $user->lang['options'],
            'L_PARSE_LOG'    => $user->lang['parse_log'],
            'L_CLOSE_WINDOW' => $user->lang['close_window'],
            
            'LOG_COLS' => $log_columns)
        );
        
        $eqdkp->set_vars(array(
            'page_title'        => page_title($user->lang['parselog_title']),
            'gen_simple_header' => true,
            'template_file'     => 'admin/parse_Everquest.html',
            'display'           => true)
        );
    }
}

$parse_log = new Parse_Log;
$parse_log->process();