<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        plugins.php
 * Began:       Mon Jan 13 2003
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */

define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = './../';
require_once($eqdkp_root_path . 'common.php');

$user->check_auth('a_plugins_man', true);

$mode = $in->get('mode', 'list');
$code = $in->get('code');

if ( (!empty($code)) && (!is_dir($eqdkp_root_path . 'plugins/' . $code)) )
{
    message_die($user->lang['error_invalid_plugin']);
}

switch ( $mode )
{
    case 'install':
        $pm->install($code);
        $pm->do_hooks('/admin/plugins.php?mode=install');
        
        $plugin_object = $pm->get_plugin($code);
        $plugin_object->message(SQL_INSTALL);
        
        break;
    case 'uninstall':
        $pm->uninstall($code);
        $pm->do_hooks('/admin/plugins.php?mode=uninstall');
        
        $plugin_object = $pm->get_plugin($code);
        $plugin_object->message(SQL_UNINSTALL);
        
        break;
    case 'list':
        // Register any new plugins before we list the available ones
        $pm->register();
        
        $unset_array = array();
        $plugins_array = $pm->get_plugins(PLUGIN_ALL);
        foreach ( $plugins_array as $plugin_code => $plugin_object )
        {
            $installed = $pm->check(PLUGIN_INSTALLED, $plugin_code);
            
            // Initialize the object if we need to
            if ( !$pm->check(PLUGIN_INITIALIZED, $plugin_code) )
            {
                if ( $pm->initialize($plugin_code) )
                {
                    $unset_array[] = $plugin_code;
                }
            }
            
            $contact = $pm->get_data($plugin_code, 'contact');
            $version = $pm->get_data($plugin_code, 'version');
            
            $tpl->assign_block_vars('plugins_row', array(
                'ROW_CLASS' => $eqdkp->switch_row_class(),
                'NAME'      => sanitize($pm->get_data($plugin_code, 'name')),
                'CODE'      => sanitize($plugin_code),
                'VERSION'   => ( !empty($version) ) ? sanitize($version) : '&nbsp;',
                'U_ACTION'  => path_default('admin/plugins.php') 
                               . path_params('mode', (( $installed ) ? 'uninstall' : 'install'))
                               . path_params('code', $plugin_code),
                'ACTION'    => ( $installed ) ? $user->lang['uninstall'] : $user->lang['install'],
                'CONTACT'   => ( !is_null($contact) ) ? '<a href="mailto:' . sanitize($contact) . '">' . sanitize($contact) . '</a>' : '&nbsp;')
            );
            unset($contact, $installed, $version);
        }
        
        // Return uninitialized objects to their previous state
        foreach ( $unset_array as $plugin_code )
        {
            unset($pm->plugins[$plugin_code]);
        }
        
        $tpl->assign_vars(array(
            'L_NAME'    => $user->lang['name'],
            'L_CODE'    => $user->lang['code'],
            'L_VERSION' => $user->lang['version'],
            'L_ACTION'  => $user->lang['action'],
            'L_CONTACT' => $user->lang['contact'])
        );
        
        $eqdkp->set_vars(array(
            'page_title'    => page_title($user->lang['plugins_title']),
            'template_file' => 'admin/plugins.html',
            'display'       => true
        ));
 
        break;
}