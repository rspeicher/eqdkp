<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        listmembers.php
 * Began:       Wed Dec 18 2002
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
    9 => array('armor_type_id', 'armor_type_id desc')
);

$current_order = switch_order($sort_order);

//
// Compare members
//
// TODO: if-else causes two different pages to be rendered. Split into separate files.
if ( $in->get('submit') == $user->lang['compare_members'] && $in->get('compare_ids', false) )
{
    redirect(member_path() . path_params('compare', implode(',', $in->getArray('compare_ids', 'int'))));
}
elseif ( $in->get('compare', false) )
{
    $s_compare = true;
    $uri_addon = '';

    $compare = validateCompareInput($in->get('compare'));
    
    // Find 30 days ago, then find how many raids occurred in those 30 days, and 90 days
    $thirty_days = strtotime(date("Y-m-d", time() - 60 * 60 * 24 * 30));
    $ninety_days = strtotime(date("Y-m-d", time() - 60 * 60 * 24 * 90));

    $time = time();
    $raid_count_30 = $db->query_first("SELECT COUNT(*) FROM __raids WHERE (`raid_date` BETWEEN {$thirty_days} AND {$time})");
    $raid_count_90 = $db->query_first("SELECT COUNT(*) FROM __raids WHERE (`raid_date` BETWEEN {$ninety_days} AND {$time})");
    
    // Build an SQL query that includes each of the compare IDs
    $sql = "SELECT *, (member_earned-member_spent+member_adjustment) AS member_current,
                c.class_name AS member_class
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
        
        $rc_sql = "SELECT COUNT(*)
                   FROM __raids AS r, __raid_attendees AS ra
                   WHERE (ra.raid_id = r.raid_id)
                   AND (ra.`member_name` = '" . $db->escape($row['member_name']) . "')
                   AND (r.raid_date BETWEEN {$thirty_days} AND {$time})";
        $individual_raid_count_30 = $db->query_first($rc_sql);
        
        $rc_sql = "SELECT COUNT(*)
                   FROM __raids AS r, __raid_attendees AS ra
                   WHERE (ra.raid_id = r.raid_id)
                   AND (ra.`member_name` = '" . $db->escape($row['member_name']) . "')
                   AND (r.raid_date BETWEEN {$ninety_days} AND {$time})";
        $individual_raid_count_90 = $db->query_first($rc_sql);
        
        // Prevent division by 0
        $percent_of_raids_30 = ( $raid_count_30 > 0 ) ? round(($individual_raid_count_30 / $raid_count_30) * 100) : 0;
        $percent_of_raids_90 = ( $raid_count_90 > 0 ) ? round(($individual_raid_count_90 / $raid_count_90) * 100) : 0;
        
        // If the member's spent is greater than 0, see how long ago they looted an item
        if ( $row['member_spent'] > 0 )
        {
            $ll_sql = "SELECT max(item_date) AS last_loot
                       FROM __items
                       WHERE (`item_buyer` = '" . $db->escape($row['member_name']) . "')";
            $last_loot = $db->query_first($ll_sql);
        }
        
        $tpl->assign_block_vars('members_row', array(
            'ROW_CLASS'       => $eqdkp->switch_row_class(),
            'ID'              => $row['member_id'],
            'NAME'            => sanitize($row['member_name']),
            'LEVEL'           => ( $row['member_level'] > 0 ) ? intval($row['member_level']) : '&nbsp;',
            'CLASS'           => ( !empty($row['member_class']) ) ? sanitize($row['member_class']) : '&nbsp;',
            'EARNED'          => number_format($row['member_earned'], 2),
            'SPENT'           => number_format($row['member_spent'], 2),
            'ADJUSTMENT'      => number_format($row['member_adjustment'], 2),
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
    
    $tpl->assign_var('U_COMPARE_MEMBERS', member_path() . path_params('compare', $compare));
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
    
    $filter_options = array(
        // TODO: Localize this string
        array('VALUE' => '', 'SELECTED' => '', 'OPTION' => 'None'),
    );

	$filter_options[] = array('VALUE' => '', 'SELECTED' => '', 'OPTION' => '---------');
    
    foreach ( $gm->sql_armor_types() as $armor_type )
    {
        $filter_options[] = array(
            'VALUE'    => sanitize("armor_" . $armor_type['name'], ENT),
            'SELECTED' => option_selected($filter == "armor_{$armor_type['name']}"),
            'OPTION'   => str_replace('_', ' ', $armor_type['name'])
        );
    }
    
    $filter_options[] = array('VALUE' => '', 'SELECTED' => '', 'OPTION' => '---------');
    
    foreach ( $gm->sql_classes() as $class )
    {
        $filter_options[] = array(
            'VALUE'    => sanitize($class['name'], ENT),
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
        $filter_by = " AND (`armor_type_name` = '{$input}')";
    }
    elseif ( empty($filter) )
    {
        $filter_by = '';
    }
    else
    {
        $input = $db->escape($in->get('filter'));
        $filter_by = " AND (`class_name` = '{$input}')";
    }

	// NOTE: We currently prevent duplicate entries for the same person, by filtering out the lowest *armor type IDs* for each member's class.
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
            {$filter_by}";
	
    if ( $in->exists('rank') )
    {
        $sql .= " AND (r.`rank_id` = '" . $in->get('rank', 0) . "')";
    }
	
	// NOTE: As per the conditions of using MAX(), we need to group by something. We'll group by member ID, because it's essentially a transparent grouping.
	$sql .= " GROUP BY m.member_id";
    $sql .= " ORDER BY {$current_order['sql']}";
    
    if ( !($members_result = $db->query($sql)) )
    {
        message_die('Could not obtain member information', '', __FILE__, __LINE__, $sql);
    }

    while ( $row = $db->fetch_record($members_result) )
    {
        // Figure out the rank search URL based on show and filter
        $u_rank_search  = member_path() . path_params('rank', $row['rank_id']);
        $u_rank_search .= ( ($eqdkp->config['hide_inactive'] == 1) && (!$show_all) ) ? '' : path_params('show', 'all');
        $u_rank_search .= ( $filter != 'none' ) ? path_params('filter', $filter) : '';
        
        if ( member_display($row, $show_all, $filter) )
        {
            $member_count++;
            
            $member_name = ( $row['member_status'] == 0 ) ? '<i>' . sanitize($row['member_name']) . '</i>' : sanitize($row['member_name']);

            $tpl->assign_block_vars('members_row', array(
                'ROW_CLASS'     => $eqdkp->switch_row_class(),
                'ID'            => $row['member_id'],
                'COUNT'         => ($row[$previous_source] == $previous_data) ? '&nbsp;' : $member_count,
                'NAME'          => sprintf($row['member_sname'], $member_name),
                'RANK'          => ( !empty($row['rank_name']) ) ? '<a href="'.$u_rank_search.'">' . sanitize($row['rank_name']) . '</a>' : '&nbsp;',
                'LEVEL'         => ( $row['member_level'] > 0 ) ? $row['member_level'] : '&nbsp;',
                'CLASS'         => ( !empty($row['member_class']) ) ? sanitize($row['member_class']) : '&nbsp;',
                'ARMOR'         => ( !empty($row['armor_type']) ) ? sanitize($row['armor_type']) : '&nbsp;',
                'EARNED'        => number_format($row['member_earned'], 2),
                'SPENT'         => number_format($row['member_spent'], 2),
                'ADJUSTMENT'    => number_format($row['member_adjustment'], 2),
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
    $uri_addon .= path_params('filter', $filter);
    $uri_addon .= ( $in->get('show') != '' ) ? path_params('show', sanitize($in->get('show'))) : '';
    
    if ( ($eqdkp->config['hide_inactive'] == 1) && (!$show_all) )
    {
        // TODO: Holy god this is fugly
        $footcount_text = sprintf($user->lang['listmembers_active_footcount'], $member_count, 
                                  '<a href="' . member_path() . path_params(array(
                                      URI_ORDER => $current_order['uri']['current'],
                                      'show'    => 'all'
                                   )) . '" class="rowfoot">'
        );
    }
    else
    {
        $footcount_text = sprintf($user->lang['listmembers_footcount'], $member_count);
    }
    $db->free_result($members_result);
}

$tpl->assign_vars(array(
    'F_MEMBERS' => member_path(),
    
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
    'U_LIST_MEMBERS' => member_path() . '&amp;',
    
    'S_COMPARE' => $s_compare,
    'S_NOTMM'   => true,
    
    'LISTMEMBERS_FOOTCOUNT' => ( $s_compare ) ? sprintf($footcount_text, sizeof(explode(',', $compare))) : $footcount_text)
);

$eqdkp->set_vars(array(
    'page_title'    => page_title($user->lang['listmembers_title']),
    'template_file' => 'listmembers.html',
    'display'       => true
));

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