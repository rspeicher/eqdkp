<?php
/******************************
 * EQdkp
 * Copyright 2002-2005
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * viewraid.php
 * Began: Thu December 19 2002
 *
 * $Id: viewraid.php 46 2007-06-19 07:29:11Z tsigo $
 *
 ******************************/

define('EQDKP_INC', true);
$eqdkp_root_path = './';
include_once($eqdkp_root_path . 'common.php');

$user->check_auth('u_raid_view');

if ( $in->get(URI_RAID, 0) )
{
    $sql = "SELECT raid_id, raid_name, raid_date, raid_note, raid_value, raid_added_by, raid_updated_by
            FROM __raids
            WHERE (`raid_id` = '" . $in->get(URI_RAID, 0) . "')";
    if ( !($raid_result = $db->query($sql)) )
    {
        message_die('Could not obtain raid information', '', __FILE__, __LINE__, $sql);
    }

    // Check for valid raid
    if ( !$raid = $db->fetch_record($raid_result) )
    {
        message_die($user->lang['error_invalid_raid_provided']);
    }
    $db->free_result($raid_result);

    //
    // Attendee and Class distribution
    //
    $attendees = array();
    $classes   = array();
    
    $sql = "SELECT ra.member_name, c.class_name AS member_class,
                CONCAT(r.rank_prefix, m.member_name, r.rank_suffix) AS member_sname
            FROM __raid_attendees AS ra, __members AS m
                LEFT JOIN __member_ranks AS r ON r.rank_id = m.member_rank_id
                LEFT JOIN __classes AS c ON c.class_id = m.member_class_id
            WHERE (m.member_name = ra.member_name)
            AND (`raid_id` = '{$raid['raid_id']}')
            ORDER BY member_name";
    $result = $db->query($sql);
    while ( $arow = $db->fetch_record($result) )
    {
        $attendees[] = array('name' => $arow['member_name'], 'styled' => $arow['member_sname']);
        $classes[ $arow['member_class'] ][] = $arow['member_sname'];
    }
    $db->free_result($result);
    $total_attendees = sizeof($attendees);

    if ( sizeof($attendees) > 0 )
    {
        $rows = ceil(sizeof($attendees) / $user->style['attendees_columns']);

        // First loop: iterate through the rows
        // Second loop: iterate through the columns as defined in template_config,
        // then "add" an array to $block_vars that contains the column definitions,
        // then assign the block vars.
        // Prevents one column from being assigned and the rest of the columns for
        // that row being blank
        for ( $i = 0; $i < $rows; $i++ )
        {
            $block_vars = array();
            for ( $j = 0; $j < $user->style['attendees_columns']; $j++ )
            {
                $offset = ($i + ($rows * $j));
                $attendee = ( isset($attendees[$offset]) ) ? $attendees[$offset] : '';

                if ( is_array($attendee) )
                {
                    $block_vars += array(
                        'COLUMN'.$j.'_NAME' => '<a href="viewmember.php' . $SID . '&amp;' . URI_NAME . '=' . $attendee['name'] . '">' . $attendee['styled'] . '</a>'
                    );
                }
                else
                {
                    $block_vars += array(
                        'COLUMN'.$j.'_NAME' => ''
                    );
                }

                // Are we showing this column?
                $s_column = 's_column'.$j;
                ${$s_column} = true;
            }
            $tpl->assign_block_vars('attendees_row', $block_vars);
        }
        $column_width = floor(100 / $user->style['attendees_columns']);
    }
    else
    {
        message_die('Could not get raid attendee information.','Critical Error');
    }

    //
    // Drops
    //
    $sql = "SELECT item_id, item_buyer, item_name, item_value
            FROM __items
            WHERE (`raid_id` = '{$raid['raid_id']}')";
    if ( !($items_result = $db->query($sql)) )
    {
        message_die('Could not obtain item information', '', __FILE__, __LINE__, $sql);
    }
    while ( $item = $db->fetch_record($items_result) )
    {
        $tpl->assign_block_vars('items_row', array(
            'ROW_CLASS'    => $eqdkp->switch_row_class(),
            'BUYER'        => $item['item_buyer'],
            'U_VIEW_BUYER' => 'viewmember.php' . $SID . '&amp;' . URI_NAME . '='.$item['item_buyer'],
            'NAME'         => sanitize($item['item_name']),
            'U_VIEW_ITEM'  => 'viewitem.php' . $SID . '&amp;' . URI_ITEM . '='.$item['item_id'],
            'VALUE'        => number_format($item['item_value'], 2)
        ));
    }

    //
    // Class distribution
    //
    ksort($classes);
    foreach ( $classes as $class => $members )
    {
        // TODO: We're potentially calling count() multiple times on the same class type, but it shouldn't be much overhead
        $class_count = count($classes[$class]);
        $percentage =  ( $total_attendees > 0 ) ? round(($class_count / $total_attendees) * 100) : 0;

        $tpl->assign_block_vars('class_row', array(
            'ROW_CLASS' => $eqdkp->switch_row_class(),
            'CLASS'     => $class,
            'BAR'       => create_bar($percentage, $class_count . ' (' . $percentage . '%)'),
            'ATTENDEES' => implode(', ', $members)
        ));
    }
    unset($classes);

    $tpl->assign_vars(array(
        'L_MEMBERS_PRESENT_AT' => sprintf($user->lang['members_present_at'], stripslashes($raid['raid_name']),
                                  date($user->style['date_notime_long'], $raid['raid_date'])),
        'L_ADDED_BY'           => $user->lang['added_by'],
        'L_UPDATED_BY'         => $user->lang['updated_by'],
        'L_NOTE'               => $user->lang['note'],
        'L_VALUE'              => $user->lang['value'],
        'L_DROPS'              => $user->lang['drops'],
        'L_BUYER'              => $user->lang['buyer'],
        'L_ITEM'               => $user->lang['item'],
        'L_SPENT'              => $user->lang['spent'],
        'L_ATTENDEES'          => $user->lang['attendees'],
        'L_CLASS_DISTRIBUTION' => $user->lang['class_distribution'],
        'L_CLASS'              => $user->lang['class'],
        'L_PERCENT'            => $user->lang['percent'],
        'L_RANK_DISTRIBUTION'  => $user->lang['rank_distribution'],
        'L_RANK'               => $user->lang['rank'],

        'S_COLUMN0' => ( isset($s_column0) ) ? true : false,
        'S_COLUMN1' => ( isset($s_column1) ) ? true : false,
        'S_COLUMN2' => ( isset($s_column2) ) ? true : false,
        'S_COLUMN3' => ( isset($s_column3) ) ? true : false,
        'S_COLUMN4' => ( isset($s_column4) ) ? true : false,
        'S_COLUMN5' => ( isset($s_column5) ) ? true : false,
        'S_COLUMN6' => ( isset($s_column6) ) ? true : false,
        'S_COLUMN7' => ( isset($s_column7) ) ? true : false,
        'S_COLUMN8' => ( isset($s_column8) ) ? true : false,
        'S_COLUMN9' => ( isset($s_column9) ) ? true : false,

        'COLUMN_WIDTH' => ( isset($column_width) ) ? $column_width : 0,
        'COLSPAN'      => $user->style['attendees_columns'],

        'RAID_ADDED_BY'       => ( !empty($raid['raid_added_by']) ) ? stripslashes($raid['raid_added_by']) : 'N/A',
        'RAID_UPDATED_BY'     => ( !empty($raid['raid_updated_by']) ) ? stripslashes($raid['raid_updated_by']) : 'N/A',
        'RAID_NOTE'           => ( !empty($raid['raid_note']) ) ? stripslashes($raid['raid_note']) : '&nbsp;',
        'DKP_NAME'            => $eqdkp->config['dkp_name'],
        'RAID_VALUE'          => $raid['raid_value'],
        'ATTENDEES_FOOTCOUNT' => sprintf($user->lang['viewraid_attendees_footcount'], sizeof($attendees)),
        'ITEM_FOOTCOUNT'      => sprintf($user->lang['viewraid_drops_footcount'], $db->num_rows($items_result)))
    );

    $eqdkp->set_vars(array(
        'page_title'    => page_title($user->lang['viewraid_title']),
        'template_file' => 'viewraid.html',
        'display'       => true)
    );
}
else
{
    message_die($user->lang['error_invalid_raid_provided']);
}