<?php
/******************************
 * EQdkp
 * Copyright 2002-2005
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * listmembers.php
 * begin: Wed December 18 2002
 * 
 * $Id$
 * 
 ******************************/
 
define('EQDKP_INC', true);
$eqdkp_root_path = './';
include_once($eqdkp_root_path . 'common.php');

$user->check_auth('u_member_list');

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
    9 => array('class_armor_type', 'class_armor_type desc')
);

$current_order = switch_order($sort_order);

//
// Compare members
//
// TODO: if-else causes two different pages to be rendered. Split into separate files.
if ( $in->get('submit') == $user->lang['compare_members'] && $in->get('compare_ids', false) )
{
    redirect('listmembers.php?compare=' . implode(',', $in->getArray('compare_ids', 'int')));
}
elseif ( $in->get('compare', false) )
{
    $s_compare = true;
    $uri_addon = '';

    $compare = validateCompareInput($in->get('compare'));
    
    // Find 30 days ago, then find how many raids occurred in those 30 days
    // Do the same for 60 and 90 days
    $thirty_days = mktime(0, 0, 0, date('m'), date('d')-30, date('Y'));
    $ninety_days = mktime(0, 0, 0, date('m'), date('d')-90, date('Y'));
    
    $raid_count_30 = $db->query_first("SELECT count(*) FROM __raids WHERE raid_date BETWEEN {$thirty_days} AND " . time());
    $raid_count_90 = $db->query_first("SELECT count(*) FROM __raids WHERE raid_date BETWEEN {$ninety_days} AND " . time());
    
    // Build an SQL query that includes each of the compare IDs
    $sql = "SELECT *, (member_earned-member_spent+member_adjustment) AS member_current, c.class_name AS member_class
            FROM __members AS m, __classes AS c
            WHERE (m.member_class_id = c.class_id)
            AND (member_id IN ({$compare}))
            ORDER BY {$current_order['sql']}";
    $result = $db->query($sql);

    // Output each row
    while ( $row = $db->fetch_record($result) )
    {
        $individual_raid_count_30 = 0;
        $individual_raid_count_90 = 0;
        
        $rc_sql = "SELECT count(*)
                   FROM __raids AS r, __raid_attendees AS ra
                   WHERE (ra.raid_id = r.raid_id)
                   AND (ra.`member_name` = '{$row['member_name']}')
                   AND (r.raid_date BETWEEN {$thirty_days} AND " . time() . ')';
        $individual_raid_count_30 = $db->query_first($rc_sql);
        
        $rc_sql = "SELECT count(*)
                   FROM __raids AS r, __raid_attendees AS ra
                   WHERE (ra.raid_id = r.raid_id)
                   AND (ra.`member_name` = '{$row['member_name']}')
                   AND (r.raid_date BETWEEN {$ninety_days} AND " . time() . ')';
        $individual_raid_count_90 = $db->query_first($rc_sql);
        
        // Prevent division by 0
        $percent_of_raids_30 = ( $raid_count_30 > 0 ) ? round(($individual_raid_count_30 / $raid_count_30) * 100) : 0;
        $percent_of_raids_90 = ( $raid_count_90 > 0 ) ? round(($individual_raid_count_90 / $raid_count_90) * 100) : 0;
        
        // If the member's spent is greater than 0, see how long ago they looted an item
        if ( $row['member_spent'] > 0 )
        {
            $ll_sql = "SELECT max(item_date) AS last_loot
                       FROM __items
                       WHERE `item_buyer` = '{$row['member_name']}'";
            $last_loot = $db->query_first($ll_sql);
        }
        
        $tpl->assign_block_vars('members_row', array(
            'ROW_CLASS'       => $eqdkp->switch_row_class(),
            'ID'              => $row['member_id'],
            'NAME'            => $row['member_name'],
            'LEVEL'           => ( $row['member_level'] > 0 ) ? $row['member_level'] : '&nbsp;',
            'CLASS'           => ( !empty($row['member_class']) ) ? $row['member_class'] : '&nbsp;',
            'EARNED'          => $row['member_earned'],
            'SPENT'           => $row['member_spent'],
            'ADJUSTMENT'      => $row['member_adjustment'],
            'CURRENT'         => number_format($row['member_current'], 2),
            'LASTRAID'        => ( !empty($row['member_lastraid']) ) ? date($user->style['date_notime_short'], $row['member_lastraid']) : '&nbsp;',
            'LASTLOOT'        => ( isset($last_loot) ) ? date($user->style['date_notime_short'], $last_loot) : '&nbsp;',
            'RAIDS_30_DAYS'   => sprintf($user->lang['of_raids'], $percent_of_raids_30),
            'RAIDS_90_DAYS'   => sprintf($user->lang['of_raids'], $percent_of_raids_90),
            'C_ADJUSTMENT'    => color_item($row['member_adjustment']),
            'C_CURRENT'       => color_item($row['member_current']),
            'C_LASTRAID'      => 'neutral',
            'C_RAIDS_30_DAYS' => color_item($percent_of_raids_30, true),
            'C_RAIDS_90_DAYS' => color_item($percent_of_raids_90, true),
            'U_VIEW_MEMBER'   => member_path($row['member_name'])
        ));
        unset($last_loot);
    }
    $db->free_result($result);
    $footcount_text = $user->lang['listmembers_compare_footcount'];
    
    $encoded_ids = $compare;
    $tpl->assign_vars(array(
        'U_COMPARE_MEMBERS' => 'listmembers.php' . $SID . '&amp;compare=' . $encoded_ids . '&amp;')
    );
}
//
// Normal member display
//
else
{
    $s_compare = false;
    
    $member_count = 0;
    $previous_data = '';
    
    // Figure out what data we're comparing from member to member
    // in order to rank them
    $sort_index = explode('.', $current_order['uri']['current']);
    $previous_source = preg_replace('/( (asc|desc))?/i', '', $sort_order[$sort_index[0]][$sort_index[1]]);
    
    $show_all = ( $in->get('show') == 'all' ) ? true : false;
    
    // ---------------------------
    // Build filter drop-down
    // ---------------------------
    $filter = $in->get('filter');
    require_once($eqdkp_root_path . 'games/game_manager.php');
    $gm = new Game_Manager();
    
    $filter_options = array(
        // TODO: Localize this string
        array('VALUE' => '', 'SELECTED' => '', 'OPTION' => 'None'),
        array('VALUE' => '', 'SELECTED' => '', 'OPTION' => '---------')
    );
    
    foreach ( $gm->getArmorTypes() as $type )
    {
        $filter_options[] = array(
            'VALUE'    => "armor_" . $type,
            'SELECTED' => option_selected($filter == "armor_{$type}"),
            'OPTION'   => str_replace('_', ' ', $type)
        );
    }
    
    $filter_options[] = array('VALUE' => '', 'SELECTED' => '', 'OPTION' => '---------');
    
    foreach ( $gm->getClasses() as $class )
    {
        $filter_options[] = array(
            'VALUE'    => $class['name'],
            'SELECTED' => option_selected($filter == $class['name']),
            'OPTION'   => $class['name']
        );
    }
    
    foreach ( $filter_options as $option )
    {
        $tpl->assign_block_vars('filter_row', $option);
    }
    
    // NOTE: Filtering by class or by armor may not be mutually exclusive actions. consider revising.
    // ---------------------------
    // Filter
    // ---------------------------
    $filter_by = '';
    if ( preg_match('/^armor_.+/', $filter) )
    {
        $input = $db->escape(str_replace('armor_', '', $in->get('filter')));
        $filter_by = " AND `class_armor_type` = '{$input}'";
    }
    elseif ( empty($filter) )
    {
        $filter_by = '';
    }
    else
    {
        $input = $db->escape($in->get('filter'));
        $filter_by = " AND `class_name` = '{$input}'";
    }

    $sql = "SELECT m.*, (m.member_earned-m.member_spent+m.member_adjustment) AS member_current, 
                m.member_status, r.rank_name, r.rank_hide, r.rank_prefix, r.rank_suffix, 
                c.class_name AS member_class, c.class_armor_type AS armor_type,
                c.class_min_level AS min_level, c.class_max_level AS max_level
            FROM __members AS m, __member_ranks AS r, __classes AS c
            WHERE (c.class_id = m.member_class_id)
            AND (m.member_rank_id = r.rank_id)
            {$filter_by}";
    if ( $in->get('rank', false) && validateRank($in->get('rank')) )
    {
        $sql .= " AND (r.`rank_name` = '" . $db->escape($in->get('rank')) . "')";
    }
    $sql .= " ORDER BY {$current_order['sql']}";
    
    if ( !($members_result = $db->query($sql)) )
    {
        message_die('Could not obtain member information', '', __FILE__, __LINE__, $sql);
    }

    while ( $row = $db->fetch_record($members_result) )
    {
        // Figure out the rank search URL based on show and filter
        $u_rank_search  = 'listmembers.php' . $SID . '&amp;rank=' . urlencode($row['rank_name']);
        $u_rank_search .= ( ($eqdkp->config['hide_inactive'] == 1) && (!$show_all) ) ? '&amp;show=' : '&amp;show=all';
        $u_rank_search .= ( $filter != 'none' ) ? '&amp;filter=' . $filter : '';
        
        if ( member_display($row, $show_all, $filter) )
        {
            $member_count++;

            $tpl->assign_block_vars('members_row', array(
                'ROW_CLASS'     => $eqdkp->switch_row_class(),
                'ID'            => $row['member_id'],
                //'COUNT'       => ($row[$previous_source] == $previous_data) ? '&nbsp;' : $member_count,
                'COUNT'         => $member_count,
                'NAME'          => $row['rank_prefix'] . (( $row['member_status'] == '0' ) ? '<i>' . sanitize($row['member_name']) . '</i>' : sanitize($row['member_name'])) . $row['rank_suffix'],
                'RANK'          => ( !empty($row['rank_name']) ) ? (( $row['rank_hide'] == '1' ) ? '<i>' . '<a href="'.$u_rank_search.'">' . stripslashes($row['rank_name']) . '</a>' . '</i>'  : '<a href="'.$u_rank_search.'">' . sanitize($row['rank_name']) . '</a>') : '&nbsp;',
                'LEVEL'         => ( $row['member_level'] > 0 ) ? $row['member_level'] : '&nbsp;',
                'CLASS'         => ( !empty($row['member_class']) ) ? $row['member_class'] : '&nbsp;',
                'ARMOR'         => ( !empty($row['armor_type']) ) ? $row['armor_type'] : '&nbsp;',
                'EARNED'        => $row['member_earned'],
                'SPENT'         => $row['member_spent'],
                'ADJUSTMENT'    => $row['member_adjustment'],
                'CURRENT'       => number_format($row['member_current'], 2),
                'LASTRAID'      => ( !empty($row['member_lastraid']) ) ? date($user->style['date_notime_short'], $row['member_lastraid']) : '&nbsp;',
                'C_ADJUSTMENT'  => color_item($row['member_adjustment']),
                'C_CURRENT'     => color_item($row['member_current']),
                'C_LASTRAID'    => 'neutral',
                'U_VIEW_MEMBER' => member_path($row['member_name'])
            ));
            $u_rank_search = '';
            unset($last_loot);
            
            // So that we can compare this member to the next member,
            // set the value of the previous data to the source
            $previous_data = $row[$previous_source];
        }
    }
    
    $uri_addon  = ''; // Added to the end of the sort links
    $uri_addon .= '&amp;filter=' . urlencode($filter);
    $uri_addon .= ( $in->get('show') != '' ) ? '&amp;show=' . sanitize($in->get('show')) : '';
    
    if ( ($eqdkp->config['hide_inactive'] == 1) && (!$show_all) )
    {
        $footcount_text = sprintf($user->lang['listmembers_active_footcount'], $member_count, 
                                  '<a href="listmembers.php' . $SID . '&amp;' . URI_ORDER . '=' . $current_order['uri']['current'] . '&amp;show=all" class="rowfoot">');
    }
    else
    {
        $footcount_text = sprintf($user->lang['listmembers_footcount'], $member_count);
    }
    $db->free_result($members_result);
}

$tpl->assign_vars(array(
    'F_MEMBERS' => 'listmembers.php'.$SID,
    'V_SID'     => str_replace('?' . URI_SESSION . '=', '', $SID),
    
    'L_FILTER'        => $user->lang['filter'],
    'L_NAME'          => $user->lang['name'],
    'L_RANK'          => $user->lang['rank'],
    'L_LEVEL'         => $user->lang['level'],
    'L_CLASS'         => $user->lang['class'],
    'L_ARMOR'         => $user->lang['armor'],
    'L_EARNED'        => $user->lang['earned'],
    'L_SPENT'         => $user->lang['spent'],
    'L_ADJUSTMENT'    => $user->lang['adjustment'],
    'L_CURRENT'       => $user->lang['current'],
    'L_LASTRAID'      => $user->lang['lastraid'],
    'L_LASTLOOT'      => $user->lang['lastloot'],
    'L_RAIDS_30_DAYS' => sprintf($user->lang['raids_x_days'], 30),
    'L_RAIDS_90_DAYS' => sprintf($user->lang['raids_x_days'], 90),
    'BUTTON_NAME'     => 'submit',
    'BUTTON_VALUE'    => $user->lang['compare_members'],
    
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
    
    'URI_ADDON'      => $uri_addon,
    'U_LIST_MEMBERS' => 'listmembers.php' . $SID . '&amp;',
    
    'S_COMPARE' => $s_compare,
    'S_NOTMM'   => true,
    
    'LISTMEMBERS_FOOTCOUNT' => ( $s_compare ) ? sprintf($footcount_text, sizeof(explode(',', $compare))) : $footcount_text)
);

$eqdkp->set_vars(array(
    'page_title'    => page_title($user->lang['listmembers_title']),
    'template_file' => 'listmembers.html',
    'display'       => true)
);

function member_display(&$row, $show_all = false, $filter = null)
{
    global $eqdkp;
    
    // Replace space with underscore (for array indices)
    // Damn you Shadow Knights!
    $d_filter = ucwords(str_replace('_', ' ', $filter));
    $d_filter = str_replace(' ', '_', $d_filter);
    
    $member_display = null;
    
    // Are we showing all?
    if ( $show_all )
    {
        $member_display = true;
    }
    else
    {
        // Are we hiding inactive members?
        if ( $eqdkp->config['hide_inactive'] == '0' )
        {
            //Are we hiding their rank?
            $member_display = ( $row['rank_hide'] == '0' ) ? true : false;
        }
        else
        {
            // Are they active?
            if ( $row['member_status'] == '0' )
            {
                $member_display = false;
            }
            else
            {
                $member_display = ( $row['rank_hide'] == '0' ) ? true : false;
            }
        }
    }
    
    return $member_display;
}

// Assure _GET['rank'] is one of our ranks
function validateRank($rank)
{
    global $db;
    $retval = false;
    
    $sql = "SELECT rank_id, rank_name
            FROM __member_ranks";
    $result = $db->query($sql);
    
    while ( $row = $db->fetch_record($result) )
    {
        if ( $row['rank_id'] == $rank || $row['rank_name'] == $rank )
        {
            $retval = true;
        }
    }
    $db->free_result($result);
    
    return $retval;
}

function validateCompareInput($input)
{
    // Remove codes from the list, like "%20"
    $retval = urldecode($input);
    
    // Remove anything that's not a comma or numeric
    $retval = preg_replace('#[^0-9\,]#', '', $retval);
    
    // Remove any extra commas as a result of removing bogus entries above
    $retval = preg_replace('#\,{2,}#', ',', $retval);
    
    // Remove a trailing blank entry
    $retval = preg_replace('#,$#', '', $retval);
    
    return $retval;
}