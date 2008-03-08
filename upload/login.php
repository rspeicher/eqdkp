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
 * @copyright   2002-2008 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */
 
define('EQDKP_INC', true);
$eqdkp_root_path = './';
require_once($eqdkp_root_path . 'common.php');

// Get some page variables
// FIXME: These actions are mutually exclusive. Alamgamate them into a single 'mode' variable.
$login    = $in->exists('login') ? true : false;
$logout   = $in->exists('logout') ? true : false;
$lostpass = $in->exists('lost_password') ? true : false;

// For now, I'm gonna fudge a mutually exclusive operation.
$mode     = ($lostpass) ? 'lost_password' : (($logout) ? 'logout' : (($login) ? 'login' : ''));
$redirect = $in->get('redirect', $eqdkp->config['start_page']);

// Do the requested operation
switch ($mode)
{
    case 'login':
    // Process login
        if ($user->data['user_id'] <= 0)
        {
            $redirect_path = path_default('login.php') . path_params('redirect', $redirect);
            
            if ( !$user->login($in->get('username'), $in->get('password')) )
            {
                // Invalid login attempt. Trigger error + redirect back to login page
                meta_refresh(3, $redirect_path);
                message_die($user->lang['invalid_login'], $user->lang['error']);
            }
            else
            {
                redirect(preg_replace('/^.*?redirect=(.+?)&(.+?)$/', "\\1?\\2", $redirect));
            }
        }
        else
        {
            // FIXME: The user already existed. This is a strange case, and I'm not sure about it. It happened to me once, so... more testing required.
        }
    break;
    
    case 'logout':
    // Process logout
        if ($user->data['user_id'] != ANONYMOUS)
        {
            $user->logout();
            redirect(preg_replace('/^.*?redirect=(.+?)&(.+?)$/', "\\1?\\2", $redirect));
        }
    break;
    

    case 'lost_password':
    // Display the lost password form
        if ($user->data['user_id'] <= 0)
        {
            $eqdkp->set_vars(array(
                'page_title'          => page_title($user->lang['login_title']),
                'template_file'       => 'login.html'
            ));
            
            // Lost password form
            $tpl->assign_vars(array(
                'S_LOGIN' => false,
                
                'L_GET_NEW_PASSWORD'  => $user->lang['get_new_password'],
                'L_USERNAME'          => $user->lang['username'],
                'L_EMAIL'             => $user->lang['email'],
                'L_SUBMIT'            => $user->lang['submit'],
                'L_RESET'             => $user->lang['reset']
            ));
            
            $eqdkp->display();
        }
    break;

    default:
    // Display the login form
        if ($user->data['user_id'] <= 0)
        {
            $eqdkp->set_vars(array(
                'page_title'          => page_title($user->lang['login_title']),
                'template_file'       => 'login.html'
            ));

            // Login form
            $tpl->assign_vars(array(
                'S_LOGIN'             => true,
                
                'FORM_ACTION'         => path_default('login.php') . path_params('redirect', $in->get('redirect', $eqdkp->config['start_page'])),
                
                'L_LOGIN'             => $user->lang['login'],
                'L_USERNAME'          => $user->lang['username'],
                'L_PASSWORD'          => $user->lang['password'],
                'L_REMEMBER_PASSWORD' => $user->lang['remember_password'],
                'L_LOGIN'             => $user->lang['login'],
                'L_LOST_PASSWORD'     => $user->lang['lost_password'],
                
                'ONLOAD'              => ' onload="javascript:document.post.username.focus()"'
            ));
            
            $eqdkp->display();
        }
    break;
}

// If a mode was used in an unexpected context (eg: user logged in and tries to log in), redirect to the index
redirect(path_default('index.php'));
exit;
