<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        mm_ranks.php
 * Began:       Fri Feb 14 2003
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2008 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */
 
// This script handles editing membership ranks
if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

class MM_Ranks extends EQdkp_Admin
{
    function mm_ranks()
    {
        parent::eqdkp_admin();
        
        $this->assoc_buttons(array(
            'submit' => array(
                'name'    => 'submit',
                'process' => 'process_submit',
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
        return $this->fv->is_error();
    }
    
    // ---------------------------------------------------------
    // Process submit
    // ---------------------------------------------------------
    function process_submit()
    {
        global $db, $eqdkp, $user, $in;
        
        $rank_names = $in->getArray('ranks', 'string');
        $prefixes   = $in->getArray('prefix', 'string');
        $suffixes   = $in->getArray('suffix', 'string');
        $hidden     = $in->getArray('hide', 'int');
        
        foreach ( $rank_names as $rank_id => $rank_name )
        {
            $rank_id = intval($rank_id);
            
            // If the rank's been removed, NULL the member_rank for users that have it
            if ( $rank_name == '' )
            {
                $sql = "DELETE FROM __member_ranks WHERE (`rank_id` = '{$rank_id}')";
                $db->query($sql);
                
                $sql = "UPDATE __members
                        SET `member_rank_id` = NULL
                        WHERE (`member_rank_id` = '{$rank_id}')";
                $db->query($sql);
            }
            // Otherwise re-add the rank to the table
            else
            {
                $rank_prefix = ( isset($prefixes[$rank_id]) ) ? unsanitize($prefixes[$rank_id]) : '';
                $rank_suffix = ( isset($suffixes[$rank_id]) ) ? unsanitize($suffixes[$rank_id]) : '';
               
                $db->query("REPLACE INTO __member_ranks :params", array(
                    'rank_id'     => $rank_id,
                    'rank_name'   => $rank_name,
                    'rank_hide'   => ( isset($hidden[$rank_id]) ) ? '1' : '0',
                    'rank_prefix' => $rank_prefix,
                    'rank_suffix' => $rank_suffix
                ));
            }
        }
        
        header('Location: ' . path_default('admin/manage_members.php') . path_params('mode', 'ranks'));
    }
    
    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl;
        
        //
        // Populate the fields
        //
        $max_id = 0;
        $sql = "SELECT rank_id, rank_name, rank_hide, rank_prefix, rank_suffix
                FROM __member_ranks
                WHERE (`rank_id` > 0)
                ORDER BY `rank_id`";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $tpl->assign_block_vars('ranks_row', array(
                'ROW_CLASS'    => $eqdkp->switch_row_class(),
                'RANK_ID'      => intval($row['rank_id']),
                'RANK_NAME'    => sanitize($row['rank_name'], ENT),
                'RANK_PREFIX'  => sanitize($row['rank_prefix'], ENT),
                'RANK_SUFFIX'  => sanitize($row['rank_suffix'], ENT),
                'HIDE_CHECKED' => option_checked($row['rank_hide'] == '1')
            ));
            $max_id = ( $max_id < $row['rank_id'] ) ? $row['rank_id'] : $max_id;
        }
        $db->free_result($result);
        
        $tpl->assign_vars(array(
            // Form vars
            'F_EDIT_RANKS' => path_default('admin/manage_members.php') . path_params('mode', 'ranks'),
            
            // Form values
            'ROW_CLASS' => $eqdkp->switch_row_class(),
            'RANK_ID'   => $max_id + 1,
            
            // Language
            'L_EDIT_RANKS_TITLE' => $user->lang['edit_ranks'],
            'L_TITLE'            => $user->lang['title'],
            'L_HIDE'             => $user->lang['hide'],
            'L_LIST_PREFIX'      => $user->lang['list_prefix'],
            'L_LIST_SUFFIX'      => $user->lang['list_suffix'],
            'L_EDIT_RANKS'       => $user->lang['edit_ranks'],
            'L_RESET'            => $user->lang['reset']
        ));
        
        $eqdkp->set_vars(array(
            'page_title'    => page_title($user->lang['manage_members_title']),
            'template_file' => 'admin/mm_ranks.html',
            'display'       => true
        ));
    }
}