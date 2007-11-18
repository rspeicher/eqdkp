<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        mm_transfer.php
 * Began:       Thu Jan 30 2003
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */

// This script handles processing of a member history transfer
// Also displays the form to do so

if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

class MM_Transfer extends EQdkp_Admin
{
    var $transfer = array();
    
    function mm_transfer()
    {
        global $in;
        
        parent::eqdkp_admin();
        
        $this->transfer = array(
            'from' => $in->get('transfer_from'),
            'to'   => $in->get('transfer_to')
        );
        
        $this->assoc_buttons(array(
            'transfer' => array(
                'name'    => 'transfer',
                'process' => 'process_transfer',
                'check'   => 'a_members_man'
            ),
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_members_man'
            )
        ));
    }
    
    function error_check()
    {
        global $user, $in;
        
        if ( $in->get('transfer_from') == '' || $in->get('transfer_to') == '' )
        {
            $this->fv->errors['transfer'] = $user->lang['fv_difference_transfer'];
        }
        if ( $in->get('transfer_from', 'from') == $in->get('transfer_to', 'to') )
        {
            $this->fv->errors['transfer'] = $user->lang['fv_difference_transfer'];
        }
        
        $this->transfer = array(
            'from' => $in->get('transfer_from'),
            'to'   => $in->get('transfer_to')
        );
        
        return $this->fv->is_error();
    }
    
    // ---------------------------------------------------------
    // Process transfer
    // ---------------------------------------------------------
    function process_transfer()
    {
        global $db, $eqdkp, $user, $tpl, $in;
        
        // Dev note: At some point, I'd like to make this more configurable
        // ie, They can select what (raid, item, adjustment) they want to transfer
        // and maybe even select specific raids/items/adjustments - not now though
        
        $member_from = $db->escape($in->get('transfer_from'));
        $member_to   = $db->escape($in->get('transfer_to'));
        
        // Transfer raids
        $raidcount_addon = 0; // So we know their new raidcount
        $sql = "SELECT raid_id, member_name
                FROM __raid_attendees
                WHERE (`member_name` = '{$member_from}')";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            // Check if the TO attended the same raid
            $sql = "SELECT member_name
                    FROM __raid_attendees
                    WHERE (`raid_id` = '{$row['raid_id']}')
                    AND (`member_name` = '{$member_to}')";
                    
            // If they didn't, replace the FROM with the TO
            if ( $db->num_rows($db->query($sql)) == 0 )
            {
                $sql = "UPDATE __raid_attendees
                        SET `member_name` = '{$member_to}'
                        WHERE (`raid_id` = '{$row['raid_id']}')
                        AND (`member_name` = '{$member_from}')";
                $db->query($sql);
                $raidcount_addon++;
            }
        }
        
        // Find their new earned
        $sql = "SELECT SUM(r.raid_value) 
                FROM __raids AS r, __raid_attendees AS ra 
                WHERE (ra.`raid_id` = r.`raid_id`)
                AND (ra.`member_name` = '{$member_to}')";
        $earned = $db->query_first($sql);
        
        // Transfer Items
        $sql = "UPDATE __items
                SET `item_buyer` = '{$member_to}'
                WHERE (`item_buyer` = '{$member_from}')";
        $db->query($sql);
        
        // Find their new spent
        $sql = "SELECT SUM(item_value)
                FROM __items
                WHERE (`item_buyer` = '{$member_to}')";
        $spent = $db->query_first($sql);
        
        // Transfer adjustments
        $sql = "UPDATE __adjustments
                SET `member_name` = '{$member_to}'
                WHERE (`member_name` = '{$member_from}')";
        $db->query($sql);
        
        // Find the new total adjustment
        // We're doing this two ways
        // 1: Individual adjustments get lumped into the total no matter what
        // 2: Group adjustments are added if their first raid was on or before 
        //    the adjustment date
        $sql = "SELECT SUM(adjustment_value)
                FROM __adjustments
                WHERE (`member_name` = '{$member_to}')";
        $total_iadj = $db->query_first($sql);
        
        $sql = "SELECT SUM(a.adjustment_value)
                FROM __adjustments AS a LEFT JOIN __members AS m ON m.`member_firstraid` <= a.`adjustment_date`
                WHERE (m.`member_name` = '{$member_to}')
                AND (a.`member_name` IS NULL)";
        $total_gadj = $db->query_first($sql);
        
        $adjustment = ($total_gadj + $total_iadj);
        $adjustment = ( !empty($adjustment) ) ? $adjustment : '0.00';
        
        // Update the member_to
        $sql = "UPDATE __members
                SET `member_earned` = '{$earned}',
                    `member_spent` = '{$spent}',
                    `member_adjustment` = '{$adjustment}',
                    `member_raidcount` = `member_raidcount` + {$raidcount_addon}
                WHERE (`member_name` = '{$member_to}')";
        $db->query($sql);
        
        // Delete the member_from
        $sql = "DELETE FROM __members
                WHERE (`member_name` = '{$member_from}')";
        $db->query($sql);
        
        // Delete any remaining raids that the FROM attended
        $sql = "DELETE FROM __raid_attendees
                WHERE (`member_name` = '{$member_from}')";
        $db->query($sql);
        
        //
        // Logging
        //
        $log_action = array(
            'header'   => '{L_ACTION_HISTORY_TRANSFER}',
            '{L_FROM}' => $member_from,
            '{L_TO}'   => $member_to
        );
        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action
        ));
        
        //
        // Success message
        //
        $success_message = sprintf($user->lang['admin_transfer_history_success'], sanitize($member_from), sanitize($member_to));
        $this->admin_die($success_message);
    }
    
    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl;
        
        //
        // Generate to/from drop-downs
        //
        $sql = "SELECT member_name
                FROM __members
                ORDER BY member_name";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $tpl->assign_block_vars('transfer_from_row', array(
                'VALUE'    => sanitize($row['member_name'], ENT),
                'SELECTED' => option_selected($this->transfer['from'] == $row['member_name']),
                'OPTION'   => sanitize($row['member_name'], ENT)
            ));
            
            $tpl->assign_block_vars('transfer_to_row', array(
                'VALUE'    => sanitize($row['member_name'], ENT),
                'SELECTED' => option_selected($this->transfer['to'] == $row['member_name']),
                'OPTION'   => sanitize($row['member_name'], ENT)
            ));
        }
        
        $tpl->assign_vars(array(
            // Form vars
            'F_TRANSFER' => path_default('manage_members.php', true) . path_params('mode', 'transfer'),
            
            // Language
            'L_TRANSFER_MEMBER_HISTORY'             => $user->lang['transfer_member_history'],
            'L_TRANSFER_MEMBER_HISTORY_DESCRIPTION' => $user->lang['transfer_member_history_description'],
            'L_FROM'                                => $user->lang['from'],
            'L_TO'                                  => $user->lang['to'],
            'L_SELECT_1_OF_X_MEMBERS'               => sprintf($user->lang['select_1ofx_members'], $db->num_rows($result)),
            'L_TRANSFER_HISTORY'                    => $user->lang['transfer_history'],
            
            // Form validation
            'FV_TRANSFER' => $this->fv->generate_error('transfer')
        ));
        $db->free_result($result);
        
        $eqdkp->set_vars(array(
            'page_title'    => page_title($user->lang['manage_members_title']),
            'template_file' => 'admin/mm_transfer.html',
            'display'       => true
        ));
    }
}