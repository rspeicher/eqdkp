<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        index.php
 * Began:       Tue Dec 24 2002
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */

// IN_ADMIN not yet defined, display the main admin page
if ( !defined('IN_ADMIN') )
{
    // ---------------------------------------------------------
    // Function definitions
    // ---------------------------------------------------------
    /**
    * 'Pretty-up' the Location field of the Who's Online list
    * If we recognize the script they're on, return a more friendly
    * location (like most modern forums do), otherwise just return the path
    *
    * @param    string  $page Page URL
    * @return   string
    */
    function resolve_eqdkp_page($page)
    {
        global $db, $eqdkp, $user;

        $matches = explode('&', $page);

        if ( !empty($matches[0]) )
        {
            // See if we recognize the page/script we're on
            switch ( $matches[0] )
            {
                // ---------------------------------------------------------
                // Admin
                // ---------------------------------------------------------
                case 'addadj':
                    $page = $user->lang['adding_groupadj'];
                    if ( (!empty($matches[1])) && (preg_match('/^' . URI_ADJUSTMENT . '=([0-9]{1,})/', $matches[1], $adjustment_id)) )
                    {
                        $page  = $user->lang['editing_groupadj'] . ': ';
                        $page .= '<a href="' . edit_adjustment_path($adjustment_id[1]) . '">' . $adjustment_id[1] . '</a>';
                    }
                    break;
                // ---------------------------------------------------------
                case 'addiadj':
                    $page = $user->lang['adding_indivadj'];
                    if ( (!empty($matches[1])) && (preg_match('/^' . URI_ADJUSTMENT . '=([0-9]{1,})/', $matches[1], $adjustment_id)) )
                    {
                        $page  = $user->lang['editing_indivadj'] . ': ';
                        $page .= '<a href="' . edit_iadjustment_path($adjustment_id[1]) . '">' . $adjustment_id[1] . '</a>';
                    }
                    break;
                // ---------------------------------------------------------
                case 'additem':
                    $page = $user->lang['adding_item'];
                    if ( (!empty($matches[1])) && (preg_match('/^' . URI_ITEM . '=([0-9]{1,})/', $matches[1], $item_id)) )
                    {
                        $item_name = get_object_name('item', $item_id[1]);
                        $page  = $user->lang['editing_item'] . ': ';
                        $page .= '<a href="' . edit_item_path($item_id[1]) . '">' . $item_name . '</a>';
                    }
                    break;
                // ---------------------------------------------------------
                case 'addnews':
                    $page = $user->lang['adding_news'];
                    if ( (!empty($matches[1])) && (preg_match('/^' . URI_NEWS . '=([0-9]{1,})/', $matches[1], $news_id)) )
                    {
                        $news_name = get_object_name('news', $news_id[1]);
                        $page  = $user->lang['editing_item'] . ': ';
                        $page .= '<a href="' . edit_news_path($news_id[1]) . '">' . $news_name . '</a>';
                    }
                    break;
                // ---------------------------------------------------------
                case 'addraid':
                    $page = $user->lang['adding_raid'];

                    if ( (!empty($matches[1])) && (preg_match('/^' . URI_RAID . '=([0-9]{1,})/', $matches[1], $raid_id)) )
                    {
                        $raid_name = get_object_name('raid', $raid_id[1]);
                        $page  = $user->lang['editing_raid'] . ': ';
                        $page .= '<a href="' . edit_raid_path($raid_id[1]) . '">' . $raid_name . '</a>';
                    }
                    break;
                // ---------------------------------------------------------
                case 'addturnin':
                    $page = $user->lang['adding_turnin'];
                    break;
                // ---------------------------------------------------------
                case 'config':
                    $page = $user->lang['managing_config'];
                    break;
                // ---------------------------------------------------------
                case 'index':
                    $page = $user->lang['viewing_admin_index'];
                    break;
                // ---------------------------------------------------------
                case 'logs':
                    $page = $user->lang['viewing_logs'];
                    break;
                // ---------------------------------------------------------
                case 'manage_members':
                    $page = $user->lang['managing_members'];
                    break;
                // ---------------------------------------------------------
                case 'manage_users':
                    $page = $user->lang['managing_users'];
                    break;
                // ---------------------------------------------------------
                case 'mysql_info':
                    $page = $user->lang['viewing_mysql_info'];
                    break;
                // ---------------------------------------------------------
                case 'plugins':
                    $page = $user->lang['managing_plugins'];
                    break;
                // ---------------------------------------------------------
                case 'styles':
                    $page = $user->lang['managing_styles'];
                    break;

                // ---------------------------------------------------------
                // Listing
                // ---------------------------------------------------------
                case 'listadj':
                    if ( (empty($matches[1])) || ($matches[1] == 'group') )
                    {
                        $page = $user->lang['listing_groupadj'];
                    }
                    else
                    {
                        $page = $user->lang['listing_indivadj'];
                    }
                    break;
                // ---------------------------------------------------------
                case 'listevents':
                    $page = $user->lang['listing_events'];
                    break;
                // ---------------------------------------------------------
                case 'listitems':
                    if ( (empty($matches[1])) || ($matches[1] == 'values') )
                    {
                        $page = $user->lang['listing_itemvals'];
                    }
                    else
                    {
                        $page = $user->lang['listing_itemhist'];
                    }
                    break;
                // ---------------------------------------------------------
                case 'listmembers':
                    $page = $user->lang['listing_members'];
                    break;
                // ---------------------------------------------------------
                case 'listraids':
                    $page = $user->lang['listing_raids'];
                    break;

                // ---------------------------------------------------------
                // Misc
                // ---------------------------------------------------------
                case 'parse_log':
                    $page = $user->lang['parsing_log'];
                    break;

                case 'stats':
                    $page = $user->lang['viewing_stats'];
                    break;

                case 'summary':
                    $page = $user->lang['viewing_summary'];
                    break;

                // ---------------------------------------------------------
                // Viewing
                // ---------------------------------------------------------
                case 'viewevent':
                    $page = $user->lang['viewing_event'] . ': ';
                    if ( !empty($matches[1]) )
                    {
                        preg_match('/^' . URI_EVENT . '=([0-9]{1,})/', $matches[1], $event_id);
                        $page .= '<a href="' . event_path($event_id[1]) . '" target="_top">' . get_object_name('event', $event_id[1]) . '</a>';
                    }
                    break;
                // ---------------------------------------------------------
                case 'viewitem':
                    $page = $user->lang['viewing_item'] . ': ';
                    if ( !empty($matches[1]) )
                    {
                        preg_match('/^' . URI_ITEM . '=([0-9]{1,})/', $matches[1], $item_id);
                        $page .= '<a href="' . item_path($item_id[1]) . '" target="_top">' . get_object_name('item', $item_id[1]) . '</a>';
                    }
                    break;
                // ---------------------------------------------------------
                case 'viewnews':
                    $page = $user->lang['viewing_news'];
                    break;
                // ---------------------------------------------------------
                case 'viewmember':
                    $page = $user->lang['viewing_member'] . ': ';
                    if ( !empty($matches[1]) )
                    {
                        preg_match('/^' . URI_NAME . '=([A-Za-z]{1,})/', $matches[1], $member_name);
                        $page .= '<a href="' . member_path($member_name[1]) . '" target="_top">' . $member_name[1] . '</a>';
                    }
                    break;
                // ---------------------------------------------------------
                case 'viewraid':
                    $page = $user->lang['viewing_raid'] . ': ';
                    if ( !empty($matches[1]) )
                    {
                        preg_match('/^' . URI_RAID . '=([0-9]{1,})/', $matches[1], $raid_id);
                        $page .= '<a href="' . raid_path($raid_id[1]) . '" target="_top">' . get_object_name('raid', $raid_id[1]) . '</a>';
                    }
                    break;
            }
        }

        return $page;
    }
    
    function get_object_name($type, $id)
    {
        global $db;
        
        $id = intval($id);
        
        $table_name = ( $type != 'news' ) ? "__{$type}s" : "__news";
        $field_name = ( $type != 'news' ) ? "{$type}_name" : "news_headline";
        
        // Escape input since an anonymous user could access any page with a 
        // bogus ID and, even though they won't see anything, they'd potentially
        // break this query.
        $sql = "SELECT `{$field_name}` FROM `{$table_name}` WHERE (`{$type}_id` = '" . $db->escape($id) . "')";
        $name = $db->query_first($sql);
        
        return ( !empty($name) ) ? sanitize($name) : 'Unknown';
    }

    // TODO: Apparently 1.3 disabled a call to this function, for unknown reasons.
    // Update this page so the user has an option to hide the notice after they've seen it once.
    function get_eqdkp_version()
    {
        // Try and get the latest EQdkp version from EQdkp.com
        $sh = @fsockopen('eqdkp.com', 80, $errno, $error, 5);
        if ( !$sh )
        {
            return EQDKP_VERSION;
        }
        else
        {
            @fputs($sh, "GET /version.php HTTP/1.1\r\nHost: eqdkp.com\r\nConnection: close\r\n\r\n");
            while ( !feof($sh) )
            {
                $content = @fgets($sh, 512);
                if ( preg_match('/<version>(.*)<\/version>/', $content, $version) )
                {
                    return $version[1];
                }
            }
        }
        @fclose($sh);

        return EQDKP_VERSION;
    }

    // ---------------------------------------------------------
    // Display the main admin page
    // ---------------------------------------------------------

    define('EQDKP_INC', true);
    define('IN_ADMIN', true);
    $eqdkp_root_path = './../';
    require_once($eqdkp_root_path . 'common.php');

    $user->check_auth('a_');

    $days = ((time() - $eqdkp->config['eqdkp_start']) / 86400);

    $total_members_inactive = $db->query_first("SELECT COUNT(*) FROM __members WHERE (`member_status` = '0')");
    $total_members_active = $db->query_first("SELECT COUNT(*) FROM __members WHERE (`member_status` = '1')");
    $total_members = $total_members_active . ' / ' . $total_members_inactive;

    $total_raids   = $db->query_first("SELECT COUNT(*) FROM __raids");
    $raids_per_day = number_format($total_raids / $days, 2);

    $total_items   = $db->query_first("SELECT COUNT(*) FROM __items");
    $items_per_day = number_format($total_items / $days, 2);

    $total_logs    = $db->query_first("SELECT COUNT(*) FROM __logs");

    if ( $raids_per_day > $total_raids )
    {
        $raids_per_day = $total_raids;
    }
    if ( $items_per_day > $total_items )
    {
        $items_per_day = $total_items;
    }

    // DB Size
    $dbsize = get_database_size();

    //
    // Who's Online
    //
    $sql = "SELECT s.*, u.user_name
            FROM __sessions AS s LEFT JOIN __users AS u ON u.`user_id` = s.`user_id`
            GROUP BY u.`user_name`, s.`session_ip`
            ORDER BY u.`user_name`, s.`session_current` DESC";
    $result = $db->query($sql);
    while ( $row = $db->fetch_record($result) )
    {
        $session_page = resolve_eqdkp_page($row['session_page']);

        $tpl->assign_block_vars('online_row', array(
            'ROW_CLASS'   => $eqdkp->switch_row_class(),
            'USERNAME'    => ( !empty($row['user_name']) ) ? sanitize($row['user_name']) : $user->lang['anonymous'],
            'LOGIN'       => date($user->style['date_time'], $row['session_start']),
            'LAST_UPDATE' => date($user->style['date_time'], $row['session_current']),
            'LOCATION'    => $session_page,
            'IP_ADDRESS'  => preg_replace('/[^0-9\.]/', '', $row['session_ip'])
        ));
    }
    $online_count = $db->num_rows($result);

    // Log Actions
    $s_logs = false;
    if ( $user->check_auth('a_logs_view', false) )
    {
        if ( $total_logs > 0 )
        {
            $sql = "SELECT l.*, u.user_name
                    FROM __logs AS l, __users AS u
                    WHERE (u.`user_id` = l.`admin_id`)
                    ORDER BY l.`log_date` DESC
                    LIMIT 10";
            $result = $db->query($sql);
            while ( $row = $db->fetch_record($result) )
            {
                eval($row['log_action']);

                switch ( $row['log_type'] )
                {
                    case '{L_ACTION_EVENT_ADDED}':
                        $logline = sprintf($user->lang['vlog_event_added'],      $row['user_name'], $log_action['{L_NAME}'], $log_action['{L_VALUE}']);
                        break;
                    case '{L_ACTION_EVENT_UPDATED}':
                        $logline = sprintf($user->lang['vlog_event_updated'],    $row['user_name'], $log_action['{L_NAME_BEFORE}']);
                        break;
                    case '{L_ACTION_EVENT_DELETED}':
                        $logline = sprintf($user->lang['vlog_event_deleted'],    $row['user_name'], $log_action['{L_NAME}']);
                        break;
                    case '{L_ACTION_GROUPADJ_ADDED}':
                        $logline = sprintf($user->lang['vlog_groupadj_added'],   $row['user_name'], $log_action['{L_ADJUSTMENT}']);
                        break;
                    case '{L_ACTION_GROUPADJ_UPDATED}':
                        $logline = sprintf($user->lang['vlog_groupadj_updated'], $row['user_name'], $log_action['{L_ADJUSTMENT_BEFORE}']);
                        break;
                    case '{L_ACTION_GROUPADJ_DELETED}':
                        $logline = sprintf($user->lang['vlog_groupadj_deleted'], $row['user_name'], $log_action['{L_ADJUSTMENT}']);
                        break;
                    case '{L_ACTION_HISTORY_TRANSFER}':
                        $logline = sprintf($user->lang['vlog_history_transfer'], $row['user_name'], $log_action['{L_FROM}'], $log_action['{L_TO}']);
                        break;
                    case '{L_ACTION_INDIVADJ_ADDED}':
                        $logline = sprintf($user->lang['vlog_indivadj_added'],   $row['user_name'], $log_action['{L_ADJUSTMENT}'], count(explode(', ', $log_action['{L_MEMBERS}'])));
                        break;
                    case '{L_ACTION_INDIVADJ_UPDATED}':
                        $logline = sprintf($user->lang['vlog_indivadj_updated'], $row['user_name'], $log_action['{L_ADJUSTMENT_BEFORE}'], $log_action['{L_MEMBERS_BEFORE}']);
                        break;
                    case '{L_ACTION_INDIVADJ_DELETED}':
                        $logline = sprintf($user->lang['vlog_indivadj_deleted'], $row['user_name'], $log_action['{L_ADJUSTMENT}'], $log_action['{L_MEMBERS}']);
                        break;
                    case '{L_ACTION_ITEM_ADDED}':
                        $logline = sprintf($user->lang['vlog_item_added'],       $row['user_name'], $log_action['{L_NAME}'], count(explode(', ', $log_action['{L_BUYERS}'])), $log_action['{L_VALUE}']);
                        break;
                    case '{L_ACTION_ITEM_UPDATED}':
                        $logline = sprintf($user->lang['vlog_item_updated'],     $row['user_name'], $log_action['{L_NAME_BEFORE}'], count(explode(', ', $log_action['{L_BUYERS_BEFORE}'])));
                        break;
                    case '{L_ACTION_ITEM_DELETED}':
                        $logline = sprintf($user->lang['vlog_item_deleted'],     $row['user_name'], $log_action['{L_NAME}'], count(explode(', ', $log_action['{L_BUYERS}'])));
                        break;
                    case '{L_ACTION_MEMBER_ADDED}':
                        $logline = sprintf($user->lang['vlog_member_added'],     $row['user_name'], $log_action['{L_NAME}']);
                        break;
                    case '{L_ACTION_MEMBER_UPDATED}':
                        $logline = sprintf($user->lang['vlog_member_updated'],   $row['user_name'], $log_action['{L_NAME_BEFORE}']);
                        break;
                    case '{L_ACTION_MEMBER_DELETED}':
                        $logline = sprintf($user->lang['vlog_member_deleted'],   $row['user_name'], $log_action['{L_NAME}']);
                        break;
                    case '{L_ACTION_NEWS_ADDED}':
                        $logline = sprintf($user->lang['vlog_news_added'],       $row['user_name'], $log_action['{L_HEADLINE}']);
                        break;
                    case '{L_ACTION_NEWS_UPDATED}':
                        $logline = sprintf($user->lang['vlog_news_updated'],     $row['user_name'], $log_action['{L_HEADLINE_BEFORE}']);
                        break;
                    case '{L_ACTION_NEWS_DELETED}':
                        $logline = sprintf($user->lang['vlog_news_deleted'],     $row['user_name'], $log_action['{L_HEADLINE}']);
                        break;
                    case '{L_ACTION_RAID_ADDED}':
                        $logline = sprintf($user->lang['vlog_raid_added'],       $row['user_name'], $log_action['{L_EVENT}']);
                        break;
                    case '{L_ACTION_RAID_UPDATED}':
                        $logline = sprintf($user->lang['vlog_raid_updated'],     $row['user_name'], $log_action['{L_EVENT_BEFORE}']);
                        break;
                    case '{L_ACTION_RAID_DELETED}':
                        $logline = sprintf($user->lang['vlog_raid_deleted'],     $row['user_name'], $log_action['{L_EVENT}']);
                        break;
                    case '{L_ACTION_TURNIN_ADDED}':
                        $logline = sprintf($user->lang['vlog_turnin_added'],     $row['user_name'], $log_action['{L_FROM}'], $log_action['{L_TO}'], $log_action['{L_ITEM}']);
                        break;
                }
                unset($log_action);

                // Show the log if we have a valid line for it
                if ( isset($logline) )
                {
                    $tpl->assign_block_vars('actions_row', array(
                        'ROW_CLASS'  => $eqdkp->switch_row_class(),
                        'U_VIEW_LOG' => log_path($row['log_id']),
                        'ACTION'     => sanitize($logline)
                    ));
                }
                unset($logline);
            }
            $db->free_result($result);

            $s_logs = true;
        }
    }

    // FIXME: Improve and re-enable version check?
    $eqdkp_com_version = EQDKP_VERSION;

    $tpl->assign_vars(array(
        'S_NEW_VERSION' => false, // Always false, for now
        'S_LOGS'        => $s_logs,

        'L_VERSION_UPDATE'     => $user->lang['version_update'],
        'L_NEW_VERSION_NOTICE' => sprintf($user->lang['new_version_notice'], $eqdkp_com_version),
        'L_STATISTICS'         => $user->lang['statistics'],
        'L_NUMBER_OF_MEMBERS'  => $user->lang['number_of_members'],
        'L_NUMBER_OF_RAIDS'    => $user->lang['number_of_raids'],
        'L_NUMBER_OF_ITEMS'    => $user->lang['number_of_items'],
        'L_DATABASE_SIZE'      => $user->lang['database_size'],
        'L_NUMBER_OF_LOGS'     => $user->lang['number_of_logs'],
        'L_RAIDS_PER_DAY'      => $user->lang['raids_per_day'],
        'L_ITEMS_PER_DAY'      => $user->lang['items_per_day'],
        'L_EQDKP_STARTED'      => $user->lang['eqdkp_started'],

        'NUMBER_OF_MEMBERS' => $total_members,
        'NUMBER_OF_RAIDS'   => $total_raids,
        'NUMBER_OF_ITEMS'   => $total_items,
        'DATABASE_SIZE'     => $dbsize,
        'NUMBER_OF_LOGS'    => $total_logs,
        'RAIDS_PER_DAY'     => $raids_per_day,
        'ITEMS_PER_DAY'     => $items_per_day,
        'EQDKP_STARTED'     => date($user->style['date_time'], $eqdkp->config['eqdkp_start']),

        'L_WHO_ONLINE'  => $user->lang['who_online'],
        'L_USERNAME'    => $user->lang['username'],
        'L_LOGIN'       => $user->lang['login'],
        'L_LAST_UPDATE' => $user->lang['last_update'],
        'L_LOCATION'    => $user->lang['location'],
        'L_IP_ADDRESS'  => $user->lang['ip_address'],

        'L_NEW_ACTIONS' => $user->lang['new_actions'],

        'ONLINE_FOOTCOUNT' => sprintf($user->lang['online_footcount'], $online_count)
    ));

    $eqdkp->set_vars(array(
        'page_title'    => $user->lang['admin_index_title'],
        'template_file' => 'admin/admin_index.html',
        'display'       => true
    ));
}
// IN_ADMIN already defined, just output the menu
else
{
    // Build a dynamic admin menu
    // Credit to draelon for the idea and original implementation
    // 0 = header
    // 1 - n = array(link, text, auth_check)
    $admin_menu = array(
        'events' => array(
            0 => $user->lang['events'],
            1 => array('link' => path_default('addevent.php', true),   'text' => $user->lang['add'],  'check' => 'a_event_add'),
            2 => array('link' => path_default('listevents.php', true), 'text' => $user->lang['list'], 'check' => 'a_event_')
        ),
        'groupadj' => array(
            0 => $user->lang['group_adjustments'],
            1 => array('link' => path_default('addadj.php', true),  'text' => $user->lang['add'],  'check' => 'a_groupadj_add'),
            2 => array('link' => path_default('listadj.php', true), 'text' => $user->lang['list'], 'check' => 'a_groupadj_')
        ),
        'indivadj' => array(
            0 => $user->lang['individual_adjustments'],
            1 => array('link' => path_default('addiadj.php', true),                                       'text' => $user->lang['add'],  'check' => 'a_indivadj_add'),
            2 => array('link' => path_default('listadj.php', true) . path_params(URI_PAGE, 'individual'), 'text' => $user->lang['list'], 'check' => 'a_indivadj_')
        ),
        'items' => array(
            0 => $user->lang['items'],
            1 => array('link' => path_default('additem.php', true),   'text' => $user->lang['add'],  'check' => 'a_item_add'),
            2 => array('link' => path_default('listitems.php', true), 'text' => $user->lang['list'], 'check' => 'a_item_')
        ),
        'mysql' => array(
            0 => $user->lang['mysql'],
            1 => array('link' => path_default('mysql_info.php', true), 'text' => $user->lang['mysql_info'], 'check' => ''),
            2 => array('link' => path_default('backup.php', true),     'text' => $user->lang['backup'],     'check' => 'a_backup')
        ),
        'news' => array(
            0 => $user->lang['news'],
            1 => array('link' => path_default('addnews.php', true),  'text' => $user->lang['add'],  'check' => 'a_news_add'),
            2 => array('link' => path_default('listnews.php', true), 'text' => $user->lang['list'], 'check' => 'a_news_')
        ),
        'raids' => array(
            0 => $user->lang['raids'],
            1 => array('link' => path_default('addraid.php', true),   'text' => $user->lang['add'],  'check' => 'a_raid_add'),
            2 => array('link' => path_default('listraids.php', true), 'text' => $user->lang['list'], 'check' => 'a_raid_'),
            3 => array('link' => path_default('parse_log.php', true), 'text' => 'Parse Log (DEBUG)', 'check' => 'a_raid_'),
        ),
        'turnin' => array(
            0 => $user->lang['turn_ins'],
            1 => array('link' => path_default('addturnin.php', true), 'text' => $user->lang['add'], 'check' => 'a_turnin_add')
        ),
        'general' => array(
            0 => $user->lang['general_admin'],
            1 => array('link' => path_default('settings.php', true),       'text' => $user->lang['configuration'],  'check' => 'a_config_man'),
            2 => array('link' => path_default('manage_members.php', true), 'text' => $user->lang['manage_members'], 'check' => 'a_members_man'),
            3 => array('link' => path_default('plugins.php', true),        'text' => $user->lang['manage_plugins'], 'check' => 'a_plugins_man'),
            4 => array('link' => path_default('manage_users.php', true),   'text' => $user->lang['manage_users'],   'check' => 'a_users_man'),
            5 => array('link' => path_default('logs.php', true),           'text' => $user->lang['view_logs'],      'check' => 'a_logs_view')
        ),
        'styles' => array(
            0 => $user->lang['styles'],
            1 => array('link' => path_default('styles.php', true) . path_params('mode', 'create'), 'text' => $user->lang['create'], 'check' => 'a_styles_man'),
            2 => array('link' => path_default('styles.php', true),                                 'text' => $user->lang['manage'], 'check' => 'a_styles_man')
        )
    );

    // Now get plugin hooks for the menu
    $admin_menu = (is_array($pm->get_menus('admin_menu'))) ? array_merge($admin_menu, $pm->get_menus('admin_menu')) : $admin_menu;

    // Sort the array by the keys to make it alphabetical by header (essentially)
    // Note: I considered using the header as the key itself, but this could
    //      possibly break PHP if non-standard characters were used when another language
    //      was in use.
    ksort($admin_menu);
    reset($admin_menu);

    foreach ( $admin_menu as $k => $v )
    {
        // Restart next loop if the element isn't an array we can use
        if ( !is_array($v) )
        {
            continue;
        }

        // Set the header with the first element
        $tpl->assign_block_vars('header_row', array(
            'HEADER' => $v[0]
        ));

        foreach ( $v as $k2 => $row )
        {
            // Ignore the first element (header)
            if ( $k2 == 0 )
            {
                continue;
            }

            // Show the link if they have permission to use it
            if ( ($row['check'] == '') || ($user->check_auth($row['check'], false)) )
            {
                $tpl->assign_block_vars('header_row.menu_row', array(
                    'ROW_CLASS' => $eqdkp->switch_row_class(),
                    'LINK'      => '<a href="' . $row['link'] . '">' . $row['text'] . '</a>'
                ));
            }
        }
    }

    $tpl->assign_vars(array(
        'L_ADMINISTRATION' => $user->lang['administration'],
        'L_ADMIN_INDEX'    => $user->lang['admin_index'],
        'L_EQDKP_INDEX'    => $user->lang['eqdkp_index']
    ));
}