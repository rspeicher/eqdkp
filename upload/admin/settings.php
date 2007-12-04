<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        settings.php
 * Began:       Sat Dec 28 2002
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

class EQdkp_Config extends EQdkp_Admin
{
    function eqdkp_config()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        
        parent::eqdkp_admin();
        
        $this->assoc_buttons(array(
            'submit' => array(
                'name'    => 'submit',
                'process' => 'process_submit',
                'check'   => 'a_config_man'
            ),
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_config_man'
            )
        ));
    }
    
    function error_check()
    {
        global $user;
        
        $this->fv->is_number(array(
            'default_alimit'     => $user->lang['fv_number'],
            'default_elimit'     => $user->lang['fv_number'],
            'default_ilimit'     => $user->lang['fv_number'],
            'default_nlimit'     => $user->lang['fv_number'],
            'default_rlimit'     => $user->lang['fv_number'],
            'active_point_adj'   => $user->lang['fv_number'],
            'inactive_point_adj' => $user->lang['fv_number']
        ));
        
        $this->fv->is_within_range('default_alimit', 1, 1000);
        $this->fv->is_within_range('default_elimit', 1, 1000);
        $this->fv->is_within_range('default_ilimit', 1, 1000);
        $this->fv->is_within_range('default_nlimit', 1, 1000);
        $this->fv->is_within_range('default_rlimit', 1, 1000);
        
        return $this->fv->is_error();
    }
    
    // ---------------------------------------------------------
    // Process submit
    // ---------------------------------------------------------
    function process_submit()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;
        
        $parsetags = $in->get('parsetags');
        $parsetags = preg_replace('/([^\w\s]+)/', '', $parsetags);

        // Update each config setting
        $eqdkp->config_set(array(
            'guildtag'           => $in->get('guildtag',           $eqdkp->config['guildtag']),
            'parsetags'          => $parsetags,
            'server_name'        => $in->get('server_name',        $eqdkp->config['server_name']),
            'server_port'        => $in->get('server_port',        intval($eqdkp->config['server_port'])),
            'server_path'        => $in->get('server_path',        $eqdkp->config['server_path']),
            'main_title'         => $in->get('main_title',         $eqdkp->config['main_title']),
            'sub_title'          => $in->get('sub_title',          $eqdkp->config['sub_title']),
            'dkp_name'           => $in->get('dkp_name',           $eqdkp->config['dkp_name']),
            'default_game'       => $in->get('default_game',       $eqdkp->config['default_game']),
            'default_locale'     => $in->get('default_locale',     $eqdkp->config['default_locale']),
            'account_activation' => $in->get('account_activation', intval($eqdkp->config['account_activation'])),
            'default_alimit'     => $in->get('default_alimit',     intval($eqdkp->config['default_alimit'])),
            'default_elimit'     => $in->get('default_elimit',     intval($eqdkp->config['default_elimit'])),
            'default_ilimit'     => $in->get('default_ilimit',     intval($eqdkp->config['default_ilimit'])),
            'default_nlimit'     => $in->get('default_nlimit',     intval($eqdkp->config['default_nlimit'])),
            'default_rlimit'     => $in->get('default_rlimit',     intval($eqdkp->config['default_rlimit'])),
            'default_lang'       => $in->get('default_lang',       $eqdkp->config['default_lang']),
            'default_style'      => $in->get('default_style',      intval($eqdkp->config['default_style'])),
            'hide_inactive'      => $in->get('hide_inactive',      intval($eqdkp->config['hide_inactive'])),
            'inactive_period'    => $in->get('inactive_period',    intval($eqdkp->config['inactive_period'])),
            'active_point_adj'   => $in->get('active_point_adj',   floatval($eqdkp->config['active_point_adj'])),
            'inactive_point_adj' => $in->get('inactive_point_adj', floatval($eqdkp->config['inactive_point_adj'])),
            'enable_gzip'        => $in->get('enable_gzip',        intval($eqdkp->config['enable_gzip'])),
            'cookie_domain'      => $in->get('cookie_domain',      $eqdkp->config['cookie_domain']),
            'cookie_name'        => $in->get('cookie_name',        $eqdkp->config['cookie_name']),
            'cookie_path'        => $in->get('cookie_path',        $eqdkp->config['cookie_path']),
            'session_length'     => $in->get('session_length',     intval($eqdkp->config['session_length'])),
            'admin_email'        => $in->get('admin_email',        $eqdkp->config['admin_email']),
            'start_page'         => $in->get('start_page',         $eqdkp->config['start_page'])
        ));

        $current_game = $eqdkp->config['default_game'];
        $default_game = preg_replace('/[^\w]+/', '', $in->get('default_game', $current_game));
        
        if ( $default_game != $current_game ) 
        {
            // TODO: Change to Game Manager
            include($eqdkp->root_path . 'games/' . $default_game . '.php');
            $game_extension = new Manage_Game;
            $game_extension->process();
        }

        // Permissions
        $sql = "SELECT auth_id, auth_value
                FROM __auth_options
                ORDER BY `auth_id`";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $auth_default = $in->get($row['auth_value'], 'N');
            $this->update_auth_default($row['auth_value'], $auth_default);
        }

        header('Location: ' . path_default('settings.php', true));
    }
    
    // ---------------------------------------------------------
    // Process helper methods
    // ---------------------------------------------------------
    function update_auth_default($auth_value, $auth_default='N')
    {
        global $db;
        
        if ( empty($auth_value) )
        {
            return false;
        }
        
        $auth_value   = $db->escape($auth_value);
        $auth_default = ( $auth_default != 'Y' ) ? 'N' : 'Y';
        
        $sql = "UPDATE __auth_options
                SET `auth_default` = '{$auth_default}'
                WHERE (`auth_value` = '{$auth_value}')";
        if ( !($result = $db->query($sql)) )
        {
            return false;
        }
        
        return true;
    }
    
    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;

        //
        // Find default auth settings
        //
        $auth_defaults = array();
        $sql = "SELECT auth_id, auth_default
                FROM __auth_options
                ORDER BY `auth_id`";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $auth_defaults[$row['auth_id']] = $row['auth_default'];
        }
        $db->free_result($result);
        
        //
        // Build the config permissions
        //
        $config_permissions = generate_permission_boxes();
        foreach ( $config_permissions as $group => $checks )
        {
            $tpl->assign_block_vars('permissions_row', array(
                'GROUP' => $group
            ));
            
            foreach ( $checks as $data )
            {
                $tpl->assign_block_vars('permissions_row.check_group', array(
                    'CBNAME'    => sanitize($data['CBNAME']),
                    'CBCHECKED' => option_checked($auth_defaults[$data['CBCHECKED']] == 'Y'),
                    'TEXT'      => sanitize($data['TEXT'])
                ));
            }
        }
        unset($config_permissions);
        
        $tpl->assign_vars(array(
            // Form vars
            'F_CONFIG' => path_default('settings.php', true),
            
            // Form values
            'GUILDTAG'                  => sanitize($eqdkp->config['guildtag'], ENT),
            'PARSETAGS'                 => sanitize($eqdkp->config['parsetags'], ENT),
            'SERVER_NAME'               => sanitize($eqdkp->config['server_name'], ENT),
            'SERVER_PORT'               => intval($eqdkp->config['server_port']),
            'SERVER_PATH'               => sanitize($eqdkp->config['server_path'], ENT),
            'MAIN_TITLE'                => sanitize($eqdkp->config['main_title'], ENT),
            'SUB_TITLE'                 => sanitize($eqdkp->config['sub_title'], ENT),
            'DKP_NAME'                  => sanitize($eqdkp->config['dkp_name'], ENT),
            'ACTIVATION_NONE_CHECKED'   => option_checked($eqdkp->config['account_activation'] == USER_ACTIVATION_NONE),
            'ACTIVATION_USER_CHECKED'   => option_checked($eqdkp->config['account_activation'] == USER_ACTIVATION_SELF),
            'ACTIVATION_ADMIN_CHECKED'  => option_checked($eqdkp->config['account_activation'] == USER_ACTIVATION_ADMIN),
            'DEFAULT_ALIMIT'            => intval($eqdkp->config['default_alimit']),
            'DEFAULT_ELIMIT'            => intval($eqdkp->config['default_elimit']),
            'DEFAULT_ILIMIT'            => intval($eqdkp->config['default_ilimit']),
            'DEFAULT_NLIMIT'            => intval($eqdkp->config['default_nlimit']),
            'DEFAULT_RLIMIT'            => intval($eqdkp->config['default_rlimit']),
            'HIDE_INACTIVE_YES_CHECKED' => option_checked($eqdkp->config['hide_inactive'] == '1'),
            'HIDE_INACTIVE_NO_CHECKED'  => option_checked($eqdkp->config['hide_inactive'] == '0'),
            'INACTIVE_PERIOD'           => intval($eqdkp->config['inactive_period']),
            'ACTIVE_POINT_ADJ'          => number_format($eqdkp->config['active_point_adj'], 2),
            'INACTIVE_POINT_ADJ'        => number_format($eqdkp->config['inactive_point_adj'], 2),
            'GZIP_YES_CHECKED'          => option_checked($eqdkp->config['enable_gzip'] == '1'),
            'GZIP_NO_CHECKED'           => option_checked($eqdkp->config['enable_gzip'] == '0'),
            'COOKIE_DOMAIN'             => sanitize($eqdkp->config['cookie_domain'], ENT),
            'COOKIE_NAME'               => sanitize($eqdkp->config['cookie_name'], ENT),
            'COOKIE_PATH'               => sanitize($eqdkp->config['cookie_path'], ENT),
            'SESSION_LENGTH'            => intval($eqdkp->config['session_length']),
            'ADMIN_EMAIL'               => sanitize($eqdkp->config['admin_email'], ENT),
            'DEFAULT_GAME'              => sanitize($eqdkp->config['default_game'], ENT),

            // Language (General Settings)
            'L_GENERAL_SETTINGS'          => $user->lang['general_settings'],
            'L_GUILDTAG'                  => $user->lang['guildtag'],
            'L_GUILDTAG_NOTE'             => $user->lang['guildtag_note'],
            'L_PARSETAGS'                 => $user->lang['parsetags'],
            'L_PARSETAGS_NOTE'            => $user->lang['parsetags_note'],
            'L_DOMAIN_NAME'               => $user->lang['domain_name'],
            'L_SERVER_PORT'               => $user->lang['server_port'],
            'L_SERVER_PORT_NOTE'          => $user->lang['server_port_note'],
            'L_SCRIPT_PATH'               => $user->lang['script_path'],
            'L_SCRIPT_PATH_NOTE'          => $user->lang['script_path_note'],
            'L_SITE_NAME'                 => $user->lang['site_name'],
            'L_SITE_DESCRIPTION'          => $user->lang['site_description'],
            'L_POINT_NAME'                => $user->lang['point_name'],
            'L_POINT_NAME_NOTE'           => $user->lang['point_name_note'],
            'L_ENABLE_ACCOUNT_ACTIVATION' => $user->lang['enable_account_activation'],
            'L_NONE'                      => $user->lang['none'],
            'L_USER'                      => $user->lang['user'],
            'L_ADMIN'                     => $user->lang['admin'],
            'L_ADJUSTMENTS_PER_PAGE'      => $user->lang['adjustments_per_page'],
            'L_EVENTS_PER_PAGE'           => $user->lang['events_per_page'],
            'L_ITEMS_PER_PAGE'            => $user->lang['items_per_page'],
            'L_NEWS_PER_PAGE'             => $user->lang['news_per_page'],
            'L_RAIDS_PER_PAGE'            => $user->lang['raids_per_page'],
            'L_DEFAULT_LANGUAGE'          => $user->lang['default_language'],
            'L_DEFAULT_GAME'              => $user->lang['default_game'],
            'L_DEFAULT_GAME_WARN'         => $user->lang['default_game_warn'],
            'L_DEFAULT_STYLE'             => $user->lang['default_style'],
            'L_DEFAULT_PAGE'              => $user->lang['default_page'],
            'L_DEFAULT_LOCALE'            => $user->lang['default_locale'],
            'L_PREVIEW'                   => $user->lang['preview'],
            'L_HIDE_INACTIVE'             => $user->lang['hide_inactive'],
            'L_HIDE_INACTIVE_NOTE'        => $user->lang['hide_inactive_note'],
            'L_INACTIVE_PERIOD'           => $user->lang['inactive_period'],
            'L_INACTIVE_PERIOD_NOTE'      => $user->lang['inactive_period_note'],
            'L_ACTIVE_POINT_ADJ'          => $user->lang['active_point_adj'],
            'L_ACTIVE_POINT_ADJ_NOTE'     => $user->lang['active_point_adj_note'],
            'L_INACTIVE_POINT_ADJ'        => $user->lang['inactive_point_adj'],
            'L_INACTIVE_POINT_ADJ_NOTE'   => $user->lang['inactive_point_adj_note'],
            'L_ENABLE_GZIP'               => $user->lang['enable_gzip'],
            
            // Language (Default Permissions)
            'L_DEFAULT_PERMISSIONS'      => $user->lang['default_permissions'],
            'L_DEFAULT_PERMISSIONS_NOTE' => $user->lang['default_permissions_note'],
            
            // Language (Cookie Settings)
            'L_COOKIE_SETTINGS' => $user->lang['cookie_settings'],
            'L_COOKIE_DOMAIN'   => $user->lang['cookie_domain'],
            'L_COOKIE_NAME'     => $user->lang['cookie_name'],
            'L_COOKIE_PATH'     => $user->lang['cookie_path'],
            'L_SESSION_LENGTH'  => $user->lang['session_length'],
            
            // Language (E-mail Settings)
            'L_EMAIL_SETTINGS' => $user->lang['email_settings'],
            'L_ADMIN_EMAIL'    => $user->lang['admin_email'],
            
            // Language
            'L_YES'    => $user->lang['yes'],
            'L_NO'     => $user->lang['no'],
            'L_SUBMIT' => $user->lang['submit'],
            'L_RESET'  => $user->lang['reset']
        ));

        // Build language drop-down
        foreach ( select_language($eqdkp->config['default_lang']) as $row )
        {
            $tpl->assign_block_vars('lang_row', $row);
        }

        // Build style drop-down
        foreach ( select_style($eqdkp->config['default_style']) as $row )
        {
            $tpl->assign_block_vars('style_row', $row);
        }

        //
        // Build game option drop-down
        // New for 1.3
        // Total hack job - I moved the class, race, and faction 
        // info to the db, but I'm hardcoding what games I support 
        // for the "button push" - what a tard I am :-)
        // To add a new game option, just copy the 4 lines below,
        // add them to the botton, increase the value of VALUE by 1,
        // and be sure to set OPTION to the EXACT SAME THING you put
        // in the == check in the SELECTED line and there must be
        // no spaces in the name, since the value below gets changed
        // to name.php and ran when you change it: for example,
        // if you change to WoW, this program will redirect you to
        // WoW.php and use that file to populate the database.
        // 
        // Cheesy, but extensible and effective.
        //
        
        // ^ Hey, if you have to describe something as a "total hack job", it probably sucks
        // TODO: Use Game_Manager to abstract the game selection drop-down
        $games = array('Everquest', 'Everquest2', 'WoW', 'DAoC', 'Vanguard-SoH');
        foreach ( $games as $game )
        {
            $tpl->assign_block_vars('game_row', array(
                'VALUE'    => $game,
                'SELECTED' => option_selected($eqdkp->config['default_game'] == $game),
                'OPTION'   => $game
            ));
        }
        unset($games);

        // Default locale drop-down
        // new for 1.3
        // Dont forget to change the install script -- maybe query the system for all supported
        // locales? that would break the "pretty" name of the locale (english, french, etc)
        // but would provide greater support

        // TODO: Abstract the available locales?
        $locales = array('en_US', 'de_DE', 'fr_FR');
        foreach ( $locales as $locale )
        {
            $tpl->assign_block_vars('locale_row', array(
                'VALUE'    => $locale,
                'SELECTED' => option_selected($eqdkp->config['default_locale'] == $locale),
                'OPTION'   => $locale
            ));            
        }

        //
        // Build start page drop-down
        //
        $menus = $eqdkp->gen_menus();
        $pages = array_merge($menus['menu1'], $menus['menu2']);
        unset($menus);
        
        foreach ( $pages as $page )
        {
            $link = preg_replace('#\?' . URI_SESSION . '\=([0-9A-Za-z]{1,32})?#', '', $page['link']);
            $link = preg_replace('#\.php&amp;#', '.php?', $link);
            
            // Remove the username from the logout menu option
            $text = ( isset($user->data['username']) ) ? str_replace($user->data['username'], $user->lang['username'], $page['text']) : $page['text'];
            
            $tpl->assign_block_vars('page_row', array(
                'VALUE'    => preg_replace('/^[\/\.]+([\w\/\.\-]+)$/', '\1\2', $link), // Remove any path traversals at the start of the link
                'SELECTED' => option_selected($eqdkp->config['start_page'] == $link),
                'OPTION'   => $text
            ));
            unset($link, $text);
        }
        
        $eqdkp->set_vars(array(
            'page_title'    => page_title($user->lang['settings_title']),
            'template_file' => 'admin/settings.html',
            'display'       => true
        ));
    }
}

$eqdkp_config = new EQdkp_Config;
$eqdkp_config->process();