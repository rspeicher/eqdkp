<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        login.php
 * Began:       Sat Dec 21 2002
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */
 
define('EQDKP_INC', true);
$eqdkp_root_path = './';
include_once($eqdkp_root_path . 'common.php');

$redirect = '';

if ( $in->exists('logout') && $user->data['user_id'] != ANONYMOUS )
{
    $redirect = $in->get('redirect', $eqdkp->config['start_page']);
    
    $user->destroy();
}
elseif ( $in->exists('login') && $user->data['user_id'] <= 0 )
{
    $redirect = $in->get('redirect', $eqdkp->config['start_page']);
    $redirect_path = path_default('login.php') . path_params('redirect', $redirect);
    
    if ( !$user->login($in->get('username'), $in->get('password'), $in->get('auto_login', 0)) )
    {
        $tpl->assign_var('META', '<meta http-equiv="refresh" content="3;url=' . $redirect_path . '" />');
        
        message_die($user->lang['invalid_login'], $user->lang['error']);
    }
}

if ( $redirect != '' )
{
    redirect(preg_replace('/^.*?redirect=(.+?)&(.+?)$/', "\\1{$SID}&\\2", $redirect));
}

$eqdkp->set_vars(array(
    'page_title'    => page_title($user->lang['login_title']),
    'template_file' => 'login.html'
));

if ( $in->exists('lost_password') )
{
    // Lost password form
    $tpl->assign_vars(array(
        'S_LOGIN' => false,
        
        'L_GET_NEW_PASSWORD' => $user->lang['get_new_password'],
        'L_USERNAME'         => $user->lang['username'],
        'L_EMAIL'            => $user->lang['email'],
        'L_SUBMIT'           => $user->lang['submit'],
        'L_RESET'            => $user->lang['reset']
    ));
    
    $eqdkp->display();
}
elseif ( $user->data['user_id'] <= 0 )
{
    // Login form
    $tpl->assign_vars(array(
        'S_LOGIN' => true,
        
        'FORM_ACTION' => path_default('login.php') . path_params('redirect', $in->get('redirect', $eqdkp->config['start_page'])),
        
        'L_LOGIN'             => $user->lang['login'],
        'L_USERNAME'          => $user->lang['username'],
        'L_PASSWORD'          => $user->lang['password'],
        'L_REMEMBER_PASSWORD' => $user->lang['remember_password'],
        'L_LOGIN'             => $user->lang['login'],
        'L_LOST_PASSWORD'     => $user->lang['lost_password'],
        
        'ONLOAD' => ' onload="javascript:document.post.username.focus()"'
    ));
    
    $eqdkp->display();
}
else
{
    redirect("index.php{$SID}");
}