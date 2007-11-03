<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        summary.php
 * Began:       Sat Dec 21 2002
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
require_once($eqdkp_root_path . 'common.php');

$user->check_auth('u_raid_list');

//
// Build the from/to GET vars to pass back to the script
//
if ( $in->get('submit') == $user->lang['create_news_summary'] )
{
    $fv = new Form_Validate();
    $fv->is_number(array(
        'mo1' => '',
        'd1'  => '',
        'y1'  => '',
        'mo2' => '',
        'd2'  => '',
        'y2'  => ''
    ));
    
    // Kick 'em back to the start if there was an error from above
    if ( $fv->is_error() )
    {
        header('Location: ' . path_default('summary.php'));
    }
    else
    {
        // Make the dates into yyyy-mm-dd and add them to the URI,
        // then redirect back to the script
        $from = $in->get('y1', 0) . '-' . $in->get('mo1', 0) . '-' . $in->get('d1', 0);
        $to   = $in->get('y2', 0) . '-' . $in->get('mo2', 0) . '-' . $in->get('d2', 0);
        header("Location: summary.php{$SID}&from={$from}&to={$to}");
    }
}
//
// Display summary
//
elseif ( preg_match('/^\d{2,4}-\d{1,2}-\d{1,2}$/', $in->get('from')) 
    &&   preg_match('/^\d{2,4}-\d{1,2}-\d{1,2}$/', $in->get('to')) )
{
    $s_step1 = false;
    
    $from = strtotime($in->get('from'));
    $to   = strtotime($in->get('to')) + 86400; // Includes raids/items ON that day
    
    if ( $from > 0 && $to > 0 )
    {
        // Get the current active members.  Used to find out the percentage of
        // active members present on each raid
        $active_members = $db->query_first("SELECT COUNT(member_id) FROM __members WHERE (`member_firstraid` BETWEEN {$from} AND {$to})");
        
        // Build the raids
        $raids    = array();
        $drops    = array();
        $raid_ids = array();
        
        $sql = "SELECT r.raid_id, r.raid_name, r.raid_date, r.raid_note,
                    r.raid_value, COUNT(ra.raid_id) AS attendee_count 
                FROM __raids AS r, __raid_attendees AS ra
                WHERE (ra.raid_id = r.raid_id)
                AND (r.`raid_date` BETWEEN {$from} AND {$to})
                GROUP BY r.raid_id
                ORDER BY r.raid_date DESC";
        if ( !($raids_result = $db->query($sql)) )
        {
            message_die('Could not obtain raid information', '', __FILE__, __LINE__, $sql);
        }
        
        if ( !$db->num_rows($raids_result) )
        {
            // TODO: Localize?
            message_die('No raids occurred between ' . date('n/j/Y', $from) . ' and ' . date('n/j/Y', $to - 86400));
        }
        
        while ( $row = $db->fetch_record($raids_result) )
        {
            $raids[ $row['raid_id'] ] = array(
                'raid_id'        => $row['raid_id'],
                'raid_name'      => $row['raid_name'],
                'raid_date'      => $row['raid_date'],
                'raid_note'      => $row['raid_note'],
                'raid_value'     => $row['raid_value'],
                'attendee_count' => $row['attendee_count']);
                
            $raid_ids[] = intval($row['raid_id']);
        }
        $db->free_result($raids_result);
        
        // Find the item drops for each raid
        $sql = "SELECT raid_id, COUNT(item_id) AS count 
                FROM __items
                WHERE (`raid_id` IN (" . $db->escape(',', $raid_ids) . "))
                GROUP BY raid_id";
        $result = $db->query($sql);
        
        while ( $row = $db->fetch_record($result) )
        {
            $drops[ $row['raid_id'] ] = $row['count'];
        }
        $db->free_result($result);
        
        foreach ( $raids as $raid_id => $row )
        {
            $raid_drops = ( isset($drops[ $row['raid_id'] ]) ) ? $drops[ $row['raid_id'] ] : 0;
            
            $attendees = $row['attendee_count'];
            $attendees_percent = ( $active_members > 0 ) ? round(($attendees / $active_members) * 100) : '0';
            
            $tpl->assign_block_vars('raids_row', array(
                'ROW_CLASS'       => $eqdkp->switch_row_class(),
                'DATE'            => ( !empty($row['raid_date']) ) ? date($user->style['date_notime_short'], $row['raid_date']) : '&nbsp;',
                'U_VIEW_RAID'     => raid_path($row['raid_id']),
                'NAME'            => sanitize($row['raid_name']),
                'NOTE'            => sanitize($row['raid_note']),
                'ATTENDEES'       => intval($attendees),
                'ATTENDEES_PCT'   => sprintf("%d%%", $attendees_percent),
                'ITEMS'           => intval($raid_drops),
                'VALUE'           => number_format($row['raid_value'], 2),
                'C_ATTENDEES_PCT' => color_item($attendees_percent, true)
            ));
        }
        
        // Build the raid array. Contains total raids, total earned
        $sql = "SELECT COUNT(raid_id) AS total_raids, SUM(raid_value) AS total_earned
                FROM __raids
                WHERE (`raid_date` BETWEEN {$from} AND {$to})";
        $raid_total_result     = $db->query($sql);
        $raids                 = $db->fetch_record($raid_total_result);
        $raids['total_earned'] = ( isset($raids['total_earned']) ) ? $raids['total_earned'] : 0.00;
        $db->free_result($raid_total_result);
        
        // Build the drops array. Contains total drops, total spent
        $sql = "SELECT COUNT(item_id) AS total_drops, SUM(item_value) AS total_spent
                FROM __items
                WHERE (`item_date` BETWEEN {$from} AND {$to})
                AND (item_value != 0.00)";
        $drop_total_result    = $db->query($sql);
        $drops                = $db->fetch_record($drop_total_result);
        $drops['total_spent'] = ( isset($drops['total_spent']) ) ? $drops['total_spent'] : 0.00;
        $db->free_result($drop_total_result);
        
        // Class Summary
        // Classes array - if an element is false, that class has gotten no
        // loot and won't show up from the SQL query
        // Otherwise it contains an array with the SQL data
        // New for 1.3 - grab class info from database
        
        // TODO: It's from 1.3, so it needs to be fixed (of course!)
        $eq_classes = array();

        // Find the total members existing before this date to get overall class percentage
        $sql = "SELECT COUNT(member_id)
                FROM __members
                WHERE (`member_firstraid` BETWEEN {$from} AND {$to})
                AND (member_class_id > 0)";
        $total_members = $db->query_first($sql);
        
        // Find out how many members of each class exist
        $class_counts = array();
        $sql = "SELECT c.class_name AS member_class, COUNT(m.member_id) AS class_count
                FROM __members AS m, __classes AS c
                WHERE (m.`member_firstraid` BETWEEN {$from} AND {$to})
                AND (c.class_id = m.member_class_id)
                GROUP BY member_class";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $class_counts[ $row['member_class'] ] = $row['class_count'];
        }
        $db->free_result($result);

        // Query finds all items purchased by each class between these dates
        // Will not find items that are unpriced, will not find members that don't have a class defined
        $sql = "SELECT c.class_name AS member_class, COUNT(i.item_id) AS class_drops
                FROM __items AS i, __members AS m, __classes AS c
                WHERE (m.member_name = i.item_buyer)
                AND (i.item_value != 0.00)
                AND (m.member_class_id > 0)
                AND (c.class_id = m.member_class_id)
                AND (i.`item_date` BETWEEN {$from} AND {$to})
                GROUP BY m.member_class_id";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $class          = $row['member_class'];
            $class_drops    = $row['class_drops'];
            $class_drop_pct = ( $drops['total_drops'] > 0 ) ? round(($class_drops / $drops['total_drops']) * 100) : 0;
            $class_members  = ( isset($class_counts[$class]) ) ? $class_counts[$class] : 0;
            
            $eq_classes[$class] = array(
                'drops'       => $class_drops,
                'drop_pct'    => $class_drop_pct,
                'class_count' => $class_members,
                'class_pct'   => ( $total_members > 0 ) ? round(($class_members / $total_members) * 100) : 0,
                'factor'      => 0
            );
        }
        $db->free_result($result);
        
        foreach ( $eq_classes as $k => $v )
        {
            // If v's an array, we have data for this class
            // e.g., they looted something in this time period
            if ( !is_array($v) )
            {
                // We still need to find out how many of the class existed
                $sql = "SELECT COUNT(member_id) 
                        FROM __members
                        WHERE (`member_class` = '" . $db->escape($k) . "')
                        AND (`member_firstraid` BETWEEN {$from} AND {$to})";
                $class_members = $db->query_first($sql);
                $class_factor  = 0;
                
                $v = array(
                    'drops'       => 0,
                    'drop_pct'    => 0,
                    'class_count' => $class_members,
                    'class_pct'   => ( $total_members > 0 ) ? round(($class_members / $total_members) * 100) : 0,
                    'factor'      => $class_factor
                );
            }
            
            $loot_factor = ( $v['class_pct'] > 0 ) ? round((($v['drop_pct'] / $v['class_pct']) - 1) * 100) : '0';
            
            $tpl->assign_block_vars('class_row', array(
                'ROW_CLASS'      => $eqdkp->switch_row_class(),
                'U_LIST_MEMBERS' => member_path() . path_params('filter', $k),
                'CLASS'          => sanitize($k),
                'LOOT_COUNT'     => intval($v['drops']),
                'LOOT_PCT'       => sprintf("%d%%", intval($v['drop_pct'])),
                'CLASS_COUNT'    => intval($v['class_count']),
                'CLASS_PCT'      => sprintf("%d%%", intval($v['class_pct'])),
                'LOOT_FACTOR'    => sprintf("%d%%", intval($loot_factor)),
                'C_LOOT_FACTOR'  => color_item($loot_factor)
            ));
        }

        $tpl->assign_vars(array(
            'L_SUMMARY_DATES' => sprintf($user->lang['summary_dates'], date('Y-n-j', $from), date('Y-n-j', $to - 86400)),
            'L_CLASS_SUMMARY' => sprintf($user->lang['class_summary'], date('Y-n-j', $from), date('Y-n-j', $to - 86400)),
            'L_LOOTS'         => $user->lang['loots'],
            'L_MEMBERS'       => $user->lang['members'],
            'L_LOOT_FACTOR'   => $user->lang['loot_factor'],
            
            'TOTAL_RAIDS'  => intval($raids['total_raids']),
            'TOTAL_ITEMS'  => intval($drops['total_drops']),
            'TOTAL_EARNED' => intval($raids['total_earned']),
            'TOTAL_SPENT'  => intval($drops['total_spent']),
        ));
    }
    else
    {
        header('Location: summary.php');
    }
}
else
{
    $s_step1 = true;
}

$tpl->assign_vars(array(
    'S_STEP1' => $s_step1,

    'L_ENTER_DATES'         => $user->lang['enter_dates'],
    'L_STARTING_DATE'       => $user->lang['starting_date'],
    'L_ENDING_DATE'         => $user->lang['ending_date'],
    'L_CREATE_NEWS_SUMMARY' => $user->lang['create_news_summary'],
    'L_TOTAL_RAIDS'         => $user->lang['total_raids'],
    'L_TOTAL_ITEMS'         => $user->lang['total_items'],
    'L_TOTAL_EARNED'        => $user->lang['total_earned'],
    'L_TOTAL_SPENT'         => $user->lang['total_spent'],
    'L_DATE'                => $user->lang['date'],
    'L_EVENT'               => $user->lang['event'],
    'L_NOTE'                => $user->lang['note'],
    'L_ATTENDEES'           => $user->lang['attendees'],
    'L_ITEMS'               => $user->lang['items'],
    'L_VALUE'               => $user->lang['value'],
    
    'MO2' => date('m'),
    'D2'  => date('d'),
    'Y2'  => date('Y')
));

$eqdkp->set_vars(array(
    'page_title'    => page_title($user->lang['summary_title']),
    'template_file' => 'summary.html',
    'display'       => true
));