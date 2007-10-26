<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        listitems.php
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
include_once($eqdkp_root_path . 'common.php');

$user->check_auth('u_item_list');

//
// Item Values (unique items)
//

// TODO: if-else causes two different pages to be rendered. Split into separate files.
if ( $in->get(URI_PAGE, 'values') == 'values' )
{
    $sort_order = array(
        0 => array('item_name', 'item_name desc'),
        1 => array('item_buyer', 'item_buyer desc'),
        2 => array('item_date desc', 'item_date'),
        3 => array('raid_name', 'raid_name desc'),
        4 => array('item_value desc', 'item_value')
     );

    $current_order = switch_order($sort_order);
    
    $u_list_items = item_path() . '&amp;';
    
    $page_title = page_title($user->lang['listitems_title']);
    
    $total_items = $db->num_rows($db->query("SELECT item_id FROM __items GROUP BY item_name"));
    $start = $in->get('start', 0);
    
    // We don't care about history; ignore making the items unique
    $s_history = false;

    $sql = "SELECT i.item_id, i.item_name, i.item_buyer, i.item_date, i.raid_id, 
                MIN(i.item_value) AS item_value, r.raid_name
            FROM __items AS i, __raids AS r
            WHERE (i.raid_id = r.raid_id)
            GROUP BY `item_name`
            ORDER BY {$current_order['sql']}
            LIMIT {$start},{$user->data['user_ilimit']}";
    
    $listitems_footcount = sprintf($user->lang['listitems_footcount'], $total_items, $user->data['user_ilimit']);
    $pagination = generate_pagination(item_path() . path_params(URI_ORDER, $current_order['uri']['current']), 
        $total_items, $user->data['user_ilimit'], $start);
}

//
// Item Purchase History (all items)
//
elseif ( $in->get(URI_PAGE) == 'history' )
{
    $sort_order = array(
        0 => array('item_date desc', 'item_date'),
        1 => array('item_buyer', 'item_buyer desc'),
        2 => array('item_name', 'item_name desc'),
        3 => array('raid_name', 'raid_name desc'),
        4 => array('item_value desc', 'item_value')
    );
    
    $current_order = switch_order($sort_order);

    $u_list_items = item_path() . path_params(URI_PAGE, 'history') . '&amp;';
    
    $page_title = page_title($user->lang['listpurchased_title']);
    
    $total_items = $db->query_first("SELECT COUNT(*) FROM __items");
    $start = $in->get('start', 0);
    
    $s_history = true;
    
    $sql = "SELECT i.item_id, i.item_name, i.item_buyer, i.item_date, i.raid_id, 
                i.item_value, r.raid_name
            FROM __items AS i, __raids AS r
            WHERE (r.`raid_id` = i.`raid_id`)
            ORDER BY {$current_order['sql']}
            LIMIT {$start},{$user->data['user_ilimit']}";
            
    $listitems_footcount = sprintf($user->lang['listpurchased_footcount'], $total_items, $user->data['user_ilimit']);
    $pagination = generate_pagination(item_path() . path_params(array(URI_PAGE => 'history', URI_ORDER => $current_order['uri']['current'])),
        $total_items, $user->data['user_ilimit'], $start);
}

// Regardless of which listitem page they're on, we're essentially 
// outputting the same stuff. Purchase History just has a buyer column.
if ( !($items_result = $db->query($sql)) )
{
    message_die('Could not obtain item information', '', __FILE__, __LINE__, $sql);
}

while ( $item = $db->fetch_record($items_result) )
{
    $tpl->assign_block_vars('items_row', array(
        'ROW_CLASS'    => $eqdkp->switch_row_class(),
        'DATE'         => ( !empty($item['item_date']) ) ? date($user->style['date_notime_short'], $item['item_date']) : '&nbsp;',
        'BUYER'        => ( !empty($item['item_buyer']) ) ? sanitize($item['item_buyer']) : '&lt;<i>Not Found</i>&gt;',
        'U_VIEW_BUYER' => member_path($item['item_buyer']),
        'NAME'         => sanitize($item['item_name']),
        'U_VIEW_ITEM'  => item_path($item['item_id']),
        'RAID'         => ( !empty($item['raid_name']) ) ? sanitize($item['raid_name']) : '&lt;<i>Not Found</i>&gt;',
        'U_VIEW_RAID'  => raid_path($item['raid_id']),
        'VALUE'        => number_format($item['item_value'], 2)
    ));
}
$db->free_result($items_result);

$tpl->assign_vars(array(
    'L_DATE'  => $user->lang['date'],
    'L_BUYER' => $user->lang['buyer'],
    'L_ITEM'  => $user->lang['item'],
    'L_RAID'  => $user->lang['raid'],
    'L_VALUE' => $user->lang['value'],
    
    'O_DATE'  => $current_order['uri'][0],
    'O_BUYER' => $current_order['uri'][1],
    'O_NAME'  => $current_order['uri'][2],
    'O_RAID'  => $current_order['uri'][3],
    'O_VALUE' => $current_order['uri'][4],
    
    'U_LIST_ITEMS' => $u_list_items,
    
    'START'               => $start,
    'S_HISTORY'           => $s_history,
    'LISTITEMS_FOOTCOUNT' => $listitems_footcount,
    'ITEM_PAGINATION'     => $pagination
));

$eqdkp->set_vars(array(
    'page_title'    => $page_title,
    'template_file' => 'listitems.html',
    'display'       => true
));