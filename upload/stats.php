<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        stats.php
 * Began:       Sat Dec 21 2002
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

$user->check_auth('u_member_list');

$sort_order = array(
     0 => array('member_name', 'member_name desc'),
     1 => array('member_firstraid', 'member_firstraid desc'),
     2 => array('member_lastraid', 'member_lastraid desc'),
     3 => array('member_raidcount desc', 'member_raidcount'),
     4 => array('member_earned desc', 'member_earned'),
     5 => array('earned_per_day desc', 'earned_per_day'),
     6 => array('earned_per_raid desc', 'earned_per_raid'),
     7 => array('member_spent desc', 'member_spent'),
     8 => array('spent_per_day desc', 'spent_per_day'),
     9 => array('spent_per_raid desc', 'spent_per_raid'),
    10 => array('lost_to_adjustment desc', 'lost_to_adjustment'),
    11 => array('lost_to_spent desc', 'lost_to_spent'),
    12 => array('member_current desc', 'member_current')
);

$current_order = switch_order($sort_order);

$total_raids = $db->query_first("SELECT COUNT(*) FROM __raids");
$show_all = ( $in->get('show') == 'all' ) ? true : false;

// No idea if this massive query will work outside MySQL...if not, we'll have
// to use a switch and get the values another way
$time = time();
$sql = "SELECT member_name, member_earned, member_spent, member_adjustment,
            (member_earned-member_spent+member_adjustment) AS member_current,
            member_firstraid, member_lastraid, member_raidcount,
            ((member_spent/member_earned)*100) AS lost_to_spent,
            ((member_adjustment-(member_adjustment*2))/member_earned)*100 AS lost_to_adjustment,
            (member_earned / ((({$time} - member_firstraid)+86400) / 86400) ) AS earned_per_day,
            (({$time} - member_firstraid) / 86400) AS zero_check,
            member_spent / ((({$time} - member_firstraid)+86400) / 86400) AS spent_per_day,
            member_earned / member_raidcount AS earned_per_raid,
            member_spent / member_raidcount AS spent_per_raid,
            r.rank_prefix, r.rank_suffix
        FROM __members AS m 
        LEFT JOIN __member_ranks AS r ON m.member_rank_id = r.rank_id";

if ( ($eqdkp->config['hide_inactive'] == 1) && (!$show_all) )
{
    $sql .= " WHERE (`member_status` = '1')";
}
$sql .= " ORDER BY {$current_order['sql']}";

if ( !($members_result = $db->query($sql)) )
{
    message_die('Could not obtain member information', '', __FILE__, __LINE__, $sql);
}
while ( $row = $db->fetch_record($members_result) )
{
    // Default the values of these in case they have no earned or spent or
    // adjustment
    $row['earned_per_day']     = ( !empty($row['earned_per_day']) && $row['zero_check'] > '0.01' )
        ? $row['earned_per_day']     : 0.00;
    $row['earned_per_raid']    = ( !empty($row['earned_per_raid']) ) 
        ? $row['earned_per_raid']    : 0.00;
    $row['spent_per_day']      = ( !empty($row['spent_per_day'])  && $row['zero_check'] > '0.01' )
        ? $row['spent_per_day']      : 0.00;
    $row['spent_per_raid']     = ( !empty($row['spent_per_raid']) )
        ? $row['spent_per_raid']     : 0.00;
    $row['lost_to_adjustment'] = ( !empty($row['lost_to_adjustment']) )
        ? $row['lost_to_adjustment'] : 0.00;
    $row['lost_to_spent']      = ( !empty($row['lost_to_spent']) )
        ? $row['lost_to_spent']      : 0.00;

    // Find out how many days it's been since their first raid
    $days_since_start = 0;
    $days_since_start = round((time() - $row['member_firstraid']) / 86400);

    // Find the percentage of raids they've been on
    $attended_percent = ( $total_raids > 0 ) ? round(($row['member_raidcount'] / $total_raids) * 100) : 0;

    $tpl->assign_block_vars('stats_row', array(
        'ROW_CLASS'          => $eqdkp->switch_row_class(),
        'U_VIEW_MEMBER'      => member_path($row['member_name']),
        'NAME'               => $row['rank_prefix'] . sanitize($row['member_name']) . $row['rank_suffix'],
        'FIRST_RAID'         => ( !empty($row['member_firstraid']) ) ? date($user->style['date_notime_short'], $row['member_firstraid']) : '&nbsp;',
        'LAST_RAID'          => ( !empty($row['member_lastraid']) ) ? date($user->style['date_notime_short'], $row['member_lastraid']) : '&nbsp;',
        'ATTENDED_COUNT'     => intval($row['member_raidcount']),
        'C_ATTENDED_PERCENT' => color_item($attended_percent, true),
        'ATTENDED_PERCENT'   => $attended_percent,
        'EARNED_TOTAL'       => number_format($row['member_earned'], 2),
        'EARNED_PER_DAY'     => number_format($row['earned_per_day'], 2),
        'EARNED_PER_RAID'    => number_format($row['earned_per_raid'], 2),
        'SPENT_TOTAL'        => number_format($row['member_spent'], 2),
        'SPENT_PER_DAY'      => number_format($row['spent_per_day'], 2),
        'SPENT_PER_RAID'     => number_format($row['spent_per_raid'], 2),
        'LOST_TO_ADJUSTMENT' => number_format($row['lost_to_adjustment'], 2),
        'LOST_TO_SPENT'      => number_format($row['lost_to_spent'], 2),
        'C_CURRENT'          => color_item($row['member_current']),
        'CURRENT'            => number_format($row['member_current'], 2),
    ));
}

if ( ($eqdkp->config['hide_inactive'] == 1) && (!$show_all) )
{
    $path = path_default('stats.php') . path_params(array(
        URI_ORDER => $current_order['uri']['current'], 
        'show' => 'all'
    ));
    $footcount_text = sprintf($user->lang['stats_active_footcount'], $db->num_rows($members_result),
                              '<a href="' . $path . '" class="rowfoot">');
}
else
{
    $footcount_text = sprintf($user->lang['stats_footcount'], $db->num_rows($members_result));
}

## ############################################################################
## Class statistics
## ############################################################################

// Find the total members
$sql = "SELECT COUNT(member_id)
        FROM __members";
$total_members = $db->query_first($sql);

// Store drop data
$drop_data['total_drops'] = 0;
foreach ( $gm->sql_classes() as $cdata )
{
    $drop_data[$cdata['name']] = array(
        'drops'   => 0,
        'members' => 0,
        'factor'  => 0
    );
}

$sql = "SELECT c.class_name, COUNT(i.item_id) AS num_drops,
            COUNT(m.member_id) AS num_members
        FROM __items AS i, __classes AS c, __members AS m
        WHERE (m.member_name = i.item_buyer)
        AND (m.member_class_id = c.class_id)
        AND (i.item_value > 0)
        GROUP BY c.class_name";
$result = $db->query($sql);

while ( $row = $db->fetch_record($result) )
{
    $drop_data['total_drops'] += $row['num_drops'];
    
    $drop_data[$row['class_name']] = array(
        'drops'   => $row['num_drops'],
        'members' => $row['num_members'],
        'factor'  => ( $row['num_members'] > 0 ) 
            ? round(($row['num_drops'] / $row['num_members']) * 100) 
            : 0
    );
}
$db->free_result($result);

// Process data by class
$sql = "SELECT c.class_name, COUNT(m.member_id) as class_count
        FROM __members AS m, __classes AS c
        WHERE (m.`member_class_id` = c.`class_id`)
        AND (c.`class_name` IS NOT NULL)
        GROUP BY m.member_class_id
        ORDER BY c.class_name";
$result = $db->query($sql);

while ( $row = $db->fetch_record($result) )
{
    $class = $row['class_name'];
    $class_count = $row['class_count'];
    
    $row_class = ( $in->get('class') == $class ) ? 'rowhead' : $eqdkp->switch_row_class();
    
    $cdata = $drop_data[$row['class_name']];
    $cdata = array_merge($cdata, array(
        'class_pct' => ($total_members == 0) ? 0 : round(($class_count / $total_members) * 100),
        'drop_pct'  => ($drop_data['total_drops'] == 0) ? 0 : round(($cdata['drops'] / $drop_data['total_drops']) * 100),
    ));

    $loot_factor = ( $cdata['class_pct'] > 0 && $cdata['drop_pct'] > 0 ) ? round((($cdata['drop_pct'] / $cdata['class_pct']) - 1) * 100) : 0;

    $tpl->assign_block_vars('class_row', array(
        'ROW_CLASS'      => $row_class,
        'LINK_CLASS'     => ( $row_class == 'rowhead' ) ? 'header' : '',
        'U_LIST_MEMBERS' => member_path() . path_params('filter', $class),
        'CLASS'          => sanitize($class),
        'LOOT_COUNT'     => intval($cdata['drops']),
        'LOOT_PCT'       => sprintf("%d%%", $cdata['drop_pct']),
        'CLASS_COUNT'    => intval($class_count),
        'CLASS_PCT'      => sprintf("%d%%", $cdata['class_pct']),
        'LOOT_FACTOR'    => sprintf("%d%%", $loot_factor),
        'C_LOOT_FACTOR'  => color_item($loot_factor))
    );
}
$db->free_result($result);

$tpl->assign_vars(array(
    'L_NAME'               => $user->lang['name'],
    'L_RAIDS'              => $user->lang['raids'],
    'L_EARNED'             => $user->lang['earned'],
    'L_SPENT'              => $user->lang['spent'],
    'L_PCT_EARNED_LOST_TO' => $user->lang['pct_earned_lost_to'],
    'L_CURRENT'            => $user->lang['current'],
    'L_FIRST'              => $user->lang['first'],
    'L_LAST'               => $user->lang['last'],
    'L_ATTENDED'           => $user->lang['attended'],
    'L_TOTAL'              => $user->lang['total'],
    'L_PER_DAY'            => $user->lang['per_day'],
    'L_PER_RAID'           => $user->lang['per_raid'],
    'L_ADJUSTMENT'         => $user->lang['adjustment'],
    'L_CLASS'              => $user->lang['class'],
    'L_LOOTS'              => $user->lang['loots'],
    'L_MEMBERS'            => $user->lang['members'],
    'L_LOOT_FACTOR'        => $user->lang['loot_factor'],

    'O_NAME'               => $current_order['uri'][0],
    'O_FIRSTRAID'          => $current_order['uri'][1],
    'O_LASTRAID'           => $current_order['uri'][2],
    'O_RAIDCOUNT'          => $current_order['uri'][3],
    'O_EARNED'             => $current_order['uri'][4],
    'O_EARNED_PER_DAY'     => $current_order['uri'][5],
    'O_EARNED_PER_RAID'    => $current_order['uri'][6],
    'O_SPENT'              => $current_order['uri'][7],
    'O_SPENT_PER_DAY'      => $current_order['uri'][8],
    'O_SPENT_PER_RAID'     => $current_order['uri'][9],
    'O_LOST_TO_ADJUSTMENT' => $current_order['uri'][10],
    'O_LOST_TO_SPENT'      => $current_order['uri'][11],
    'O_CURRENT'            => $current_order['uri'][12],

    'U_STATS' => path_default('stats.php') . '&amp;',

    'SHOW' => ( $show_all ) ? 'all' : '',

    'STATS_FOOTCOUNT' => $footcount_text
));

$eqdkp->set_vars(array(
    'page_title'    => page_title(sprintf($user->lang['stats_title'], $eqdkp->config['dkp_name'])),
    'template_file' => 'stats.html',
    'display'       => true
));