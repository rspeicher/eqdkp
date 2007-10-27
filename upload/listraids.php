<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        listraids.php
 * Began:       Thu Dec 19 2002
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */

define('EQDKP_INC', true);
$eqdkp_root_path = './';
include_once($eqdkp_root_path . 'common.php');

$user->check_auth('u_raid_list');

$sort_order = array(
    0 => array('raid_date desc', 'raid_date'),
    1 => array('raid_name', 'raid_name desc'),
    2 => array('raid_note', 'raid_note desc'),
    3 => array('raid_value desc', 'raid_value')
);
 
$current_order = switch_order($sort_order);

$total_raids = $db->query_first("SELECT COUNT(*) FROM __raids");

$start = $in->get('start', 0);

$sql = "SELECT raid_id, raid_name, raid_date, raid_note, raid_value 
        FROM __raids
        ORDER BY {$current_order['sql']}
        LIMIT {$start},{$user->data['user_rlimit']}";
        
if ( !($raids_result = $db->query($sql)) )
{
    message_die('Could not obtain raid information', '', __FILE__, __LINE__, $sql);
}
while ( $row = $db->fetch_record($raids_result) )
{
    $tpl->assign_block_vars('raids_row', array(
        'ROW_CLASS'   => $eqdkp->switch_row_class(),
        'DATE'        => ( !empty($row['raid_date']) ) ? date($user->style['date_notime_short'], $row['raid_date']) : '&nbsp;',
        'U_VIEW_RAID' => raid_path($row['raid_id']),
        'NAME'        => ( !empty($row['raid_name']) ) ? sanitize($row['raid_name']) : '&lt;<i>Not Found</i>&gt;',
        'NOTE'        => ( !empty($row['raid_note']) ) ? sanitize($row['raid_note']) : '&nbsp;',
        'VALUE'       => number_format($row['raid_value'], 2),
    ));
}

$tpl->assign_vars(array(
    'L_DATE'  => $user->lang['date'],
    'L_NAME'  => $user->lang['name'],
    'L_NOTE'  => $user->lang['note'],
    'L_VALUE' => $user->lang['value'],
    
    'O_DATE'  => $current_order['uri'][0],
    'O_NAME'  => $current_order['uri'][1],
    'O_NOTE'  => $current_order['uri'][2],
    'O_VALUE' => $current_order['uri'][3],
    
    'U_LIST_RAIDS' => raid_path() . '&amp;',
    
    'START'               => $start,
    'LISTRAIDS_FOOTCOUNT' => sprintf($user->lang['listraids_footcount'], $total_raids, $user->data['user_rlimit']),
    'RAID_PAGINATION'     => generate_pagination(raid_path() . path_params(URI_ORDER, $current_order['uri']['current']), $total_raids, $user->data['user_rlimit'], $start)
));

$eqdkp->set_vars(array(
    'page_title'    => page_title($user->lang['listraids_title']),
    'template_file' => 'listraids.html',
    'display'       => true
));