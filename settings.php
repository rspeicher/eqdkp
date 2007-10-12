<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * settings.php
 * Began: Mon December 30 2002
 * 
 * $Id: settings.php 46 2007-06-19 07:29:11Z tsigo $
 * 
 ******************************/
 
define('EQDKP_INC', true);
$eqdkp_root_path = './';
include_once($eqdkp_root_path . 'common.php');

$fv = new Form_Validate;

$mode = $in->get('mode');

if ( $user->data['user_id'] == ANONYMOUS )
{
    header('Location: login.php'.$SID);
}

switch ( $mode )
{
    case 'account':
        $action = 'account_settings';
        break;
    default:
        $action = 'display';
        break;
}

if ( $in->get('submit', false) )
{
    $action = 'update';
    
    // Error-check the form
    $change_username = false;
    if ( $in->get('username') != $user->data['username'] )
    {
        // They changed the username. See if it's already registered
        $sql = "SELECT user_id
                FROM __users
                WHERE (`username` = '" . $in->get('username') . "')";
        if ( $db->num_rows($db->query($sql)) > 0 )
        {
            $fv->errors['username'] = $user->lang['fv_already_registered_username'];
        }
        $change_username = true;
    }
    
    $change_password = false;
    if ( $in->get('new_user_password1', false) && $in->get('new_user_password2') )
    {
        $fv->matching_passwords('new_user_password1', 'new_user_password2', $user->lang['fv_match_password']);
        $change_password = true;
    }
    
    // If they changed their username or password, we have to confirm
    // their current password
    if ( ($change_username) || ($change_password) )
    {
        $sql = "SELECT user_id
                FROM __users
                WHERE (`user_id` = '{$user->data['user_id']}')
                AND (`user_password` = '" . md5($in->get('user_password')) . "')";
        if ( $db->num_rows($db->query($sql)) == 0 )
        {
            $fv->errors['user_password'] = $user->lang['incorrect_password'];
        }
    }
    
    $fv->is_number(array(
        'user_alimit' => $user->lang['fv_number'],
        'user_elimit' => $user->lang['fv_number'],
        'user_ilimit' => $user->lang['fv_number'],
        'user_nlimit' => $user->lang['fv_number'],
        'user_rlimit' => $user->lang['fv_number'])
    );
    
    $fv->is_within_range('user_alimit', 1, 9999);
    $fv->is_within_range('user_elimit', 1, 9999);
    $fv->is_within_range('user_ilimit', 1, 9999);
    $fv->is_within_range('user_nlimit', 1, 9999);
    $fv->is_within_range('user_rlimit', 1, 9999);
    
    if ( $fv->is_error() )
    {
        $action = 'account_settings';
    }
}

switch ( $action )
{
    //
    // Process the update
    //
    case 'update':
        // Errors have been checked at this point, build the query
        // User settings
        $update = array(
            'user_email'  => $in->get('user_email'),
            'user_alimit' => $in->get('user_alimit', intval($eqdkp->config['default_alimit'])),
            'user_elimit' => $in->get('user_elimit', intval($eqdkp->config['default_elimit'])),
            'user_ilimit' => $in->get('user_ilimit', intval($eqdkp->config['default_ilimit'])),
            'user_nlimit' => $in->get('user_nlimit', intval($eqdkp->config['default_nlimit'])),
            'user_rlimit' => $in->get('user_rlimit', intval($eqdkp->config['default_rlimit'])),
            'user_lang'   => $in->get('user_lang',   $eqdkp->config['default_lang']),
            'user_style'  => $in->get('user_style',  intval($eqdkp->config['default_style'])),
        );
        if ( $change_username )
        {
            $update['username'] = $in->get('username');
        }
        if ( $change_password )
        {
            $update['user_password'] = md5($in->get('new_user_password1'));
        }
        
        $query = $db->build_query('UPDATE', $update);
        $sql = "UPDATE __users SET {$query} WHERE (`user_id` = '{$user->data['user_id']}')";
        
        if ( !($result = $db->query($sql)) )
        {
            message_die('Could not update user information', '', __FILE__, __LINE__, $sql);
        }
        
        $tpl->assign_vars(array(
            'META' => '<meta http-equiv="refresh" content="3;index.php' . $SID . '" />')
        );
       
        message_die($user->lang['update_settings_success']);
        
        break;
    //
    // Display the account settings form
    //
    case 'account_settings':
        $tpl->assign_vars(array(
            'F_SETTINGS' => 'settings.php'.$SID.'&amp;mode=account',
            
            'S_CURRENT_PASSWORD' => true,
            'S_NEW_PASSWORD'     => true,
            'S_SETTING_ADMIN'    => false,
            'S_MU_TABLE'         => false,

            'L_REGISTRATION_INFORMATION' => $user->lang['registration_information'],
            'L_REQUIRED_FIELD_NOTE'      => $user->lang['required_field_note'],
            'L_USERNAME'                 => $user->lang['username'],
            'L_EMAIL_ADDRESS'            => $user->lang['email_address'],
            'L_CURRENT_PASSWORD'         => $user->lang['current_password'],
            'L_CURRENT_PASSWORD_NOTE'    => $user->lang['current_password_note'],
            'L_NEW_PASSWORD'             => $user->lang['new_password'],
            'L_NEW_PASSWORD_NOTE'        => $user->lang['new_password_note'],
            'L_CONFIRM_PASSWORD'         => $user->lang['confirm_password'],
            'L_CONFIRM_PASSWORD_NOTE'    => $user->lang['confirm_password_note'],
            'L_PREFERENCES'              => $user->lang['preferences'],
            'L_ADJUSTMENTS_PER_PAGE'     => $user->lang['adjustments_per_page'],
            'L_EVENTS_PER_PAGE'          => $user->lang['events_per_page'],
            'L_ITEMS_PER_PAGE'           => $user->lang['items_per_page'],
            'L_NEWS_PER_PAGE'            => $user->lang['news_per_page'],
            'L_RAIDS_PER_PAGE'           => $user->lang['raids_per_page'],
            'L_LANGUAGE'                 => $user->lang['language'],
            'L_STYLE'                    => $user->lang['style'],
            'L_PREVIEW'                  => $user->lang['preview'],
            'L_SUBMIT'                   => $user->lang['submit'],
            'L_RESET'                    => $user->lang['reset'],

            'USERNAME'    => $user->data['username'],
            'USER_EMAIL'  => $user->data['user_email'],
            'USER_ALIMIT' => $user->data['user_alimit'],
            'USER_ELIMIT' => $user->data['user_elimit'],
            'USER_ILIMIT' => $user->data['user_ilimit'],
            'USER_NLIMIT' => $user->data['user_nlimit'],
            'USER_RLIMIT' => $user->data['user_rlimit'],

            'FV_USERNAME'     => $fv->generate_error('username'),
            'FV_PASSWORD'     => $fv->generate_error('user_password'),
            'FV_NEW_PASSWORD' => $fv->generate_error('new_user_password1'),
            'FV_USER_ALIMIT'  => $fv->generate_error('user_alimit'),
            'FV_USER_ELIMIT'  => $fv->generate_error('user_elimit'),
            'FV_USER_ILIMIT'  => $fv->generate_error('user_ilimit'),
            'FV_USER_NLIMIT'  => $fv->generate_error('user_nlimit'),
            'FV_USER_RLIMIT'  => $fv->generate_error('user_rlimit')
        ));

        // TODO: Building language drop-down. Consider revising method (also, perhaps move to functions.php?).
        if ( $dir = @opendir($eqdkp_root_path . 'language/') )
        {
            while ( $file = @readdir($dir) )
            {
                if ( (!is_file($eqdkp_root_path . 'language/' . $file)) && (!is_link($eqdkp_root_path . 'language/' . $file)) && ($file != '.') && ($file != '..') && ($file != 'CVS') & ($file != '.svn') )
                {
                    $tpl->assign_block_vars('lang_row', array(
                        'VALUE' => $file,
                        'SELECTED' => ( $user->data['user_lang'] == $file ) ? ' selected="selected"' : '',
                        'OPTION' => ucfirst($file)
                    ));
                }
            }
        }

        // TODO: Building style drop-down. Consider revising method (also, perhaps move to functions.php?).
        $sql = "SELECT style_id, style_name
                FROM __styles
                ORDER BY `style_name`";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $tpl->assign_block_vars('style_row', array(
                'VALUE'    => $row['style_id'],
                'SELECTED' => ( $user->data['user_style'] == $row['style_id'] ) ? ' selected="selected"' : '',
                'OPTION'   => $row['style_name']
            ));
        }
        $db->free_result($result);
        
        $eqdkp->set_vars(array(
            'page_title'    => sprintf($user->lang['title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']).': '.$user->lang['settings_title'],
            'template_file' => 'settings.html',
            'display'       => true
        ));

        break;
    //
    // Display a list of available settings
    // This can include plugin user settings
    //
    case 'display':
        // Build the available options
        $settings_menu = array(
            $user->lang['basic'] => array(
                0 => '<a href="settings.php' . $SID . '&amp;mode=account">' . $user->lang['account_settings'] . '</a>'
            )
        );
        
        $plugins_menu = $pm->get_menus('settings');
        if ( @sizeof($plugins_menu) > 0 )
        {
            $settings_menu = array_merge($settings_menu, $plugins_menu);
        }

        foreach ( $settings_menu as $root => $sub )
        {
            $tpl->assign_block_vars('root_menu', array(
                'TEXT' => $root
            ));
            
            foreach ( $sub as $sub_text )
            {
                $tpl->assign_block_vars('root_menu.sub_menu', array(
                    'TEXT' => $sub_text
                ));
            }
        }
        
        $eqdkp->set_vars(array(
            'page_title'    => sprintf($user->lang['title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']).': '.$user->lang['settings_title'],
            'template_file' => 'settings_menu.html',
            'display'       => true)
        );
        
        break;
}