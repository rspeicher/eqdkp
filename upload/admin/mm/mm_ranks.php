<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * mm_ranks.php
 * Began: Fri February 14 2003
 * 
 * $Id$
 * 
 ******************************/
 
// This script handles editing membership ranks
if( !defined('EQDKP_INC') )
{ // Hacking attempt
    header('HTTP/1.0 404 Not Found');
    exit;
}

class MM_Ranks extends EQdkp_Admin
{
    function mm_ranks()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;
        
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
        global $db, $eqdkp, $user, $tpl, $pm, $in;
        global $SID;
        
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
               
                $query = $db->build_query('INSERT', array(
                    'rank_id'     => $rank_id,
                    'rank_name'   => $rank_name,
                    'rank_hide'   => ( isset($hidden[$rank_id]) ) ? '1' : '0',
                    'rank_prefix' => $rank_prefix,
                    'rank_suffix' => $rank_suffix
                ));
                $db->query("REPLACE INTO __member_ranks {$query}");
            }
        }
        
        header('Location: manage_members.php' . $SID . '&mode=ranks');
    }
    
    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;
        
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
            // Don't strip HTML tags, we want to allow them. That's the whole point.
            $prefix = sanitize($row['rank_prefix'], true, false);
            $suffix = sanitize($row['rank_suffix'], true, false);
            
            $tpl->assign_block_vars('ranks_row', array(
                'ROW_CLASS'    => $eqdkp->switch_row_class(),
                'RANK_ID'      => intval($row['rank_id']),
                'RANK_NAME'    => stripslashes($row['rank_name']),
                'RANK_PREFIX'  => stripslashes($prefix),
                'RANK_SUFFIX'  => stripslashes($suffix),
                'HIDE_CHECKED' => option_checked($row['rank_hide'] == '1')
            ));
            $max_id = ( $max_id < $row['rank_id'] ) ? $row['rank_id'] : $max_id;
        }
        $db->free_result($result);
        
        $tpl->assign_vars(array(
            // Form vars
            'F_EDIT_RANKS' => "manage_members.php{$SID}&amp;mode=ranks",
            
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
            'L_RESET'            => $user->lang['reset'])
        );
        
        $eqdkp->set_vars(array(
            'page_title'    => page_title($user->lang['manage_members_title']),
            'template_file' => 'admin/mm_ranks.html',
            'display'       => true
        ));
    }
}