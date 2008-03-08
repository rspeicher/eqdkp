<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        mm_listmembers.php
 * Began:       Thu Jan 30 2003
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2008 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */
 
// Shows a list of members, basically just an admin-themed version of
// /listmembers.php

if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

$sort_order = array(
    0 => array('member_name', 'member_name desc'),
    1 => array('member_earned desc', 'member_earned'),
    2 => array('member_spent desc', 'member_spent'),
    3 => array('member_adjustment desc', 'member_adjustment'),
    4 => array('member_current desc', 'member_current'),
    5 => array('member_lastraid desc', 'member_lastraid'),
    6 => array('member_level desc', 'member_level'),
    7 => array('member_class', 'member_class desc'),
    8 => array('rank_name', 'rank_name desc'),
    9 => array('armor_type_id', 'armor_type_id desc')
);

$current_order = switch_order($sort_order);

$member_count = 0;
$previous_data = '';

// Figure out what data we're comparing from member to member
// in order to rank them
$sort_index = explode('.', $current_order['uri']['current']);
$previous_source = preg_replace('/( (asc|desc))?/i', '', $sort_order[$sort_index[0]][$sort_index[1]]);

$sql = "SELECT m.*, (m.member_earned-m.member_spent+m.member_adjustment) AS member_current, 
            m.member_status, CONCAT(r.rank_prefix, '%s', r.rank_suffix) AS member_sname, 
            r.rank_name, r.rank_hide, r.rank_id, 
            c.class_name AS member_class,
            at.armor_type_name AS armor_type, 
            MAX(ca.armor_type_id) AS armor_type_id,
            ca.armor_min_level AS min_level, 
            ca.armor_max_level AS max_level
        FROM __members AS m, __member_ranks AS r, __classes AS c, __armor_types AS at, __class_armor AS ca
        WHERE (c.class_id = m.member_class_id)
        AND (ca.class_id = m.member_class_id)
        AND (at.armor_type_id = ca.armor_type_id)
        AND (m.member_rank_id = r.rank_id)
        GROUP BY m.member_id
        ORDER BY {$current_order['sql']}";
if ( !($members_result = $db->query($sql)) )
{
    message_die('Could not obtain member information', '', __FILE__, __LINE__, $sql);
}
while ( $row = $db->fetch_record($members_result) )
{
    $member_count++;
    $tpl->assign_block_vars('members_row', array(
        'ROW_CLASS'     => $eqdkp->switch_row_class(),
        'ID'            => $row['member_id'],
        'COUNT'         => ($row[$previous_source] == $previous_data) ? '&nbsp;' : $member_count,
        'NAME'          => sprintf($row['member_sname'], sanitize($row['member_name'])),
        'RANK'          => sanitize($row['rank_name']),
        'LEVEL'         => ( $row['member_level'] > 0 ) ? intval($row['member_level']) : '&nbsp;',
        'ARMOR'         => ( !empty($row['armor_type']) ) ? sanitize($row['armor_type']) : '&nbsp;',
        'CLASS'         => ( $row['member_class'] != 'NULL' ) ? sanitize($row['member_class']) : '&nbsp;',
        'EARNED'        => number_format($row['member_earned'], 2),
        'SPENT'         => number_format($row['member_spent'], 2),
        'ADJUSTMENT'    => number_format($row['member_adjustment'], 2),
        'CURRENT'       => number_format($row['member_current'], 2),
        'LASTRAID'      => ( !empty($row['member_lastraid']) ) ? date($user->style['date_notime_short'], $row['member_lastraid']) : '&nbsp;',
        'C_ADJUSTMENT'  => color_item($row['member_adjustment']),
        'C_CURRENT'     => color_item($row['member_current']),
        'C_LASTRAID'    => 'neutral',
        'U_VIEW_MEMBER' => edit_member_path($row['member_name'])
    ));
    
    // So that we can compare this member to the next member,
    // set the value of the previous data to the source
    $previous_data = $row[$previous_source];
}
$footcount_text = sprintf($user->lang['listmembers_footcount'], $db->num_rows($members_result));

$tpl->assign_vars(array(
    'F_MEMBERS' => edit_member_path(),
    
    'L_NAME'       => $user->lang['name'],
    'L_RANK'       => $user->lang['rank'],
    'L_LEVEL'      => $user->lang['level'],
    'L_CLASS'      => $user->lang['class'],
    'L_EARNED'     => $user->lang['earned'],
    'L_SPENT'      => $user->lang['spent'],
    'L_ARMOR'      => $user->lang['armor'],
    'L_ADJUSTMENT' => $user->lang['adjustment'],
    'L_CURRENT'    => $user->lang['current'],
    'L_LASTRAID'   => $user->lang['lastraid'],
    'BUTTON_NAME'  => 'delete',
    'BUTTON_VALUE' => $user->lang['delete_selected_members'],
    
    'O_NAME'       => $current_order['uri'][0],
    'O_RANK'       => $current_order['uri'][8],
    'O_LEVEL'      => $current_order['uri'][6],
    'O_CLASS'      => $current_order['uri'][7],
    'O_ARMOR'      => $current_order['uri'][9],
    'O_EARNED'     => $current_order['uri'][1],
    'O_SPENT'      => $current_order['uri'][2],
    'O_ADJUSTMENT' => $current_order['uri'][3],
    'O_CURRENT'    => $current_order['uri'][4],
    'O_LASTRAID'   => $current_order['uri'][5],
    
    'U_LIST_MEMBERS' => path_default('admin/manage_members.php') . path_params('mode', 'list') . '&amp;',
    
    'S_COMPARE' => false,
    'S_NOTMM'   => false,
    
    'LISTMEMBERS_FOOTCOUNT' => $footcount_text
));

$eqdkp->set_vars(array(
    'page_title'    => page_title($user->lang['listmembers_title']),
    'template_file' => 'listmembers.html',
    'display'       => true
));