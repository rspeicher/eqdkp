<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        addraid.php
 * Began:       Mon Dec 23 2002
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */
 
define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = './../';
include_once($eqdkp_root_path . 'common.php');

class Add_Raid extends EQdkp_Admin
{
    var $raid     = array();            // Holds raid data if URI_RAID is set               @var raid
    var $old_raid = array();            // Holds raid data from before POST                 @var old_raid
    
    function add_raid()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;
        global $SID;
        
        parent::eqdkp_admin();
        
        $this->raid = array(
            'raid_date'      => ( !$this->url_id ) ? $this->time : '',
            'raid_attendees' => $in->get('raid_attendees'),
            'raid_name'      => $in->getArray('raid_name', 'string'),
            'raid_note'      => $in->get('raid_note'),
            'raid_value'     => $in->get('raid_value') // Can't get this field as a float, bleh
        );
        
        // Vars used to confirm deletion
        $this->set_vars(array(
            'confirm_text'  => $user->lang['confirm_delete_raid'],
            'uri_parameter' => URI_RAID
        ));
        
        $this->assoc_buttons(array(
            'add' => array(
                'name'    => 'add',
                'process' => 'process_add',
                'check'   => 'a_raid_add'
            ),
            'update' => array(
                'name'    => 'update',
                'process' => 'process_update',
                'check'   => 'a_raid_upd'
            ),
            'delete' => array(
                'name'    => 'delete',
                'process' => 'process_delete',
                'check'   => 'a_raid_del'
            ),
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_raid_'
            )
        ));
        
        // Build the raid array
        // ---------------------------------------------------------
        if ( $this->url_id )
        {
            $sql = "SELECT raid_id, raid_name, raid_date, raid_note, raid_value
                    FROM __raids
                    WHERE (`raid_id` = '{$this->url_id}')";
            $result = $db->query($sql);
            if ( !$row = $db->fetch_record($result) )
            {
                message_die($user->lang['error_invalid_raid_provided']);
            }
            $db->free_result($result);
        
            $this->time = $row['raid_date'];
            $this->raid = array(
                'raid_name'  => $in->get('raid_name', $row['raid_name']),
                'raid_note'  => $in->get('raid_note', $row['raid_note']),
                'raid_value' => $in->get('raid_value', floatval($row['raid_value']))
            );
            
            $attendees = $in->get('raid_attendees');
            if ( empty($attendees) )
            {
                $attendees = array();
                $sql = "SELECT member_name
                        FROM __raid_attendees
                        WHERE (`raid_id` = '{$this->url_id}')
                        ORDER BY member_name";
                $result = $db->query($sql);
                while ( $row = $db->fetch_record($result) )
                {
                    $attendees[] = $row['member_name'];
                }
            }
            $this->raid['raid_attendees'] = ( is_array($attendees) ) ? implode("\n", $attendees) : $attendees;
            unset($attendees);
        }
    }
    
    function error_check()
    {
        global $user, $in;
        
        //setlocale(LC_ALL, 'de_DE'); // NOTE: I have no idea why this is here either. Thanks, 1.3!
        // $this->fv->is_alpha('raid_attendees',  $user->lang['fv_alpha_attendees']);
        $this->fv->is_filled('raid_attendees', $user->lang['fv_required_attendees']);
    
        $this->fv->is_within_range('mo', 1, 12,      $user->lang['fv_range_month']);
        $this->fv->is_within_range('d',  1, 31,      $user->lang['fv_range_day']);
        $this->fv->is_within_range('y',  1998, 2010, $user->lang['fv_range_year']); // How ambitious
        $this->fv->is_within_range('h',  0, 23,      $user->lang['fv_range_hour']);
        $this->fv->is_within_range('mi', 0, 59,      $user->lang['fv_range_minute']);
        $this->fv->is_within_range('s',  0, 59,      $user->lang['fv_range_second']);
        
        $raid_value = $in->get('raid_value');
        if ( !empty($raid_value) )
        {
            $this->fv->is_number('raid_value', $user->lang['fv_number_value']);
        }
    
        $raid_name = $in->getArray('raid_name', 'string');
        if ( empty($raid_name) )
        {
            $this->fv->errors['raid_name'] = $user->lang['fv_required_event_name'];
        }
        
        // FIXME: If we enter an invalid value in a date field, an error is generated, but we get back a bogus date
        $this->time = mktime($in->get('h', 0), $in->get('mi', 0), $in->get('s', 0),
            $in->get('mo', 0), $in->get('d', 0), $in->get('y', 0)
        );
        
        return $this->fv->is_error();
    }
    
    // ---------------------------------------------------------
    // Process Add
    // ---------------------------------------------------------
    function process_add()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;
        global $SID;
        
        $success_message = '';
        $this_raid_id    = 0;
        
        //
        // Raid loop for multiple events
        //
        $raid_names = $in->getArray('raid_name', 'string');
        foreach ( $raid_names as $raid_name )
        {
            // Get the raid value
            $raid_value = $this->get_raid_value($raid_name);
            
            // Insert the raid to get the raid's ID for attendees
            $query = $db->build_query('INSERT', array(
                'raid_name'     => $raid_name,
                'raid_date'     => $this->time,
                'raid_note'     => $in->get('raid_note'),
                'raid_value'    => $raid_value,
                'raid_added_by' => $this->admin_user
            ));
            $db->query("INSERT INTO __raids {$query}");
            $this_raid_id = $db->insert_id();
            
            // Attendee handling
            $raid_attendees = $this->prepare_attendees();
            
            // Adds attendees to __raid_attendees; adds/updates Member entries as necessary
            $this->process_attendees($raid_attendees, $this_raid_id, $raid_value);
            
            // Update firstraid / lastraid / raidcount
            $this->update_member_cache($raid_attendees);
            
            // Call plugin add hooks
            $pm->do_hooks('/admin/addraid.php?action=add');
            
            //
            // Logging
            //
            $log_action = array(
                'header'        => '{L_ACTION_RAID_ADDED}',
                'id'            => $this_raid_id,
                '{L_EVENT}'     => $raid_name,
                '{L_ATTENDEES}' => implode(', ', $raid_attendees),
                '{L_NOTE}'      => $in->get('raid_note'),
                '{L_VALUE}'     => $raid_value,
                '{L_ADDED_BY}'  => $this->admin_user
            );
            $this->log_insert(array(
                'log_type'   => $log_action['header'],
                'log_action' => $log_action
            ));
            
            //
            // Append success message
            //
            $success_message .= sprintf($user->lang['admin_add_raid_success'], date($user->style['date_notime_short'], $this->time), sanitize($raid_name)) . '<br />';
            
            unset($raid_value);
        } // Raid loop
        
        //
        // Update player status if needed
        //
        if ( $eqdkp->config['hide_inactive'] == 1 )
        {
            $success_message .= '<br /><br />' . $user->lang['admin_raid_success_hideinactive'];
            $this->update_member_status();
            $success_message .= ' ' . strtolower($user->lang['done']);
        }
        
        //
        // Success message
        //
        $link_list = array(
            $user->lang['add_items_from_raid'] => 'additem.php' . $SID . '&amp;raid_id=' . $this_raid_id,
            $user->lang['add_raid']            => 'addraid.php' . $SID,
            $user->lang['list_raids']          => 'listraids.php' . $SID
        );
        $this->admin_die($success_message, $link_list);
    }
    
    // ---------------------------------------------------------
    // Process Update
    // ---------------------------------------------------------
    function process_update()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;
        global $SID;
        
        // Get the old data
        $this->get_old_data();
        $old_raid_attendees = explode(',', $this->old_raid['raid_attendees']);
        
        // Get the raid value
        $raid_value = $this->get_raid_value($in->get('raid_name'));
        
        // Attendee handling
        $raid_attendees = $this->prepare_attendees();
        
        // NOTE: When $old is the first argument, we will not needlessly delete new attendees that aren't in the table to begin with
        $remove_attendees = array_diff($old_raid_attendees, $raid_attendees);
        
        ## ####################################################################
        ## 'Undo' the raid from old attendees
        ## ####################################################################
        
        // Remove the attendees from the old raid
        $sql = "DELETE FROM __raid_attendees
                WHERE (`raid_id` = '{$this->url_id}')
                AND (`member_name` IN ('" . $db->escape("','", $remove_attendees) . "'))";
        $db->query($sql);
        
        // Remove the value of the old raid from the old attendees' earned
        $sql = "UPDATE __members
                SET `member_earned` = `member_earned` - {$this->old_raid['raid_value']}
                WHERE (`member_name` IN ('" . $db->escape("','", $old_raid_attendees) . "'))";
        $db->query($sql);
        
        ## ####################################################################
        ## Update the array with current data
        ## ####################################################################
        
        //
        // Update the raid
        //
        $query = $db->build_query('UPDATE', array(
            'raid_date'       => $this->time,
            'raid_note'       => $in->get('raid_note'),
            'raid_value'      => $raid_value,
            'raid_name'       => $in->get('raid_name'),
            'raid_updated_by' => $this->admin_user
        ));
        $db->query("UPDATE __raids SET {$query} WHERE (`raid_id` = '{$this->url_id}')");
        
        // Replaces attendee entries in __raid_attendees; adds/updates Member entries as necessary
        $this->process_attendees($raid_attendees, $this->url_id, $raid_value);
        
        // Update firstraid / lastraid / raidcount
        // NOTE: Merge these because if we delete someone, we want their cache udpated as well!
        $this->update_member_cache(array_merge($raid_attendees, $remove_attendees));
        
        // Call plugin update hooks
        $pm->do_hooks('/admin/addraid.php?action=update');
        
        // Logging
        $log_action = array(
            'header'               => '{L_ACTION_RAID_UPDATED}',
            'id'                   => $this->url_id,
            '{L_EVENT_BEFORE}'     => $this->old_raid['raid_name'],
            '{L_ATTENDEES_BEFORE}' => implode(', ', $this->find_difference($raid_attendees, $old_raid_attendees)),
            '{L_NOTE_BEFORE}'      => $this->old_raid['raid_note'],
            '{L_VALUE_BEFORE}'     => $this->old_raid['raid_value'],
            '{L_EVENT_AFTER}'      => $this->find_difference($this->old_raid['raid_name'], $in->get('raid_name')),
            '{L_ATTENDEES_AFTER}'  => implode(', ', $this->find_difference($old_raid_attendees, $raid_attendees)),
            '{L_NOTE_AFTER}'       => $this->find_difference($this->old_raid['raid_note'], $in->get('raid_note')),
            '{L_VALUE_AFTER}'      => $this->find_difference($this->old_raid['raid_value'], $raid_value),
            '{L_UPDATED_BY}'       => $this->admin_user
        );
        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action
        ));
        
        //
        // Success message
        //
        $success_message = sprintf($user->lang['admin_update_raid_success'], date($user->style['date_notime_short'], $this->time), sanitize($in->get('raid_name')));
        
        // Update player status if needed
        if ( $eqdkp->config['hide_inactive'] == 1 )
        {
            $success_message .= '<br /><br />' . $user->lang['admin_raid_success_hideinactive'];
            $this->update_member_status();
            $success_message .= ' ' . strtolower($user->lang['done']);
        }
        
        $link_list = array(
            $user->lang['add_items_from_raid'] => 'additem.php' . $SID . '&amp;raid_id=' . $this->url_id,
            $user->lang['add_raid']            => 'addraid.php' . $SID,
            $user->lang['list_raids']          => 'listraids.php' . $SID
        );
        $this->admin_die($success_message, $link_list);
    }
    
    // ---------------------------------------------------------
    // Process Delete (confirmed)
    // ---------------------------------------------------------
    function process_confirm()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;
        
        //
        // Get the old data
        //
        $this->get_old_data();
        $raid_attendees = explode(',', $this->old_raid['raid_attendees']);
        
        //
        // Take the value away from the attendees
        //
        $sql = "UPDATE __members
                SET `member_earned` = `member_earned` - {$this->old_raid['raid_value']},
                    `member_raidcount` = `member_raidcount` - 1
                WHERE (`member_name` IN ('" . $db->escape("','", $raid_attendees) . "'))";
        $db->query($sql);
        
        //
        // Remove cost of items from this raid from buyers
        //
        $sql = "SELECT item_id, item_buyer, item_value
                FROM __items
                WHERE (`raid_id` = '{$this->url_id}')";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $item_value = ( !empty($row['item_value']) ) ? floatval($row['item_value']) : 0.00;
            
            // One less query if there's no value to remove
            if ( $item_value > 0 )
            {
                $sql = "UPDATE __members
                        SET `member_spent` = `member_spent` - {$item_value}
                        WHERE (`member_name` = '{$row['item_buyer']}')";
                $db->query($sql);
            }
        }
        $db->free_result($result);
        
        // Delete associated items
        $db->query("DELETE FROM __items WHERE (`raid_id` = '{$this->url_id}')");
        
        // Delete attendees
        $db->query("DELETE FROM __raid_attendees WHERE (`raid_id` = '{$this->url_id}')");
        
        // Remove the raid itself
        $db->query("DELETE FROM __raids WHERE (`raid_id` = '{$this->url_id}')");
        
        // Update firstraid / lastraid / raidcount
        $this->update_member_cache($raid_attendees);
        
        // Call plugin delete hooks
        $pm->do_hooks('/admin/addraid.php?action=delete');
        
        //
        // Logging
        //
        $log_action = array(
            'header'        => '{L_ACTION_RAID_DELETED}',
            'id'            => $this->url_id,
            '{L_EVENT}'     => $this->old_raid['raid_name'],
            '{L_ATTENDEES}' => implode(', ', $raid_attendees),
            '{L_NOTE}'      => $this->old_raid['raid_note'],
            '{L_VALUE}'     => $this->old_raid['raid_value']
        );
        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action
        ));
        
        //
        // Success message
        //
        $success_message = $user->lang['admin_delete_raid_success'];
        
        // Update player status if needed
        if ( $eqdkp->config['hide_inactive'] == 1 )
        {
            $success_message .= '<br /><br />' . $user->lang['admin_raid_success_hideinactive'];
            $this->update_member_status();
            $success_message .= ' ' . strtolower($user->lang['done']);
        }
        
        $link_list = array(
            $user->lang['add_raid']   => 'addraid.php' . $SID,
            $user->lang['list_raids'] => 'listraids.php' . $SID);
        $this->admin_die($success_message, $link_list);
    }
    
    // ---------------------------------------------------------
    // Process helper methods
    // ---------------------------------------------------------
    /**
     * Populate the old_raid array
     * 
     * @return void
     */
    function get_old_data()
    {
        global $db, $eqdkp, $pm;
        
        $sql = "SELECT raid_name, raid_value, raid_note, raid_date
                FROM __raids
                WHERE (`raid_id` = '{$this->url_id}')";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            // TODO: Find out if we really need addslashes here.
            $this->old_raid = array(
                'raid_name'  => addslashes($row['raid_name']),
                'raid_value' => floatval($row['raid_value']),
                'raid_note'  => addslashes($row['raid_note']),
                'raid_date'  => intval($row['raid_date'])
            );
        }
        $db->free_result($result);
        
        $attendees = array();
        $sql = "SELECT r.member_name
                FROM __raid_attendees AS r, __members AS m
                WHERE (m.`member_name` = r.`member_name`)
                AND (`raid_id` = '{$this->url_id}')
                ORDER BY `member_name`";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $attendees[] = $row['member_name'];
        }
        $this->old_raid['raid_attendees'] = implode(',', $attendees);
        unset($attendees);
    }
    
    /**
     * Fetch a events's value, given its name, or a user-supplied value if set
     *
     * @param string $raid_name Raid (event) name
     * @return float
     */
    function get_raid_value($raid_name)
    {
        global $db, $in;
        
        $raid_name = $db->escape($raid_name);
        
        $sql = "SELECT event_value
                FROM __events
                WHERE (`event_name` = '{$raid_name}')";
        $preset_value = $db->query_first($sql);
        
        // Use the input value to perform a one-time change, if provided
        $input_value = $in->get('raid_value');
        
        $raid_value = ( empty($input_value) ) ? $preset_value : $input_value;
        
        return floatval($raid_value);
    }
    
    /**
    * Get member level/race/class from database or SESSION data
    * 
    * @param $member_name
    * @return Array
    */
    // TODO: Refactor
    function get_member_info($member_name)
    {
        global $db, $eqdkp;
        
        // The code below is too stupid to be trusted with using any data it may return
        return array();

        // FIXME: Injections
        if ($_SESSION[$member_name]['class'] == "")
        {
            unset($_SESSION[$member_name]['class']);
        }

        if ($_SESSION[$member_name]['level'] == "")
        {
            unset($_SESSION[$member_name]['level']);
        }

        if ($_SESSION[$member_name]['race'] == "") 
        {
            unset($_SESSION[$member_name]['race']);
        }

        if ( isset($_SESSION[$member_name]['level']) && isset($_SESSION[$member_name]['class']) )
        {
            // Why are we looking it up when it's set?
            $sql = "SELECT race_name FROM __races WHERE `race_name` = '" . $_SESSION[$member_name]['race'] . "'";
            $race_name = $db->query_first($sql);

            if (!isset($race_name))
            {
                $race_name = "Unknown";
            }

            $retval = array(
                'name'  => $_SESSION[$member_name],
                'level' => ( isset($_SESSION[$member_name]['level']) ) ? $_SESSION[$member_name]['level'] : false,
                'race'  => $race_name,
                'class' => ( isset($_SESSION[$member_name]['class']) ) ? $_SESSION[$member_name]['class'] : false
            );
            unset($_SESSION[$member_name]);
        }
        else 
        {
            // Why don't we just lookup everything here instead of using the pointless block above?
            $sql = "SELECT member_name, member_race_id, member_class_id, member_level 
                    FROM __members
                    WHERE `member_name` = '{$member_name}'";
            $result = $db->query($sql);
            $info = $db->fetch_record($result);

            if (!isset($info['member_level'])) 
            {
                $member_level = "1";
            }

            $sql = "SELECT race_name FROM __races WHERE `race_id` = '{$info['member_race_id']}'";
            $race_name = $db->query_first($sql);

            if (!isset($race_name))
            {
                $race_name = "Unknown";
            }
    
            $sql = "SELECT class_name FROM __classes WHERE `class_id` = '{$info['member_class_id']}'";
            $class_name = $db->query_first($sql);

            if (!isset($class_name))
            {
                $class_name = "Unknown";
            }

            $retval = array(
                'name'  => $member_name,
                'race'  => $race_name,
                'level' => $member_level,
                'class' => $class_name
            );
        }

        return $retval;
    }
    
    /**
     * Grabs raid attendees from Input and puts them in a format for use
     * elsewhere in the class.
     *
     * @return array
     */
    function prepare_attendees()
    {
        global $in;
        
        // Input should be a newline-separated list of attendee names
        $retval = $in->get('raid_attendees');
        
        // Replace any space character (including newlines) with a single space
        $retval = preg_replace('/\s+/', ' ', $retval);
        
        $retval = explode(' ', $retval);
        foreach ( $retval as $k => $v )
        {
            $v = trim($v);
            $v = ucfirst(strtolower($v));
            
            if ( !empty($v) )
            {
                $retval[$k] = $v;
            }
            else
            {
                unset($retval[$k]);
            }
        }

        $retval = array_unique($retval);
        sort($retval);
        reset($retval);
        
        return $retval;
    }
    
    // TODO: Refactor
    function process_attendees($att_array, $raid_id, $raid_value)
    {
        global $db, $eqdkp, $user;
        
        $raid_id    = intval($raid_id);
        $raid_value = floatval($raid_value);
        
        // Gather data about our attendees that we'll need to rebuild their records
        // This has to be done because REPLACE INTO deletes the record before re-inserting it,
        // meaning we lose the member's data and the default values get used (BAD!)
        $att_data = array();
        $sql = "SELECT *
                FROM __members
                WHERE (`member_name` IN ('" . $db->escape("','", $att_array) . "'))
                ORDER BY member_name";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $att_data[ $row['member_name'] ] = $row;
        }
        $db->free_result($result);
        
        foreach ( $att_array as $attendee )
        {
            // Add each attendee to the attendees table for this raid
            $sql = "REPLACE INTO __raid_attendees (raid_id, member_name)
                    VALUES ('{$raid_id}', '" . $db->escape($attendee) . "')";
            $db->query($sql);
            
            // Set the bare-minimum values for a new member
            $row = array(
                'member_name' => $attendee,
            );
            
            if ( isset($att_data[$attendee]) )
            {
                // Data exists - Update member's values
                
                // Inject our saved data into our row that gets updated
                $row = array_merge($row, $att_data[$attendee]);
                
                // Some of our values need to be updated, so do that!
                $row['member_earned'] = floatval($row['member_earned']) + $raid_value;
                
                $query = $db->build_query('UPDATE', $row);
                $sql = "UPDATE __members SET {$query} WHERE (`member_name` = '" . $db->escape($attendee) . "')";
                $db->query($sql);
            }
            else
            {
                // No data exists - Insert member
                $row['member_earned'] = $raid_value;
                
                $query = $db->build_query('INSERT', $row);
                $sql = "INSERT INTO __members {$query}";
                $db->query($sql);
            }
            
            // TODO: SESSION-based Race/Class/Level shit goes here (or likely above, since it will vary with insert/update), since it's per-member
        }
        
        // ---------------------------------------------------------------------
        // OLD METHOD ----------------------------------------------------------
        // ---------------------------------------------------------------------
        // Grab our array of name => class/level/race
        /*
        session_start();
        
        //
        // Handle existing members
        //
        $update_sql_members = array();
        $updated_members    = array();
        $raid_attendees     = array();
        
        $sql = "SELECT m.member_name, m.member_firstraid, m.member_lastraid,
                    m.member_level, r.race_name AS member_race,
                    c.class_name AS member_class, m.member_raidcount
                FROM __members AS m, __classes AS c, __races AS r
                WHERE (r.`race_id` = m.`member_race_id`)
                AND (c.`class_id` = m.`member_class_id`)";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $member_name = trim(str_replace(' ', '', $row['member_name']));
            
            // Make sure the member is in the attendees list before proceeding
            if ( (!in_array($member_name, $att_array)) || (empty($member_name)) )
            {
                continue;
            }
            
            $raid_attendees[] = $member_name;
            
            // raidcount and/or firstraid is 0 - they exist but we need to set their firstraid to this date [ #705206 ]
            if ( ($row['member_raidcount'] == '0') || ($row['member_firstraid'] == '0') )
            {
                $sql = "UPDATE __members
                        SET `member_earned` = `member_earned` + {$raid_value},
                            `member_firstraid` = '{$this->time}',
                            `member_lastraid` = '{$this->time}',
                            `member_raidcount` = `member_raidcount` + 1
                        WHERE `member_name` = '{$member_name}'";
                $db->query($sql);

                $updated_members[] = $member_name;
                continue;
            }
            else
            {
                $updated_members[] = $member_name;
            }
            
            // Check for race/class/level data for this member
            // TODO: Can't even tell how this works anymore. That's a bad sign.
            $member_data = $this->get_member_info($member_name);

            if ( (!(isset($member_data['race']) )) ||  $member_data['race'] == 'Unknown'  )
            {
                $member_data['race'] = $row['member_race'];
            }

            if ( (!(isset($member_data['class']) )) ||  $member_data['class'] == 'Unknown' )
            {
                $member_data['class'] = $row['member_class'];
            }

            $member_level = ( is_numeric($member_data['level']) ) ? trim($member_data['level']) : 'member_level';
            $member_race  = ( is_string($member_data['race']) )   ? trim($member_data['race'])  : 'member_race';
            $member_class = ( is_string($member_data['class']) )  ? trim($member_data['class']) : 'member_class';
            unset($member_data);
            
            // Update this member's race/class/level if they changed
            $time_check  = ( $process == 'process_add' ) ? ($this->time > $row['member_lastraid']) : ($this->time <= $row['member_lastraid']);
            $level_check = ( ($member_level != $row['member_level']) && ($member_level != 'member_level') ) ? true : false;
            $race_check  = ( ($member_race  != $row['member_race'])  && ($member_race  != 'member_race') )  ? true : false;
            $class_check = ( ($member_class != $row['member_class']) && ($member_class != 'member_class') ) ? true : false;
            
            if ( ($time_check) && ($level_check || $race_check || $class_check) )
            {
                // For comparison, quotes need to be added after the if statement above
                $member_level = ( $member_level != 'member_level' ) ? "'{$member_level}'" : $member_level;
                $member_race  = ( $member_race  != 'member_race'  ) ? "'{$member_race}'"  : $member_race;
                $member_class = ( $member_class != 'member_class' ) ? "'{$member_class}'" : $member_class;

                // Process the update
                $sql  = "UPDATE __members AS m, __classes AS c, __races AS r
                         SET m.`member_earned` = m.`member_earned` + {$raid_value},";
                         
                // Do not update their lastraid if it's greater than this raid's date [ #749201 ]
                if ( $row['member_lastraid'] < $this->time )
                {
                    $sql .= "m.`member_lastraid` = '{$this->time}', ";
                }
                
                $sql .= "    m.`member_raidcount` = m.`member_raidcount` + 1,
                             m.`member_level` = '{$member_level}',
                             m.`member_race_id` = r.`race_id`, 
                             m.`member_class_id` = c.`class_id`
                        WHERE r.`race_name` = {$member_race}
                        AND c.`class_name` = {$member_class}
                        AND m.`member_name` = '{$member_name}'";
                $db->query($sql);
            }
            // If they didn't, their update is lumped into $update_sql (below)
            else
            {
                $update_sql_members[] = $member_name;
            }
        }
        $db->free_result($result);
        session_destroy();
        
        // Run the lump update if we need to
        if ( sizeof($update_sql_members) > 0 )
        {
            $sql = "UPDATE __members
                    SET `member_raidcount` = `member_raidcount` + 1,
                        `member_earned` = `member_earned` + {$raid_value}
                    WHERE `member_name` IN ('" . implode("','", $update_sql_members) . "')";
            $db->query($sql);
        }
        
        //
        // Update firstraid / lastraid [ #749201 ]
        //
        $this->update_member_firstraid($raid_attendees, $this->time);
        $this->update_member_lastraid($raid_attendees,  $this->time);
               
        //
        // Handle new members
        //
        $new_members = array_diff($att_array, $updated_members);
        foreach ( $new_members as $member_name )
        {
            $member_name = trim($member_name);
            if ( $member_name != '' )
            {
                $member_data2 = $this->get_member_info($member_name);
                
                // TODO: 1.3 cleanup

                $class = $member_data2['class'];
                $race = $member_data2['race'];
    
                if ( ! ( isset($class) ) || ($class == "") ) {
                    $class = "Unknown";
                }
        
                $class_id_number = $db->query_first("SELECT class_id FROM __classes WHERE `class_name` = '{$class}'");
                $race_id_number = $db->query_first("SELECT race_id FROM __races WHERE `race_name` = '{$race}'");
        
                if (!isset($race_id_number)) {
                    $race_id_number = 0;
                }

                if (!isset($class_id_number)) {
                    $class_id_number = 0;
                }
    
                $query = $db->build_query('INSERT', array(
                    'member_name'      => $member_name,
                    'member_earned'    => $raid_value,
                    'member_status'    => '1',
                    'member_firstraid' => $this->time,
                    'member_lastraid'  => $this->time,
                    'member_raidcount' => '1',
                    'member_level'     => $member_data2['level'], // TODO: Huh?
                    'member_race_id'   => $race_id_number,
                    'member_class_id'  => $class_id_number,
                    'member_rank_id'   => '0')
                );
                $db->query("INSERT INTO __members {$query}");
            }
        }
        
        // For any member who has a 0 raidcount, reset their first/last raid to 0
        $sql = "UPDATE __members
                SET `member_firstraid` = 0, `member_lastraid` = 0
                WHERE `member_raidcount` = 0";
        $db->query($sql);
        */
    }
    
    /**
     * Recalculates and updates the first and last raids and raid counts for each
     * member in $att_array
     *
     * @param string $att_array Array of raid attendees
     * @return void
     */
    function update_member_cache($att_array)
    {
        global $db;
        
        if ( !is_array($att_array) || count($att_array) == 0 )
        {
            return;
        }
        
        $sql = "SELECT m.member_name, MIN(r.raid_date) AS firstraid, 
                    MAX(r.raid_date) AS lastraid, COUNT(r.raid_id) AS raidcount
                FROM __members AS m
                LEFT JOIN __raid_attendees AS ra ON m.member_name = ra.member_name
                LEFT JOIN __raids AS r on ra.raid_id = r.raid_id
                WHERE (m.`member_name` IN ('" . $db->escape("','", $att_array) . "'))
                GROUP BY m.member_name";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $db->query("UPDATE __members SET :params WHERE (`member_name` = '" . $db->escape($row['member_name']) . "')", array(
                'member_firstraid' => $row['firstraid'],
                'member_lastraid'  => $row['lastraid'],
                'member_raidcount' => $row['raidcount']
            ));
        }
        $db->free_result($result);
    }
    
    /**
     * Update active/inactive player status, inserting adjustments if necessary
     * 
     * @return void
     */
    function update_member_status()
    {
        global $db, $eqdkp, $user;
        
        // Timestamp for the active/inactive threshold; members with a lastraid before this date are inactive
        $inactive_time = strtotime(date('Y-m-d', time() - 60 * 60 * 24 * $eqdkp->config['inactive_period']));
        $current_time  = time();

        $active_members   = array();
        $inactive_members = array();
        
        $active_adj   = floatval($eqdkp->config['active_point_adj']);
        $inactive_adj = floatval($eqdkp->config['inactive_point_adj']);
        
        // Don't go through this whole thing of active/inactive adjustments if we don't need to.
        if ( $active_adj > 0 || $inactive_adj > 0 )
        {
            $sql = "SELECT member_name, member_status, member_lastraid
                    FROM __members";
            $result = $db->query($sql);
            while ( $row = $db->fetch_record($result) )
            {
                unset($adj_value, $adj_reason);
                
                // Active -> Inactive
                if ( $inactive_adj > 0 && $row['member_status'] == '1' && $row['member_lastraid'] < $inactive_time )
                {
                    $adj_value  = $eqdkp->config['inactive_point_adj'];
                    $adj_reason = 'Inactive adjustment'; // TODO: Localize
                    
                    $inactive_members[] = $row['member_name'];
                }
                // Inactive -> Active
                elseif ( $active_adj > 0 && $row['member_status'] == '0' && $row['member_lastraid'] >= $inactive_time )
                {
                    $adj_value  = $eqdkp->config['active_point_adj'];
                    $adj_reason = 'Active adjustment'; // TODO: Localize
                    
                    $active_members[] = $row['member_name'];
                }
                
                //
                // Insert individual adjustment
                //
                if ( isset($adj_value) && isset($adj_reason) )
                {
                    $group_key = $this->gen_group_key($current_time, $adj_reason, $adj_value);

                    $query = $db->build_query('INSERT', array(
                        'adjustment_value'     => $adj_value,
                        'adjustment_date'      => $current_time,
                        'member_name'          => $row['member_name'],
                        'adjustment_reason'    => $adj_reason,
                        'adjustment_group_key' => $group_key,
                        'adjustment_added_by'  => $user->data['username']
                    ));
                    $db->query("INSERT INTO __adjustments {$query}");
                }
            }
            
            // Update inactive members' adjustment
            if ( count($inactive_members) > 0 )
            {
                $adj_value  = $eqdkp->config['inactive_point_adj'];
                $adj_reason = 'Inactive adjustment';
                
                $sql = "UPDATE __members
                        SET `member_status` = 0, `member_adjustment` = `member_adjustment` + {$eqdkp->config['inactive_point_adj']}
                        WHERE (`member_name` IN ('" . $db->escape("','", $inactive_members) . "'))";
                        
                $log_action = array(
                    'header'         => '{L_ACTION_INDIVADJ_ADDED}',
                    '{L_ADJUSTMENT}' => $eqdkp->config['inactive_point_adj'],
                    '{L_MEMBERS}'    => implode(', ', $inactive_members),
                    '{L_REASON}'     => 'Inactive adjustment', // TODO: Localize
                    '{L_ADDED_BY}'   => $user->data['username']
                );
                $this->log_insert(array(
                    'log_type'   => $log_action['header'],
                    'log_action' => $log_action
                ));
            }
            
            // Update active members' adjustment
            if ( count($active_members) > 0 )
            {
                $sql = "UPDATE __members
                        SET `member_status` = 1, `member_adjustment` = `member_adjustment` + {$eqdkp->config['active_point_adj']}
                        WHERE (`member_name` IN ('" . $db->escape("','", $active_members) . "'))";
                $db->query($sql);
                
                $log_action = array(
                    'header'         => '{L_ACTION_INDIVADJ_ADDED}',
                    '{L_ADJUSTMENT}' => $eqdkp->config['active_point_adj'],
                    '{L_MEMBERS}'    => implode(', ', $active_members),
                    '{L_REASON}'     => 'Active adjustment', // TODO: Localize
                    '{L_ADDED_BY}'   => $user->data['username']
                );
                $this->log_insert(array(
                    'log_type'   => $log_action['header'],
                    'log_action' => $log_action
                ));
            }
        }
        else
        {
            // We're not dealing with active/inactive adjustments, so just update the status field
            
            // Active -> Inactive
            $db->query("UPDATE __members SET `member_status` = '0' WHERE (`member_lastraid` < {$inactive_time}) AND (`member_status` = 1)");
        
            // Inactive -> Active
            $db->query("UPDATE __members SET `member_status` = '1' WHERE (`member_lastraid` >= {$inactive_time}) AND (`member_status` = 0)");
        }

        /*
        // If your class_id doesn't match your level, update your class ID to the one that has
        // the same class_name, but the correct min and max level.
        // TODO: 1.3 cleanup
        $sql = "SELECT m.member_name, m.member_level, c.class_name, c.class_id, c.class_min_level, c.class_max_level
                FROM __members AS m, __classes AS c
                WHERE m.`member_class_id` = c.`class_id`";
        $result = $db->query($sql);

        while ( $row = $db->fetch_record($result) )
        {
            if ( isset($row['member_level']) && ($row['member_level'] > $row['class_max_level'] || $row['member_level'] < $row['class_min_level']))
            {
                $sql = "SELECT class_id
                        FROM __classes
                        WHERE `class_name` = '{$row['class_name']}'
                        AND `class_min_level` < '{$row['member_level']}'
                        AND `class_max_level` >= '{$row['member_level']}'";
                $new_class_id = $db->query_first($sql);

                if (!isset($new_class_id))
                {
                    $new_class_id = 0;
                }

                $sql = "UPDATE __members
                        SET `member_class_id` = '{$new_class_id}'
                        WHERE `member_name` = '{$row['member_name']}'";
                $db->query($sql);
            }
        }
        */
    }
    
    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;
        
        //
        // Find the value of the event, or use the one-time value from the form
        //
        $raid_name = $this->raid['raid_name'];
        if ( is_array($this->raid['raid_name']) )
        {
            if ( count($this->raid['raid_name']) > 0 )
            {
                $raid_name = $this->raid['raid_name'][0];
            }
            else
            {
                $raid_name = '';
            }
        }
        
        // This value is what we expect it to be based on the event's name
        $preset_value = $db->query_first("SELECT event_value FROM __events WHERE (`event_name` = '" . $db->escape($raid_name) . "')");
        $raid_value = ( $this->raid['raid_value'] == 0 )             ? '' : $this->raid['raid_value'];
        $raid_value = ( $this->raid['raid_value'] == $preset_value ) ? '' : $this->raid['raid_value'];
        
        // Use the preset value unless the user supplied a one-time change
        // $raid_value = ( $this->raid['raid_value'] != $preset_value ) ? $this->raid['raid_value'] : $preset_value;
        
        //
        // Build member drop-down
        //
        $sql = "SELECT member_name
                FROM __members
                ORDER BY member_name";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $tpl->assign_block_vars('members_row', array(
                'VALUE'  => sanitize($row['member_name'], ENT),
                'OPTION' => $row['member_name'])
            );
        }
        $db->free_result($result);
        
        //
        // Build event drop-down
        //
        $max_length = strlen(strval($db->query_first("SELECT MAX(event_value) FROM __events")));

        $sql = "SELECT event_id, event_name, event_value
                FROM __events
                ORDER BY event_name";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $selected = '';
            
            if ( is_array($this->raid['raid_name']) )
            {
                $selected = option_selected(in_array($row['event_name'], $this->raid['raid_name']));
            }
            else
            {
                $selected = option_selected($row['event_name'] == $this->raid['raid_name']);
            }
            
            $event_value = number_format($row['event_value'], 2);
            
            $tpl->assign_block_vars('events_row', array(
                'VALUE'    => sanitize($row['event_name'], ENT),
                'SELECTED' => $selected,
                // NOTE: Kinda pointless since the select box isn't fixed width!
                'OPTION'   => str_pad($event_value, $max_length, ' ', STR_PAD_LEFT) . ' - ' . sanitize($row['event_name'])
            ));
        }
        $db->free_result($result);
        
        $tpl->assign_vars(array(
            // Form vars
            'F_ADD_RAID'       => 'addraid.php' . $SID,
            'RAID_ID'          => $this->url_id,
            'U_ADD_EVENT'      => 'addevent.php'.$SID,
            'S_EVENT_MULTIPLE' => ( !$this->url_id ) ? true : false,
            
            // Form values
            'RAID_ATTENDEES' => $this->raid['raid_attendees'],
            'RAID_VALUE'     => ( is_numeric($raid_value) ) ? number_format(floatval($raid_value), 2) : '',
            'RAID_NOTE'      => sanitize($this->raid['raid_note'], ENT),
            'MO'             => date('m', $this->time),
            'D'              => date('d', $this->time),
            'Y'              => date('Y', $this->time),
            'H'              => date('H', $this->time),
            'MI'             => date('i', $this->time),
            'S'              => date('s', $this->time),
            
            // Language
            'L_ADD_RAID_TITLE'        => $user->lang['addraid_title'],
            'L_ATTENDEES'             => $user->lang['attendees'],
            'L_PARSE_LOG'             => $user->lang['parse_log'],
            'L_SEARCH_MEMBERS'        => $user->lang['search_members'],
            'L_EVENT'                 => $user->lang['event'],
            'L_ADD_EVENT'             => strtolower($user->lang['add_event']),
            'L_VALUE'                 => $user->lang['value'],
            'L_ADDRAID_VALUE_NOTE'    => $user->lang['addraid_value_note'],
            'L_DATE'                  => $user->lang['date'],
            'L_TIME'                  => $user->lang['time'],
            'L_ADDRAID_DATETIME_NOTE' => $user->lang['addraid_datetime_note'],
            'L_NOTE'                  => $user->lang['note'],
            'L_ADD_RAID'              => $user->lang['add_raid'],
            'L_RESET'                 => $user->lang['reset'],
            'L_UPDATE_RAID'           => $user->lang['update_raid'],
            'L_DELETE_RAID'           => $user->lang['delete_raid'],
            
            // Form validation
            'FV_ATTENDEES'  => $this->fv->generate_error('raid_attendees'),
            'FV_EVENT_NAME' => $this->fv->generate_error('raid_name'),
            'FV_VALUE'      => $this->fv->generate_error('raid_value'),
            'FV_MO'         => $this->fv->generate_error('mo'),
            'FV_D'          => $this->fv->generate_error('d'),
            'FV_Y'          => $this->fv->generate_error('y'),
            'FV_H'          => $this->fv->generate_error('h'),
            'FV_MI'         => $this->fv->generate_error('mi'),
            'FV_S'          => $this->fv->generate_error('s'),
            
            // Javascript messages
            'MSG_ATTENDEES_EMPTY' => $user->lang['fv_required_attendees'],
            'MSG_NAME_EMPTY'      => $user->lang['fv_required_event_name'],
            'MSG_GAME_NAME'       => $eqdkp->config['default_game'],
            
            // Buttons
            'S_ADD' => ( !$this->url_id ) ? true : false)
        );
        
        $eqdkp->set_vars(array(
            'page_title'    => page_title($user->lang['addraid_title']),
            'template_file' => 'admin/addraid.html',
            'display'       => true
        ));
    }
}

$add_raid = new Add_Raid;
$add_raid->process();