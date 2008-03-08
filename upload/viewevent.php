<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        viewevent.php
 * Began:       Fri Dec 20 2002
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2008 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */

define('EQDKP_INC', true);
$eqdkp_root_path = './';
require_once($eqdkp_root_path . 'common.php');

$user->check_auth('u_event_view');

if ( $in->get(URI_EVENT, 0) )
{
    $sort_order = array(
        0 => array('raid_date desc', 'raid_date'),
        1 => array('raid_note', 'raid_note desc'),
        2 => array('raid_value desc', 'raid_value')
    );
     
    $current_order = switch_order($sort_order);

    $sql = "SELECT event_id, event_name, event_value, event_added_by, event_updated_by
            FROM __events
            WHERE (`event_id` = '" . $in->get(URI_EVENT, 0) . "')";
            
    if ( !($event_result = $db->query($sql)) )
    {
        message_die('Could not obtain event information', '', __FILE__, __LINE__, $sql);
    }
    
    // Check for a valid event
    if ( !$event = $db->fetch_record($event_result) )
    {
        message_die($user->lang['error_invalid_event_provided']);
    }
    
    // Init vars used to get averages and totals
    $total_drop_count      = 0;
    $total_attendees_count = 0;
    $total_earned          = 0;
    $total_items           = 0;
    
    // Reduce queries
    $raids     = array();
    $raid_ids  = array();
    $items     = array();
    $attendees = array();
    
    // Find the raids for this event
    $sql = "SELECT raid_id, raid_date, raid_note, raid_value 
            FROM __raids
            WHERE (`raid_name` = '" . $db->escape($event['event_name']) . "')
            ORDER BY {$current_order['sql']}";
    $result = $db->query($sql);
    
    while ( $row = $db->fetch_record($result) )
    {
        $raids[ $row['raid_id'] ] = array(
            'raid_id'    => $row['raid_id'],
            'raid_date'  => $row['raid_date'],
            'raid_note'  => $row['raid_note'],
            'raid_value' => $row['raid_value']
        );
        $raid_ids[] = intval($row['raid_id']);
    }
    $db->free_result($result);
    
    $raid_ids_in = $db->escape(',', $raid_ids);
    
    // Find the item drops for each raid
    if ( count($raid_ids) > 0 )
    {
        $sql = "SELECT raid_id, COUNT(item_id) AS count 
                FROM __items
                WHERE (raid_id IN ({$raid_ids_in}))
                GROUP BY raid_id";
        $result = $db->query($sql);
    
        while ( $row = $db->fetch_record($result) )
        {
            $items[ $row['raid_id'] ] = intval($row['count']);
        }
        $db->free_result($result);
    
        // Find the number of attendees at each raid
        $sql = "SELECT raid_id, COUNT(member_name) AS count 
                FROM __raid_attendees
                WHERE (raid_id IN ({$raid_ids_in}))
                GROUP BY raid_id";
        $result = $db->query($sql);
    
        while ( $row = $db->fetch_record($result) )
        {
            $attendees[ $row['raid_id'] ] = intval($row['count']);
        }
        $db->free_result($result);
    }
    
    // Loop through the raids for this event
    $total_raid_count = sizeof($raids);
    foreach ( $raids as $raid_id => $raid )
    {
        $drop_count = ( isset($items[ $raid['raid_id'] ]) ) ? $items[ $raid['raid_id'] ] : '0';
        $attendees_count = ( isset($attendees[ $raid['raid_id'] ]) ) ? $attendees[ $raid['raid_id'] ] : '0';

        $tpl->assign_block_vars('raids_row', array(
            'ROW_CLASS'   => $eqdkp->switch_row_class(),
            'U_VIEW_RAID' => raid_path($raid['raid_id']),
            'DATE'        => ( !empty($raid['raid_id']) ) ? date($user->style['date_notime_short'], $raid['raid_date']) : '&nbsp;',
            'ATTENDEES'   => $attendees_count,
            'DROPS'       => $drop_count,
            'NOTE'        => ( !empty($raid['raid_note']) ) ? sanitize($raid['raid_note']) : '&nbsp;',
            'VALUE'       => number_format($raid['raid_value'], 2)
        ));
        
        // Add the values of this row to our totals
        $total_drop_count += $drop_count;
        $total_attendees_count += $attendees_count;
        $total_earned += $raid['raid_value'];
    }
 
    // Prevent div by 0
    $average_attendees = ( $total_raid_count > 0 ) ? floor($total_attendees_count / $total_raid_count) : '0';
    $average_drops     = ( $total_drop_count > 0 ) ? floor($total_drop_count / $total_raid_count)      : '0';
    
    //
    // Items
    //
    if ( count($raid_ids) > 0 )
    {
        $sql = "SELECT item_date, raid_id, item_name, item_buyer, item_id, item_value
                FROM __items
                WHERE (raid_id IN ({$raid_ids_in}))
                ORDER BY item_date DESC";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $tpl->assign_block_vars('items_row', array(
                'ROW_CLASS'     => $eqdkp->switch_row_class(),
                'DATE'          => date($user->style['date_notime_short'], $row['item_date']),
                'U_VIEW_RAID'   => raid_path($row['raid_id']),
                'BUYER'         => sanitize($row['item_buyer']),
                'U_VIEW_MEMBER' => member_path($row['item_buyer']),
                'NAME'          => sanitize($row['item_name']),
                'U_VIEW_ITEM'   => item_path($row['item_id']),
                'SPENT'         => number_format($row['item_value'], 2)
            ));
        }
        $total_items = $db->num_rows($result);
        $db->free_result($result);
    }
    
    $tpl->assign_vars(array(
        'L_RECORDED_RAID_HISTORY' => sprintf($user->lang['recorded_raid_history'], sanitize($event['event_name'])),
        'L_ADDED_BY'              => $user->lang['added_by'],
        'L_UPDATED_BY'            => $user->lang['updated_by'],
        'L_DATE'                  => $user->lang['date'],
        'L_ATTENDEES'             => $user->lang['attendees'],
        'L_DROPS'                 => $user->lang['drops'],
        'L_NOTE'                  => $user->lang['note'],
        'L_VALUE'                 => $user->lang['value'],
        'L_AVERAGE'               => $user->lang['average'],
        'L_TOTAL_EARNED'          => $user->lang['total_earned'],
        'L_ITEMS'                 => $user->lang['items'],
        'L_BUYER'                 => $user->lang['buyer'],
        'L_NAME'                  => $user->lang['name'],
        'L_SPENT'                 => $user->lang['spent'],
        
        'O_DATE'  => $current_order['uri'][0],
        'O_NOTE'  => $current_order['uri'][1],
        'O_VALUE' => $current_order['uri'][2],
        
        'U_VIEW_EVENT' => event_path($in->get(URI_EVENT, 0)) . '&amp;',
        
        'EVENT_ADDED_BY'      => ( !empty($event['event_added_by']) ) ? sanitize($event['event_added_by']) : 'N/A',
        'EVENT_UPDATED_BY'    => ( !empty($event['event_updated_by']) ) ? sanitize($event['event_updated_by']) : 'N/A',
        'ROW_CLASS'           => $eqdkp->switch_row_class(),
        'AVERAGE_ATTENDEES'   => $average_attendees,
        'AVERAGE_DROPS'       => $average_drops,
        'TOTAL_EARNED'        => number_format($total_earned, 2),
        'VIEWEVENT_FOOTCOUNT' => sprintf($user->lang['viewevent_footcount'], $total_raid_count),
        'ITEM_FOOTCOUNT'      => sprintf($user->lang['viewitem_footcount'], $total_items, $total_items))
    );
    
    $eqdkp->set_vars(array(
        'page_title'    => page_title(sprintf($user->lang['viewevent_title'], $event['event_name'])),
        'template_file' => 'viewevent.html',
        'display'       => true
    ));
}
else
{
    message_die($user->lang['error_invalid_event_provided']);
}