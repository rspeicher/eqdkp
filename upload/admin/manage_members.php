<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        manage_members.php
 * Began:       Sun Jan 5 2003
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */

// Notice: Since 'Manage Members' function as a whole handles a lot of form and 
// processing code, this script will serve only as a framework for other processing
// scripts (found in the mm directory)
 
define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = './../';
require_once($eqdkp_root_path . 'common.php');

class Manage_Members extends EQdkp_Admin
{
    function manage_members()
    {
        parent::eqdkp_admin();
        
        $this->assoc_buttons(array(
            'form' => array(
                'name'    => '',
                'process' => 'display_menu',
                'check'   => 'a_members_man'
            )
        ));
        
        $this->assoc_params(array(
            'transfer' => array(
                'name'    => 'mode',
                'value'   => 'transfer',
                'process' => 'mm_mode',
                'check'   => 'a_members_man'
            ),
            'addmember' => array(
                'name'    => 'mode',
                'value'   => 'addmember',
                'process' => 'mm_mode',
                'check'   => 'a_members_man'
            ),
            'list' => array(
                'name'    => 'mode',
                'value'   => 'list',
                'process' => 'mm_mode',
                'check'   => 'a_members_man'
            ),
            'ranks' => array(
                'name'    => 'mode',
                'value'   => 'ranks',
                'process' => 'mm_mode',
                'check'   => 'a_members_man'
            )
        ));
    }
    
    function error_check()
    {
        return $this->fv->is_error();
    }
    
    // ---------------------------------------------------------
    // Display menu
    // ---------------------------------------------------------
    function display_menu()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        
        $tpl->assign_vars(array(
            'L_MANAGE_MEMBERS'        => $user->lang['manage_members'],
            'L_ADD_MEMBER'            => $user->lang['add_member'],
            'L_LIST_EDIT_DEL_MEMBERS' => $user->lang['list_edit_del_member'],
            'L_EDIT_RANKS'            => $user->lang['edit_ranks'],
            'L_TRANSFER_HISTORY'      => $user->lang['transfer_history'],
            
            'U_ADD_MEMBER'       => path_default('manage_members.php', true) . path_params('mode', 'addmember'),
            'U_LIST_MEMBERS'     => path_default('manage_members.php', true) . path_params('mode', 'list'),
            'U_EDIT_RANKS'       => path_default('manage_members.php', true) . path_params('mode', 'ranks'),
            'U_TRANSFER_HISTORY' => path_default('manage_members.php', true) . path_params('mode', 'transfer')
        ));
        
        $eqdkp->set_vars(array(
            'page_title'    => page_title($user->lang['manage_members_title']),
            'template_file' => 'admin/mm_menu.html',
            'display'       => true
        ));
    }
    
    ## ########################################################################
    ## Process mode
    ## ########################################################################
    
    function mm_mode()
    {
        global $in;
        // We don't need these variables, but the files we include will
        global $db, $eqdkp, $tpl, $in, $pm, $user;
        
        $mode  = strtolower($in->get('mode'));
        $mode  = ( $mode == 'list' ) ? 'listmembers' : $mode; // Our mode is 'list' but the actual file/class is 'listmembers', meh.
        $class = 'MM_' . ucfirst($mode);
        
        if ( in_array($mode, array('addmember', 'listmembers', 'ranks', 'transfer')) )
        {
            require_once("mm/mm_{$mode}.php");
            $ext = new $class();
            $ext->process();
        }
    }
}

$manage_members = new Manage_Members;
$manage_members->process();