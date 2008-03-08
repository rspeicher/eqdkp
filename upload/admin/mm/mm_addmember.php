<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        mm_addmember.php
 * Began:       Thu Jan 30 2003
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2008 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */

// This script handles adding, updating or deleting a member.
// NOTE: This script will also process deleting multiple members through the
// mm_listmembers interface

if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

class MM_Addmember extends EQdkp_Admin
{
    var $member     = array();          // Holds member data if URI_NAME is set             @var member
    var $old_member = array();          // Holds member data from before POST               @var old_member

    function mm_addmember()
    {
        global $db, $user, $in;

        parent::eqdkp_admin();

        $this->member = array(
            'member_id'         => 0,
            'member_name'       => $in->get('member_name'),
            'member_earned'     => $in->get('member_earned', 0.00),
            'member_spent'      => $in->get('member_spent', 0.00),
            'member_adjustment' => $in->get('member_adjustment', 0.00),
            'member_current'    => 0.00,
            'member_race_id'    => $in->get('member_race_id', 0),
            'member_class_id'   => $in->get('member_class_id', 0),
            'member_level'      => $in->get('member_level', 0), // TODO: Default level?
            'member_rank_id'    => $in->get('member_rank_id', 0)
        );

        // Vars used to confirm deletion
        $confirm_text = $user->lang['confirm_delete_members'];
        $member_ids = array();
        if ( $in->get('delete', false) )
        {
            // NOTE: We use the misnomer compare_ids because it's just recyling the listmembers template. Oh well.
            $member_ids = $in->getArray('compare_ids', 'int');
            if ( count($member_ids) > 0 )
            {
                $sql = "SELECT member_name
                        FROM __members
                        WHERE (`member_id` IN (" . $db->escape(',', $member_ids) . "))";
                $result = $db->query($sql);
                while ( $row = $db->fetch_record($result) )
                {
                    $members[] = $row['member_name'];
                }

                $names = implode(', ', $members);

                $confirm_text .= '<br /><br />' . $names;
            }
            else
            {
                message_die('No members were selected for deletion.');
            }
        }

        $this->set_vars(array(
            'confirm_text'  => $confirm_text,
            'uri_parameter' => URI_NAME,
            'url_id'        => ( count($member_ids) > 0 ) ? implode(',', $member_ids) : $in->get(URI_NAME),
            'script_name'   => edit_member_path()
        ));

        $this->assoc_buttons(array(
            'add' => array(
                'name'    => 'add',
                'process' => 'process_add',
                'check'   => 'a_members_man'
            ),
            'update' => array(
                'name'    => 'update',
                'process' => 'process_update',
                'check'   => 'a_members_man'
            ),
            'delete' => array(
                'name'    => 'delete',
                'process' => 'process_delete',
                'check'   => 'a_members_man'
            ),
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_members_man'
            )
        ));

        // Build the member array
        // ---------------------------------------------------------
        if ( !empty($this->url_id) )
        {
            $sql = "SELECT m.*, (m.member_earned - m.member_spent + m.member_adjustment) AS member_current, 
                        c.class_name AS member_class, r.race_name AS member_race
                    FROM __members AS m, __classes AS c, __races AS r  
                    WHERE (r.`race_id` = m.`member_race_id`)
                    AND (c.`class_id` = m.`member_class_id`)
                    AND (m.`member_name` = '" . $db->escape($this->url_id) . "' OR m.`member_id` = '" . $db->escape($this->url_id) . "')";
            $result = $db->query($sql);
            $row = $db->fetch_record($result);
            $db->free_result($result);

            $this->member = array(
                'member_id'         => $row['member_id'],
                'member_current'    => $row['member_current'],
                'member_race'       => $row['member_race'],
                'member_class'      => $row['member_class'],
                'member_name'       => $in->get('member_name', $row['member_name']),
                'member_earned'     => $in->get('member_earned',     floatval($row['member_earned'])),
                'member_spent'      => $in->get('member_spent',      floatval($row['member_spent'])),
                'member_adjustment' => $in->get('member_adjustment', floatval($row['member_adjustment'])),
                'member_race_id'    => $in->get('member_race_id',  intval($row['member_race_id'])),
                'member_class_id'   => $in->get('member_class_id', intval($row['member_class_id'])),
                'member_level'      => $in->get('member_level',    intval($row['member_level'])),
                'member_rank_id'    => $in->get('member_rank_id',  intval($row['member_rank_id'])),
            );
        }
    }

    function error_check()
    {
        global $db, $user, $in;

        if ( $in->get('add', false) || $in->get('update', false) )
        {
            $this->fv->is_filled('member_name', $user->lang['fv_required_name']);
            $this->fv->is_number(array(
                'member_earned'     => $user->lang['fv_number'],
                'member_spent'      => $user->lang['fv_number'],
                'member_adjustment' => $user->lang['fv_number'])
            );
        }
        
        if ( $in->get('add', false) )
        {
            // Ensure username is unique
            $sql = "SELECT member_id FROM __members WHERE (`member_name` = '" . $db->escape($in->get('member_name')) . "') LIMIT 1";
            if ( $db->num_rows($db->query($sql)) == 1 )
            {
                $this->fv->errors['member_name'] = $user->lang['error_member_exists'];
            }
        }

        return $this->fv->is_error();
    }

    // ---------------------------------------------------------
    // Process Add
    // ---------------------------------------------------------
    function process_add()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;

        //
        // Insert the member
        //

        // Make sure that each member's name is properly capitalized
        $member_name = strtolower(preg_replace('/\s+/i', ' ', $in->get('member_name')));
        $member_name = ucwords($member_name);

        $db->query("INSERT INTO __members :params", array(
            'member_name'       => $member_name,
            'member_earned'     => $in->get('member_earned', 0.00),
            'member_spent'      => $in->get('member_spent', 0.00),
            'member_adjustment' => $in->get('member_adjustment', 0.00),
            'member_firstraid'  => 0,
            'member_lastraid'   => 0,
            'member_raidcount'  => 0,
            'member_level'      => $in->get('member_level', 0), // TODO: Default level?
            'member_race_id'    => $in->get('member_race_id', 0),
            'member_class_id'   => $in->get('member_class_id', 0),
            'member_rank_id'    => $in->get('member_rank_id', 0)
        ));

        //
        // Logging
        //
        $log_action = array(
            'header'         => '{L_ACTION_MEMBER_ADDED}',
            '{L_NAME}'       => $member_name,
            '{L_EARNED}'     => $in->get('member_earned', 0.00),
            '{L_SPENT}'      => $in->get('member_spent', 0.00),
            '{L_ADJUSTMENT}' => $in->get('member_adjustment', 0.00),
            '{L_LEVEL}'      => $in->get('member_level', 0),
            '{L_RACE}'       => $in->get('member_race_id', 0),
            '{L_CLASS}'      => $in->get('member_class_id', 0),
            '{L_ADDED_BY}'   => $this->admin_user
        );
        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action
        ));

        //
        // Success message
        //
        $success_message = sprintf($user->lang['admin_add_member_success'], sanitize($member_name));
        $link_list = array(
            $user->lang['add_member']           => edit_member_path(),
            $user->lang['list_edit_del_member'] => path_default('admin/manage_members.php') . path_params('mode', 'list')
        );
        $this->admin_die($success_message, $link_list);
    }

    // ---------------------------------------------------------
    // Process Update
    // ---------------------------------------------------------
    function process_update()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;

        //
        // Get old member data
        //
        $this->get_old_data($in->get(URI_NAME));
        $member_id = $this->old_member['member_id'];
        $old_member_name = $this->old_member['member_name'];

        // Make sure that each member's name is properly capitalized
        $member_name = strtolower(preg_replace('/\s+/i', ' ', $in->get('member_name')));
        $member_name = ucwords($member_name);

        //
        // Update the member
        //
        $query = $db->build_query('UPDATE', array(
            'member_name'       => $member_name,
            'member_earned'     => $in->get('member_earned', 0.00),
            'member_spent'      => $in->get('member_spent', 0.00),
            'member_adjustment' => $in->get('member_adjustment', 0.00),
            'member_level'      => $in->get('member_level', 0), // TODO: Default level?
            'member_race_id'    => $in->get('member_race_id', 0),
            'member_class_id'   => $in->get('member_class_id', 0),
            'member_rank_id'    => $in->get('member_rank_id', 0)
        ));
        $db->query("UPDATE __members SET {$query} WHERE (`member_name` = '" . $db->escape($old_member_name) . "')");

        if ( $member_name != $old_member_name )
        {
            $escaped_new = $db->escape($member_name);
            $escaped_old = $db->escape($old_member_name);
            $sql = "UPDATE __raid_attendees SET `member_name` = '{$escaped_new}' WHERE (`member_name` = '{$escaped_old}')";
            $db->query_first($sql);
    
            $sql = "UPDATE __items SET `item_buyer` = '{$escaped_new}' WHERE (`item_buyer` = '{$escaped_old}')";
            $db->query_first($sql);

            $sql = "UPDATE __adjustments SET `member_name` = '{$escaped_new}' WHERE (`member_name` = '{$escaped_old}')";
            $db->query_first($sql);
        }

        //
        // Logging
        //
        $log_action = array(
            'header'                => '{L_ACTION_MEMBER_UPDATED}',
            '{L_NAME_BEFORE}'       => $this->old_member['member_name'],
            '{L_EARNED_BEFORE}'     => $this->old_member['member_earned'],
            '{L_SPENT_BEFORE}'      => $this->old_member['member_spent'],
            '{L_ADJUSTMENT_BEFORE}' => $this->old_member['member_adjustment'],
            '{L_LEVEL_BEFORE}'      => $this->old_member['member_level'],
            '{L_RACE_BEFORE}'       => $this->old_member['member_race_id'],
            '{L_CLASS_BEFORE}'      => $this->old_member['member_class_id'],
            '{L_NAME_AFTER}'        => $this->find_difference($this->old_member['member_name'],       $member_name),
            '{L_EARNED_AFTER}'      => $this->find_difference($this->old_member['member_earned'],     $in->get('member_earned', 0.00)),
            '{L_SPENT_AFTER}'       => $this->find_difference($this->old_member['member_spent'],      $in->get('member_spent', 0.00)),
            '{L_ADJUSTMENT_AFTER}'  => $this->find_difference($this->old_member['member_adjustment'], $in->get('member_adjustment', 0.00)),
            '{L_LEVEL_AFTER}'       => $this->find_difference($this->old_member['member_level'],      $in->get('member_level', 0)),
            '{L_RACE_AFTER}'        => $this->find_difference($this->old_member['member_race_id'],    $in->get('member_race_id', 0)),
            '{L_CLASS_AFTER}'       => $this->find_difference($this->old_member['member_class_id'],   $in->get('member_class_id', 0)),
            '{L_UPDATED_BY}'        => $this->admin_user
        );
        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action
        ));

        //
        // Success message
        //
        $success_message = sprintf($user->lang['admin_update_member_success'], sanitize($this->old_member['member_name']));
        $this->admin_die($success_message);
    }

    // ---------------------------------------------------------
    // Process Delete (confirmed)
    // ---------------------------------------------------------
    function process_confirm()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;

        $success_message = '';
        
        if ( $in->get(URI_NAME, false) )
        {
            $member_ids = explode(',', $in->get(URI_NAME));
            // Make sure each of these is actually an integer
            foreach ( $member_ids as $k => $v )
            {
                $member_ids[$k] = intval($v);
            }
            
            foreach ( $member_ids as $id )
            {
                // Since we (stupidly) use the member name as a key in several tables, get that first
                $sql = "SELECT member_name FROM __members WHERE (`member_id` = '" . $db->escape($id) . "')";
                $member_name = $db->query_first($sql);
                
                if ( !empty($member_name) )
                {
                    // Get old member data
                    $this->get_old_data($member_name);

                    // Delete attendance
                    $sql = "DELETE FROM __raid_attendees WHERE (`member_name` = '" . $db->escape($member_name) . "')";
                    $db->query($sql);

                    // Delete items
                    $sql = "DELETE FROM __items WHERE (`item_buyer` = '" . $db->escape($member_name) . "')";
                    $db->query($sql);

                    // Delete adjustments
                    $sql = "DELETE FROM __adjustments WHERE (`member_name` = '" . $db->escape($member_name) . "')";
                    $db->query($sql);

                    // Delete member
                    $sql = "DELETE FROM __members WHERE (`member_name` = '" . $db->escape($member_name) . "')";
                    $db->query($sql);

                    //
                    // Logging
                    //
                    $log_action = array(
                        'header'         => '{L_ACTION_MEMBER_DELETED}',
                        '{L_NAME}'       => $this->old_member['member_name'],
                        '{L_EARNED}'     => $this->old_member['member_earned'],
                        '{L_SPENT}'      => $this->old_member['member_spent'],
                        '{L_ADJUSTMENT}' => $this->old_member['member_adjustment'],
                        '{L_LEVEL}'      => $this->old_member['member_level'],
                        '{L_RACE}'       => $this->old_member['member_race_id'],
                        '{L_CLASS}'      => $this->old_member['member_class_id']);
                    $this->log_insert(array(
                        'log_type'   => $log_action['header'],
                        'log_action' => $log_action
                    ));

                    // Append success message
                    $success_message .= sprintf($user->lang['admin_delete_members_success'], sanitize($member_name)) . '<br />';
                }
            }
        }

        $this->admin_die($success_message);
    }

    // ---------------------------------------------------------
    // Process helper methods
    // ---------------------------------------------------------
    function get_old_data($member_name)
    {
        global $db, $eqdkp, $user, $tpl, $pm;

        $sql = "SELECT *
                FROM __members
                WHERE (`member_name` = '" . $db->escape($member_name) . "')";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $this->old_member = array(
                'member_name'       => $row['member_name'],
                'member_id'         => $row['member_id'],
                'member_earned'     => $row['member_earned'],
                'member_spent'      => $row['member_spent'],
                'member_adjustment' => $row['member_adjustment'],
                'member_level'      => $row['member_level'],
                'member_race_id'    => $row['member_race_id'],
                'member_class_id'   => $row['member_class_id']
            );
        }
        $db->free_result($result);
    }

    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $in;
        global $gm, $pm;

        foreach ( $gm->sql_classes() as $class )
        {
            $tpl->assign_block_vars('class_row', array(
                'VALUE'    => $class['id'],
                'SELECTED' => option_selected($class['id'] == $this->member['member_class_id']),
                'OPTION'   => $class['name'],
            ));
        }

        foreach ( $gm->sql_races() as $race )
        {
            $tpl->assign_block_vars('race_row', array(
                'VALUE'    => $race['id'],
                'SELECTED' => option_selected($race['id'] == $this->member['member_race_id']),
                'OPTION'   => $race['name']
            ));
        }

        if ( !empty($this->member['member_name']) )
        {
            // Get their correct earned/spent
            $sql = "SELECT SUM(r.raid_value) 
                    FROM __raids AS r, __raid_attendees AS ra 
                    WHERE (ra.`raid_id` = r.`raid_id`)
                    AND (ra.`member_name` = '" . $db->escape($this->member['member_name']) . "')";
            $correct_earned = $db->query_first($sql);

            $sql = "SELECT SUM(item_value) 
                    FROM __items
                    WHERE (`item_buyer` = '" . $db->escape($this->member['member_name']) . "')";
            $correct_spent  = $db->query_first($sql);
        }

        //
        // Build rank drop-down
        //
        $sql = "SELECT rank_id, rank_name
                FROM __member_ranks
                ORDER BY rank_id";
        $result = $db->query($sql);

        while ( $row = $db->fetch_record($result) )
        {
            $tpl->assign_block_vars('rank_row', array(
                'VALUE'    => $row['rank_id'],
                'SELECTED' => option_selected($this->member['member_rank_id'] == $row['rank_id']),
                'OPTION'   => ( !empty($row['rank_name']) ) ? stripslashes($row['rank_name']) : "({$user->lang['none']})"
            ));
        }
        $db->free_result($result);

        $tpl->assign_vars(array(
            // Form vars
            'F_ADD_MEMBER' => edit_member_path(),

            // Form values
            'MEMBER_NAME'           => sanitize($this->member['member_name'], ENT),
            'V_MEMBER_NAME'         => ( $in->get('add', false) ) ? '' : sanitize($this->member['member_name'], ENT),
            'MEMBER_ID'             => intval($this->member['member_id']),
            'MEMBER_EARNED'         => number_format($this->member['member_earned'], 2),
            'MEMBER_SPENT'          => number_format($this->member['member_spent'], 2),
            'MEMBER_ADJUSTMENT'     => number_format($this->member['member_adjustment'], 2),
            'MEMBER_CURRENT'        => ( !empty($this->member['member_current']) ) ? number_format($this->member['member_current'], 2) : '0.00',
            'MEMBER_LEVEL'          => intval($this->member['member_level']),
            'CORRECT_MEMBER_EARNED' => ( !empty($correct_earned) ) ? number_format($correct_earned, 2) : '0.00',
            'CORRECT_MEMBER_SPENT'  => ( !empty($correct_spent) )  ? number_format($correct_spent, 2)  : '0.00',
            'C_MEMBER_CURRENT'      => color_item($this->member['member_current']),

            // Language
            'L_ADD_MEMBER_TITLE' => $user->lang['addmember_title'],
            'L_NAME'             => $user->lang['name'],
            'L_RACE'             => $user->lang['race'],
            'L_CLASS'            => $user->lang['class'],
            'L_LEVEL'            => $user->lang['level'],
            'L_EARNED'           => $user->lang['earned'],
            'L_SPENT'            => $user->lang['spent'],
            'L_ADJUSTMENT'       => $user->lang['adjustment'],
            'L_CURRENT'          => $user->lang['current'],
            'L_SHOULD_BE'        => $user->lang['should_be'],
            'L_MEMBER_RANK'      => $user->lang['member_rank'],
            'L_ADD_MEMBER'       => $user->lang['add_member'],
            'L_RESET'            => $user->lang['reset'],
            'L_UPDATE_MEMBER'    => $user->lang['update_member'],
            'L_DELETE_MEMBER'    => $user->lang['delete_member'],

            // Form validation
            'FV_MEMBER_NAME'       => $this->fv->generate_error('member_name'),
            'FV_MEMBER_LEVEL'      => $this->fv->generate_error('member_level'),
            'FV_MEMBER_EARNED'     => $this->fv->generate_error('member_earned'),
            'FV_MEMBER_SPENT'      => $this->fv->generate_error('member_spent'),
            'FV_MEMBER_ADJUSTMENT' => $this->fv->generate_error('member_adjustment'),
            'FV_MEMBER_CURRENT'    => $this->fv->generate_error('member_current'),

            // Javascript messages
            'MSG_NAME_EMPTY' => $user->lang['fv_required_name'],

            // Buttons
            'S_ADD' => ( !empty($this->url_id) ) ? false : true
        ));

        $eqdkp->set_vars(array(
            'page_title'    => page_title($user->lang['manage_members_title']),
            'template_file' => 'admin/mm_addmember.html',
            'display'       => true
        ));
    }
}