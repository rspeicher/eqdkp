<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        viewmember.php
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

$user->check_auth('u_member_view');

if ( $in->get(URI_NAME) != '' )
{
    $sort_order = array(
        0 => array('raid_name', 'raid_name desc'),
        1 => array('raid_count desc', 'raid_count')
    );

    $current_order = switch_order($sort_order);

    $sql = "SELECT member_id, member_name, member_earned, member_spent, member_adjustment, 
                (member_earned-member_spent+member_adjustment) AS member_current,
                member_firstraid, member_lastraid
            FROM __members
            WHERE (`member_name` = '" . $db->escape($in->get(URI_NAME)) . "')";

    if ( !($member_result = $db->query($sql)) )
    {
        message_die('Could not obtain member information', '', __FILE__, __LINE__, $sql);
    }

    // Make sure they provided a valid member name
    if ( !$member = $db->fetch_record($member_result) )
    {
        message_die($user->lang['error_invalid_name_provided']);
    }

    // Find the percent of raids they've attended in the last 30, 60 and 90 days
    $percent_of_raids = array(
        '30'       => raid_count(mktime(0, 0, 0, date('m'), date('d')-30, date('Y')), time(), $member['member_name']),
        '60'       => raid_count(mktime(0, 0, 0, date('m'), date('d')-60, date('Y')), time(), $member['member_name']),
        '90'       => raid_count(mktime(0, 0, 0, date('m'), date('d')-90, date('Y')), time(), $member['member_name']),
        'lifetime' => raid_count($member['member_firstraid'], $member['member_lastraid'], $member['member_name'])
    );

    //
    // Raid Attendance
    //
    $rstart = $in->get('rstart', 0);

    // Find $current_earned based on the page.  This prevents us having to pass the
    // current earned as a GET variable which could result in user error
    if ( $rstart == 0 )
    {
        $current_earned = $member['member_earned'];
    }
    else
    {
        $current_earned = $member['member_earned'];
        $sql = "SELECT raid_value
                FROM __raids AS r, __raid_attendees AS ra
                WHERE (ra.raid_id = r.raid_id)
                AND (ra.`member_name` = '{$member['member_name']}')
                ORDER BY r.raid_date DESC
                LIMIT {$rstart}";
        if ( !($earned_result = $db->query($sql)) )
        {
            message_die('Could not obtain raid information', '', __FILE__, __LINE__, $sql);
        }
        while ( $ce_row = $db->fetch_record($earned_result) )
        {
            $current_earned -= $ce_row['raid_value'];
        }
        $db->free_result($earned_result);
    }

    $sql = "SELECT r.raid_id, r.raid_name, r.raid_date, r.raid_note, r.raid_value
            FROM __raids AS r, __raid_attendees AS ra
            WHERE (ra.raid_id = r.raid_id)
            AND (ra.`member_name` = '{$member['member_name']}')
            ORDER BY r.raid_date DESC
            LIMIT {$rstart},{$user->data['user_rlimit']}";
    if ( !($raids_result = $db->query($sql)) )
    {
        message_die('Could not obtain raid information', '', __FILE__, __LINE__, $sql);
    }
    while ( $raid = $db->fetch_record($raids_result) )
    {
        $tpl->assign_block_vars('raids_row', array(
            'ROW_CLASS'      => $eqdkp->switch_row_class(),
            'DATE'           => ( !empty($raid['raid_date']) ) ? date($user->style['date_notime_short'], $raid['raid_date']) : '&nbsp;',
            'U_VIEW_RAID'    => 'viewraid.php'.$SID.'&amp;' . URI_RAID . '='.$raid['raid_id'],
            'NAME'           => ( !empty($raid['raid_name']) ) ? sanitize($raid['raid_name']) : '&lt;<i>Not Found</i>&gt;',
            'NOTE'           => ( !empty($raid['raid_note']) ) ? sanitize($raid['raid_note']) : '&nbsp;',
            'EARNED'         => $raid['raid_value'],
            'CURRENT_EARNED' => sprintf("%.2f", $current_earned)
        ));
        $current_earned -= $raid['raid_value'];
    }
    $db->free_result($raids_result);
    $sql = "SELECT count(*)
            FROM __raids AS r, __raid_attendees AS ra
            WHERE (ra.raid_id = r.raid_id)
            AND (ra.`member_name` = '{$member['member_name']}')";
    $total_attended_raids = $db->query_first($sql);

    //
    // Item Purchase History
    //
    $istart = $in->get('istart', 0);

    if ( $istart == 0 )
    {
        $current_spent = $member['member_spent'];
    }
    else
    {
        $current_spent = $member['member_spent'];
        $sql = "SELECT item_value
                FROM __items
                WHERE (`item_buyer` = '{$member['member_name']}')
                ORDER BY item_date DESC
                LIMIT {$istart}";
        if ( !($spent_result = $db->query($sql)) )
        {
            message_die('Could not obtain item information', '', __FILE__, __LINE__, $sql);
        }
        while ( $cs_row = $db->fetch_record($spent_result) )
        {
            $current_spent -= $cs_row['item_value'];
        }
        $db->free_result($spent_result);
    }

    $sql = "SELECT i.item_id, i.item_name, i.item_value, i.item_date, i.raid_id, r.raid_name
            FROM __items AS i LEFT JOIN __raids AS r ON r.raid_id = i.raid_id
            WHERE (i.`item_buyer` = '{$member['member_name']}')
            ORDER BY i.item_date DESC
            LIMIT {$istart},{$user->data['user_ilimit']}";
    if ( !($items_result = $db->query($sql)) )
    {
        message_die('Could not obtain item information', 'Database error', __FILE__, __LINE__, $sql);
    }
    while ( $item = $db->fetch_record($items_result) )
    {
        $tpl->assign_block_vars('items_row', array(
            'ROW_CLASS'     => $eqdkp->switch_row_class(),
            'DATE'          => ( !empty($item['item_date']) ) ? date($user->style['date_notime_short'], $item['item_date']) : '&nbsp;',
            'U_VIEW_ITEM'   => 'viewitem.php'.$SID.'&amp;' . URI_ITEM . '=' . $item['item_id'],
            'U_VIEW_RAID'   => 'viewraid.php'.$SID.'&amp;' . URI_RAID . '=' . $item['raid_id'],
            'NAME'          => sanitize($item['item_name']),
            'RAID'          => ( !empty($item['raid_name']) ) ? sanitize($item['raid_name']) : '&lt;<i>Not Found</i>&gt;',
            'SPENT'         => number_format($item['item_value'], 2),
            'CURRENT_SPENT' => number_format($current_spent, 2)
        ));
        $current_spent -= $item['item_value'];
    }
    $db->free_result($items_result);

    $total_purchased_items = $db->query_first("SELECT count(*) FROM __items WHERE (`item_buyer` = '{$member['member_name']}') ORDER BY item_date DESC");

    //
    // Adjustment History
    //
    $sql = "SELECT adjustment_value, adjustment_date, adjustment_reason, member_name
            FROM __adjustments
            WHERE (`member_name` = '{$member['member_name']}')
            OR (member_name IS NULL AND adjustment_date >= {$member['member_firstraid']})
            ORDER BY adjustment_date DESC";
    if ( !($adjustments_result = $db->query($sql)) )
    {
        message_die('Could not obtain adjustment information', '', __FILE__, __LINE__, $sql);
    }
    while ( $adjustment = $db->fetch_record($adjustments_result) )
    {
        $reason = ( is_null($adjustment['member_name']) ) ? $user->lang['group_adjustments'] : sanitize($adjustment['adjustment_reason']);
        
        $tpl->assign_block_vars('adjustments_row', array(
            'ROW_CLASS'               => $eqdkp->switch_row_class(),
            'DATE'                    => ( !empty($adjustment['adjustment_date']) ) ? date($user->style['date_notime_short'], $adjustment['adjustment_date']) : '&nbsp;',
            'REASON'                  => $reason,
            'C_INDIVIDUAL_ADJUSTMENT' => color_item($adjustment['adjustment_value']),
            'INDIVIDUAL_ADJUSTMENT'   => sanitize($adjustment['adjustment_value'])
        ));
    }

    //
    // Attendance by Event
    //
    $raid_counts = array();

    // Find the count for each event for this member
    $sql = "SELECT e.event_id, r.raid_name, count(ra.raid_id) AS raid_count
            FROM __events AS e, __raid_attendees AS ra, __raids AS r
            WHERE (e.event_name = r.raid_name)
            AND (r.raid_id = ra.raid_id)
            AND (ra.`member_name` = '{$member['member_name']}')
            AND (r.`raid_date` >= {$member['member_firstraid']})
            GROUP BY ra.member_name, r.raid_name";
    $result = $db->query($sql);
    while ( $row = $db->fetch_record($result) )
    {
        // The count now becomes the percent
        $raid_counts[ $row['raid_name'] ] = $row['raid_count'];

        $event_ids[ $row['raid_name'] ] = $row['event_id'];
    }
    $db->free_result($result);

    // Find the count for reach raid
    $sql = "SELECT raid_name, count(raid_id) AS raid_count
            FROM __raids
            WHERE (`raid_date` >= {$member['member_firstraid']})
            GROUP BY raid_name";
    $result = $db->query($sql);
    while ( $row = $db->fetch_record($result) )
    {
        if ( isset($raid_counts[$row['raid_name']]) )
        {
            $percent = round(($raid_counts[ $row['raid_name'] ] / $row['raid_count']) * 100);
            $raid_counts[$row['raid_name']] = array('percent' => $percent, 'count' => $raid_counts[ $row['raid_name'] ]);

            unset($percent);
        }
    }
    $db->free_result($result);

    // Since we can't sort in SQL for this case, we have to sort
    // by the array
    switch ( $current_order['sql'] )
    {
        // Sort by key
        case 'raid_name':
            ksort($raid_counts);
            break;
        case 'raid_name desc':
            krsort($raid_counts);
            break;

        // Sort by value (keeping relational keys in-tact)
        case 'raid_count':
            asort($raid_counts);
            break;
        case 'raid_count desc':
            arsort($raid_counts);
            break;
    }
    reset($raid_counts);
    foreach ( $raid_counts as $event => $data )
    {
        $tpl->assign_block_vars('event_row', array(
            'EVENT'        => sanitize($event),
            'U_VIEW_EVENT' => 'viewevent.php' . $SID . '&' . URI_EVENT . '=' . $event_ids[$event],
            'BAR'          => create_bar($data['percent'], $data['count'] . ' (' . $data['percent'] . '%)')
        ));
    }
    unset($raid_counts, $event_ids);

    $tpl->assign_vars(array(
        'GUILDTAG' => $eqdkp->config['guildtag'],
        'NAME'     => sanitize($member['member_name']),

        'L_EARNED'                        => $user->lang['earned'],
        'L_SPENT'                         => $user->lang['spent'],
        'L_ADJUSTMENT'                    => $user->lang['adjustment'],
        'L_CURRENT'                       => $user->lang['current'],
        'L_RAIDS_30_DAYS'                 => sprintf($user->lang['raids_x_days'], 30),
        'L_RAIDS_60_DAYS'                 => sprintf($user->lang['raids_x_days'], 60),
        'L_RAIDS_90_DAYS'                 => sprintf($user->lang['raids_x_days'], 90),
        'L_RAIDS_LIFETIME'                => sprintf($user->lang['raids_lifetime'],
                                                date($user->style['date_notime_short'], $member['member_firstraid']),
                                                date($user->style['date_notime_short'], $member['member_lastraid'])),
        'L_RAID_ATTENDANCE_HISTORY'       => $user->lang['raid_attendance_history'],
        'L_DATE'                          => $user->lang['date'],
        'L_NAME'                          => $user->lang['name'],
        'L_NOTE'                          => $user->lang['note'],
        'L_EARNED'                        => $user->lang['earned'],
        'L_CURRENT'                       => $user->lang['current'],
        'L_ITEM_PURCHASE_HISTORY'         => $user->lang['item_purchase_history'],
        'L_RAID'                          => $user->lang['raid'],
        'L_INDIVIDUAL_ADJUSTMENT_HISTORY' => $user->lang['individual_adjustment_history'],
        'L_REASON'                        => $user->lang['reason'],
        'L_ADJUSTMENT'                    => $user->lang['adjustment'],
        'L_ATTENDANCE_BY_EVENT'           => $user->lang['attendance_by_event'],
        'L_EVENT'                         => $user->lang['event'],
        'L_PERCENT'                       => $user->lang['percent'],

        'O_EVENT'   => $current_order['uri'][0],
        'O_PERCENT' => $current_order['uri'][1],

        'EARNED'         => $member['member_earned'],
        'SPENT'          => $member['member_spent'],
        'ADJUSTMENT'     => $member['member_adjustment'],
        'CURRENT'        => number_format($member['member_current'], 2),
        'RAIDS_30_DAYS'  => sprintf($user->lang['of_raids'], $percent_of_raids['30']),
        'RAIDS_60_DAYS'  => sprintf($user->lang['of_raids'], $percent_of_raids['60']),
        'RAIDS_90_DAYS'  => sprintf($user->lang['of_raids'], $percent_of_raids['90']),
        'RAIDS_LIFETIME' => sprintf($user->lang['of_raids'], $percent_of_raids['lifetime']),

        'C_ADJUSTMENT'     => color_item($member['member_adjustment']),
        'C_CURRENT'        => color_item($member['member_current']),
        'C_RAIDS_30_DAYS'  => color_item($percent_of_raids['30'], true),
        'C_RAIDS_60_DAYS'  => color_item($percent_of_raids['60'], true),
        'C_RAIDS_90_DAYS'  => color_item($percent_of_raids['90'], true),
        'C_RAIDS_LIFETIME' => color_item($percent_of_raids['lifetime'], true),

        'RAID_FOOTCOUNT'       => sprintf($user->lang['viewmember_raid_footcount'], $total_attended_raids, $user->data['user_rlimit']),
        'RAID_PAGINATION'      => generate_pagination('viewmember.php'.$SID.'&amp;name='.$member['member_name'].'&amp;istart='.$istart, $total_attended_raids, $user->data['user_rlimit'], $rstart, 'rstart'),
        'ITEM_FOOTCOUNT'       => sprintf($user->lang['viewmember_item_footcount'], $total_purchased_items, $user->data['user_ilimit']),
        'ITEM_PAGINATION'      => generate_pagination('viewmember.php'.$SID.'&amp;name='.$member['member_name'].'&amp;rstart='.$rstart, $total_purchased_items, $user->data['user_ilimit'], $istart, 'istart'),
        'ADJUSTMENT_FOOTCOUNT' => sprintf($user->lang['viewmember_adjustment_footcount'], $db->num_rows($adjustments_result)),

        'U_VIEW_MEMBER' => 'viewmember.php' . $SID . '&amp;' . URI_NAME . '=' . $member['member_name'] . '&amp;')
    );

    $db->free_result($adjustments_result);

    $pm->do_hooks('/viewmember.php');

    $eqdkp->set_vars(array(
        'page_title'    => page_title(sprintf($user->lang['viewmember_title'], $member['member_name'])),
        'template_file' => 'viewmember.html',
        'display'       => true
    ));
}
else
{
    message_die($user->lang['error_invalid_name_provided']);
}

function raid_count($start_date, $end_date, $member_name)
{
    global $db;
    
    $member_name = $db->escape($member_name);
    $start_date  = intval($start_date);
    $end_date    = intval($end_date);

    $raid_count = $db->query_first("SELECT count(*) FROM __raids WHERE (raid_date BETWEEN {$start_date} AND {$end_date})");

    $sql = "SELECT count(*)
            FROM __raids AS r, __raid_attendees AS ra
            WHERE (ra.`raid_id` = r.`raid_id`)
            AND (ra.`member_name` = '{$member_name}')
            AND (r.`raid_date` BETWEEN {$start_date} AND {$end_date})";
    $individual_raid_count = $db->query_first($sql);

    $percent_of_raids = ( $raid_count > 0 ) ? round(($individual_raid_count / $raid_count) * 100) : 0;
    
    return $percent_of_raids;
}