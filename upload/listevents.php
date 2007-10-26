<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * listevents.php
 * Began: Fri December 20 2002
 *
 * $Id$
 *
 ******************************/

define('EQDKP_INC', true);
$eqdkp_root_path = './';
include_once($eqdkp_root_path . 'common.php');

$user->check_auth('u_event_list');

$sort_order = array(
    0 => array('event_name', 'event_name desc'),
    1 => array('event_value desc', 'event_value')
);

$current_order = switch_order($sort_order);

$total_events = $db->query_first("SELECT COUNT(*) FROM __events");

$start = $in->get('start', 0);

$sql = "SELECT event_id, event_name, event_value
        FROM __events
        ORDER BY {$current_order['sql']}
        LIMIT {$start},{$user->data['user_elimit']}";

if ( !($events_result = $db->query($sql)) )
{
    message_die('Could not obtain event information', '', __FILE__, __LINE__, $sql);
}
while ( $event = $db->fetch_record($events_result) )
{
    $tpl->assign_block_vars('events_row', array(
        'ROW_CLASS'    => $eqdkp->switch_row_class(),
        'U_VIEW_EVENT' => event_path($event['event_id']),
        'NAME'         => sanitize($event['event_name']),
        'VALUE'        => number_format($event['event_value'], 2)
    ));
}
$db->free_result($events_result);

$tpl->assign_vars(array(
    'L_NAME'  => $user->lang['name'],
    'L_VALUE' => $user->lang['value'],

    'O_NAME'  => $current_order['uri'][0],
    'O_VALUE' => $current_order['uri'][1],

    'U_LIST_EVENTS' => event_path() . '&amp;',

    'START'                => $start,
    'LISTEVENTS_FOOTCOUNT' => sprintf($user->lang['listevents_footcount'], $total_events, $user->data['user_elimit']),
    'EVENT_PAGINATION'     => generate_pagination(event_path() . path_params(URI_ORDER, $current_order['uri']['current']), $total_events, $user->data['user_elimit'], $start)
));

$eqdkp->set_vars(array(
    'page_title'    => page_title($user->lang['listevents_title']),
    'template_file' => 'listevents.html',
    'display'       => true
));