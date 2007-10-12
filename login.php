<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * login.php
 * Began: Sat December 21 2002
 * 
 * $Id: login.php 46 2007-06-19 07:29:11Z tsigo $
 * 
 ******************************/
 
define('EQDKP_INC', true);
$eqdkp_root_path = './';
include_once($eqdkp_root_path . 'common.php');

$redirect = '';

if ( $in->get('logout', false) && $user->data['user_id'] != ANONYMOUS )
{
    $redirect = $in->get('redirect', $eqdkp->config['start_page']);
    
    $user->destroy();
}
elseif ( $in->get('login', false) && $user->data['user_id'] <= 0 )
{
    $redirect = $in->get('redirect', $eqdkp->config['start_page']);
    
    if ( !$user->login($in->get('username'), $in->get('password'), $in->get('auto_login', 0)) )
    {
        $tpl->assign_var('META', '<meta http-equiv="refresh" content="3;url=login.php' . $SID . '&amp;redirect=' . $redirect . '">');
        
        message_die($user->lang['invalid_login'], $user->lang['error']);
    }
}

if ( $redirect != '' )
{
    redirect(preg_replace('/^.*?redirect=(.+?)&(.+?)$/', "\\1{$SID}&\\2", $redirect));
}

//
// Lost Password Form
//
$eqdkp->set_vars(array(
    'page_title'    => sprintf($user->lang['title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']).': '.$user->lang['login_title'],
    'template_file' => 'login.html')
);
if ( $in->get('lost_password', false) )
{
    $tpl->assign_vars(array(
        'S_LOGIN' => false,
        
        'L_GET_NEW_PASSWORD' => $user->lang['get_new_password'],
        'L_USERNAME'         => $user->lang['username'],
        'L_EMAIL'            => $user->lang['email'],
        'L_SUBMIT'           => $user->lang['submit'],
        'L_RESET'            => $user->lang['reset'])
    );
    
    $eqdkp->display();
}

//
// Login form
//
elseif ( $user->data['user_id'] <= 0 )
{
    $tpl->assign_vars(array(
        'S_LOGIN' => true,
        
        'FORM_ACTION' => "login.php{$SID}&amp;redirect=" . $in->get('redirect', $eqdkp->config['start_page']),
        
        'L_LOGIN'             => $user->lang['login'],
        'L_USERNAME'          => $user->lang['username'],
        'L_PASSWORD'          => $user->lang['password'],
        'L_REMEMBER_PASSWORD' => $user->lang['remember_password'],
        'L_LOGIN'             => $user->lang['login'],
        'L_LOST_PASSWORD'     => $user->lang['lost_password'],
        
        'ONLOAD' => ' onload="javascript:document.post.username.focus()"')
    );
    
    $eqdkp->display();
}
else
{
    redirect('index.php'.$SID);
}