<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        addturnin.php
 * Began:       Sat Jan 4 2003
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2008 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */
 
define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = './../';
require_once($eqdkp_root_path . 'common.php');

class Add_Turnin extends EQdkp_Admin
{
    var $turnin = array();              // Holds turnin data                    @var turnin
    
    function add_turnin()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;
        
        parent::eqdkp_admin();
        
        $this->turnin = array(
            'from' => $in->get('turnin_from'),
            'to'   => $in->get('turnin_to')
        );
        
        $this->assoc_buttons(array(
            'add' => array(
                'name'    => 'add',
                'process' => 'process_add',
                'check'   => 'a_turnin_add'
            ),
            'proceed' => array(
                'name'    => 'proceed',
                'process' => 'display_step2',
                'check'   => 'a_turnin_add'
            ),
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_turnin_add'
            )
        ));
    }
    
    function error_check()
    {
        global $db, $user, $in;
        
        if ( $in->exists('turnin_from') )
        {
            $from = $in->get('turnin_from');
            $to   = $in->get('turnin_to');
            
            if ( empty($from) || empty($to) || $from == $to )
            {
                $this->fv->errors['turnin_from'] = $user->lang['fv_difference_turnin'];
                $this->fv->errors['turnin_to']   = $user->lang['fv_difference_turnin'];
            }
            
            // Make sure $from has an item to buy, first
            if ( $in->exists('proceed') )
            {
                $sql = "SELECT COUNT(item_id)
                        FROM __items
                        WHERE (`item_buyer` = '" . $db->escape($from) . "')";
                $count = $db->query_first($sql);
                
                if ( $count == 0 )
                {
                    $this->fv->errors['turnin_from'] = sprintf($user->lang['fv_turnin_noitems'], sanitize($from));
                }
            }
        
            // TODO: Why is this here?
            $this->turnin = array(
                'from' => $in->get('turnin_from'),
                'to'   => $in->get('turnin_to')
            );
        }
        
        return $this->fv->is_error();
    }
    
    // ---------------------------------------------------------
    // Process Add
    // ---------------------------------------------------------
    function process_add()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;
        
        $item_id = $db->escape($in->get('item_id', 0));
        $from    = $db->escape($in->get('from'));
        $to      = $db->escape($in->get('to'));
        
        //
        // Get item information
        //
        $sql = "SELECT item_value, item_name
                FROM __items
                WHERE (`item_id` = '{$item_id}')";
        $result = $db->query($sql);
        $row = $db->fetch_record($result);
        
        $item_value = ( !empty($row['item_value']) ) ? floatval($row['item_value']) : 0.00;
        
        //
        // Remove price from the 'From' member
        //
        $sql = "UPDATE __members
                SET `member_spent` = `member_spent` - {$item_value}
                WHERE (`member_name` = '{$from}')";
        $db->query($sql);
        
        //
        // Add the price to the 'To' member
        //
        $sql = "UPDATE __members
                SET `member_spent` = `member_spent` + {$item_value}
                WHERE (`member_name` = '{$to}')";
        $db->query($sql);
        
        //
        // Change the buyer
        //
        $sql = "UPDATE __items
                SET `item_buyer` = '{$to}'
                WHERE (`item_id` = '{$item_id}')";
        $db->query($sql);
        
        //
        // Logging
        //
        $log_action = array(
            'header'       => '{L_ACTION_TURNIN_ADDED}',
            '{L_ITEM}'     => $row['item_name'],
            '{L_VALUE}'    => $item_value,
            '{L_FROM}'     => $from,
            '{L_TO}'       => $to,
            '{L_ADDED_BY}' => $this->admin_user
        );
        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action
        ));
        
        $success_message = sprintf($user->lang['admin_add_turnin_success'], sanitize($row['item_name']), sanitize($from), sanitize($to));
        $this->admin_die($success_message);
    }
    
    // ---------------------------------------------------------
    // Process Step 2
    // ---------------------------------------------------------
    function display_step2()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;
        
        $max_length = strlen(strval($db->query_first("SELECT MAX(item_value) FROM __items")));
        
        $sql = "SELECT item_id, item_name, item_value
                FROM __items
                WHERE (`item_buyer` = '" . $db->escape($in->get('turnin_from')) . "')
                ORDER BY item_name";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $tpl->assign_block_vars('items_row', array(
                'VALUE'  => $row['item_id'],
                // NOTE: Kinda pointless since the select box isn't fixed width!
                'OPTION'   => str_pad($row['item_value'], $max_length, ' ', STR_PAD_LEFT) . ' - ' . sanitize($row['item_name'])
            ));
        }
        
        $tpl->assign_vars(array(
            // Form vars
            'F_ADD_TURNIN' => path_default('admin/addturnin.php'),
            'S_STEP1'      => false,
                        
            // Form values
            'FROM'        => sanitize($this->turnin['from'], ENT),  // Hidden field
            'TO'          => sanitize($this->turnin['to'], ENT),
            'TURNIN_FROM' => sanitize($this->turnin['from']),       // Displayed value
            'TURNIN_TO'   => sanitize($this->turnin['to']),
            
            // Language
            'L_ADD_TURNIN_TITLE' => sprintf($user->lang['addturnin_title'], '2'),
            'L_FROM'             => $user->lang['from'],
            'L_TO'               => $user->lang['to'],
            'L_ADD_TURNIN'       => $user->lang['add_turnin'],
            'L_ITEM'             => $user->lang['item'],
            
            // Form validation
            'FV_TURNIN_FROM' => $this->fv->generate_error('turnin_from'),
            'FV_TURNIN_TO'   => $this->fv->generate_error('turnin_to'),
            
            // Javascript messages
            'MSG_FROM_TO_SAME' => $user->lang['fv_difference_turnin']
        ));
        
        $eqdkp->set_vars(array(
            'page_title'    => page_title(sprintf($user->lang['addturnin_title'], '2')),
            'template_file' => 'admin/addturnin.html',
            'display'       => true
        ));
    }
    
    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        
        $sql = "SELECT member_name
                FROM __members
                ORDER BY member_name";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $tpl->assign_block_vars('turnin_from_row', array(
                'VALUE'    => sanitize($row['member_name'], ENT),
                'SELECTED' => option_selected($this->turnin['from'] == $row['member_name']),
                'OPTION'   => sanitize($row['member_name'])
            ));
            
            $tpl->assign_block_vars('turnin_to_row', array(
                'VALUE'    => sanitize($row['member_name'], ENT),
                'SELECTED' => option_selected($this->turnin['to'] == $row['member_name']),
                'OPTION'   => sanitize($row['member_name'])
            ));
        }
        
        $tpl->assign_vars(array(
            // Form vars
            'F_ADD_TURNIN' => path_default('admin/addturnin.php'),
            'S_STEP1'      => true,
            
            // Form values
            'FROM'    => sanitize($this->turnin['from'], ENT),
            'TO'      => sanitize($this->turnin['to'], ENT),
            
            // Language
            'L_ADD_TURNIN_TITLE' => sprintf($user->lang['addturnin_title'], '1'),
            'L_FROM'             => $user->lang['from'],
            'L_TO'               => $user->lang['to'],
            'L_PROCEED'          => $user->lang['proceed'],
            
            // Form validation
            'FV_TURNIN_FROM' => $this->fv->generate_error('turnin_from'),
            'FV_TURNIN_TO'   => $this->fv->generate_error('turnin_to'),
            
            // Javascript messages
            'MSG_FROM_TO_SAME' => $user->lang['fv_difference_turnin']
        ));
        
        $eqdkp->set_vars(array(
            'page_title'    => page_title(sprintf($user->lang['addturnin_title'], '1')),
            'template_file' => 'admin/addturnin.html',
            'display'       => true
        ));
    }
}

$add_turnin = new Add_Turnin;
$add_turnin->process();