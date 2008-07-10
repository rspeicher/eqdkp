<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        manage_users.php
 * Began:       Sun Dec 29 2002
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

class Manage_Users extends EQdkp_Admin
{
    var $change_username = false;       // Was the username changed?                        @var change_username
    var $change_password = false;       // Was the password changed?                        @var change_password
    var $user_data       = array();     // Holds user data if URI_NAME is set               @var user_data

    function manage_users()
    {
        global $db, $eqdkp, $user, $in;

        parent::eqdkp_admin();

        // Vars used to confirm deletion
        $confirm_text = $user->lang['confirm_delete_users'];
        
        $usernames = array();
        $user_ids  = array();
        if ( $in->get('delete', false) )
        {
            $user_ids = $in->getArray('user_id', 'int');
            if ( count($user_ids) > 0 )
            {
                $sql = "SELECT user_name
                        FROM __users
                        WHERE (`user_id` IN (" . $db->escape(',', $user_ids) . "))";
                $result = $db->query($sql);
                while ( $row = $db->fetch_record($result) )
                {
                    $usernames[] = $row['user_name'];
                }

                $names = implode(', ', $usernames);

                $confirm_text .= '<br /><br />' . sanitize($names);
            }
            else
            {
                message_die('No users were selected for deletion.');
            }
        }

        $this->set_vars(array(
            'confirm_text'  => $confirm_text,
            'uri_parameter' => 'users',
            'url_id'        => ( count($user_ids) > 0 ) ? implode(',', $user_ids) : $in->get('username'),
            'script_name'   => path_default('admin/manage_users.php')
        ));

        $this->assoc_buttons(array(
            'submit' => array(
                'name'    => 'submit',
                'process' => 'process_submit',
                'check'   => 'a_users_man'
            ),
            'update' => array(
                'name'    => 'update',
                'process' => 'process_update',
                'check'   => 'a_users_man'
            ),
            'delete' => array(
                'name'    => 'delete',
                'process' => 'process_delete',
                'check'   => 'a_users_man'
            ),
            'form' => array(
                'name'    => '',
                'process' => 'display_list',
                'check'   => 'a_users_man'
            )
        ));

        $this->assoc_params(array(
            'name' => array(
                'name'    => URI_NAME,
                'process' => 'display_form',
                'check'   => 'a_users_man'
            )
        ));
    }

    function error_check()
    {
        global $db, $user, $in;

        // Singular Update
        if ( $in->get('submit', false) )
        {
            // See if the user exists
            $sql = "SELECT au.*, u.*
                    FROM __users AS u LEFT JOIN __auth_users AS au ON u.`user_id` = au.`user_id`
                    WHERE (u.`user_name` = '" . $db->escape($in->get(URI_NAME)) . "')";
            $result = $db->query($sql);
            if ( !$this->user_data = $db->fetch_record($result) )
            {
                message_die($user->lang['error_user_not_found']);
            }
            $db->free_result($result);

            // Error-check the form
            $this->change_username = false;
            
            // The following check is a bit of a hack so that if the value isn't set,
            // Input::get won't return the default empty string (''). That way
            // if neither of these were set, it prevents them from still being equal.
            if ( $in->get('username', 'username') != $in->get(URI_NAME, URI_NAME) )
            {
                // They changed the username, see if it's already registered
                $sql = "SELECT user_id
                        FROM __users
                        WHERE (`user_name` = '" . $db->escape($in->get('username')) . "')";
                if ( $db->num_rows($db->query($sql)) > 0 )
                {
                    $this->fv->errors['username'] = $user->lang['fv_already_registered_username'];
                }
                $this->change_username = true;
            }
            $this->change_password = false;
            if ( $in->get('new_user_password1') != '' && $in->get('new_user_password2') != '' )
            {
                $this->fv->matching_passwords('new_user_password1', 'new_user_password2', $user->lang['fv_match_password']);
                $this->change_password = true;
            }
            $this->fv->is_number(array(
                'user_alimit' => $user->lang['fv_number'],
                'user_elimit' => $user->lang['fv_number'],
                'user_ilimit' => $user->lang['fv_number'],
                'user_nlimit' => $user->lang['fv_number'],
                'user_rlimit' => $user->lang['fv_number']
            ));

            // Make sure any members associated with this account aren't associated with another account
            $member_ids = $in->getArray('member_id', 'int');
            if ( count($member_ids) > 0 )
            {
                // Build array of member_id => member_name
                $member_names = array();
                $sql = "SELECT member_id, member_name
                        FROM __members
                        ORDER BY member_name";
                $result = $db->query($sql);
                while ( $row = $db->fetch_record($result) )
                {
                    $member_names[ $row['member_id'] ] = $row['member_name'];
                }
                $db->free_result($result);

                $sql = "SELECT member_id
                        FROM __member_user
                        WHERE (`member_id` IN (" . $db->escape(',', $member_ids) . "))
                        AND (`user_id` != '" . $db->escape($this->user_data['user_id']) . "')";
                $result = $db->query($sql);

                $fv_member_id = '';
                while ( $row = $db->fetch_record($result) )
                {
                    // This member's associated with another account
                    $fv_member_id .= sprintf($user->lang['fv_member_associated'], sanitize($member_names[ $row['member_id'] ])) . '<br />';
                }
                $db->free_result($result);

                if ( $fv_member_id != '' )
                {
                    $this->fv->errors['member_id'] = $fv_member_id;
                }
            }
            unset($member_ids);
        }
        elseif ( $in->get(URI_NAME, false) )
        {
            // See if the user exists
            $sql = "SELECT au.*, u.*
                    FROM __users AS u LEFT JOIN __auth_users AS au ON u.`user_id` = au.`user_id`
                    WHERE (u.`user_name` = '" . $db->escape($in->get(URI_NAME)) . "')";
            $result = $db->query($sql);
            if ( !$this->user_data = $db->fetch_record($result) )
            {
                message_die($user->lang['error_user_not_found']);
            }
            $db->free_result($result);
        }

        return $this->fv->is_error();
    }

    // ---------------------------------------------------------
    // Process Submit
    // ---------------------------------------------------------
    function process_submit()
    {
        global $db, $eqdkp, $user, $pm, $in;

        $user_id = $this->user_data['user_id'];

        //
        // Build the query
        //
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
            'user_active' => $in->get('user_active', 1),
        );
        if ( $this->change_username )
        {
            $update['user_name'] = $in->get('username');
        }
        if ( $this->change_password )
        {
            $update['user_salt']     = generate_salt();
            $update['user_password'] = hash_password($in->get('new_user_password1'), $update['user_salt']);
        }
        $query = $db->build_query('UPDATE', $update);
        $sql = "UPDATE __users SET {$query} WHERE (`user_id` = '" . $db->escape($this->user_data['user_id']) . "')";

        if ( !($result = $db->query($sql)) )
        {
            message_die('Could not update user information', '', __FILE__, __LINE__, $sql);
        }

        // Permissions
        $sql = "SELECT auth_id, auth_value
                FROM __auth_options
                ORDER BY `auth_id`";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $r_auth_id    = $row['auth_id'];
            $r_auth_value = $row['auth_value'];

            $chk_auth_value = ( $user->check_auth($r_auth_value, false, $user_id) ) ? 'Y' : 'N';
            $db_auth_value  = ( $in->get($r_auth_value, false) )                    ? 'Y' : 'N';

            if ( $chk_auth_value != $db_auth_value )
            {
               $this->update_auth_users($user_id, $r_auth_id, $db_auth_value);
            }
        }
        $db->free_result($result);

        // Users -> Members associations
        $sql = "DELETE FROM __member_user
                WHERE (`user_id` = '" . $db->escape($this->user_data['user_id']) . "')";
        $db->query($sql);

        $member_ids = $in->getArray('member_id', 'int');
        if ( count($member_ids) > 0 )
        {
            $sql = "INSERT INTO __member_user (member_id, user_id) VALUES ";

            $query = array();
            foreach ( $member_ids as $member_id )
            {
                $query[] = "({$member_id},{$this->user_data['user_id']})";
            }

            $sql .= implode(', ', $query);
            $db->query($sql);
        }

        // See if any plugins need to update the DB
        $pm->do_hooks('/admin/manage_users.php?action=update');

        $this->admin_die($user->lang['update_settings_success']);
    }

    // ---------------------------------------------------------
    // Process Mass Update
    // ---------------------------------------------------------
    function process_update()
    {
        global $db, $eqdkp, $user, $pm, $in;

        $user_ids = $in->getArray('user_id', 'int');
        if ( count($user_ids) > 0 )
        {
            // Delete existing permissions for these users
            $sql = "DELETE FROM __auth_users
                    WHERE (user_id IN (" . $db->escape(',', $user_ids) . "))";
            $db->query($sql);

            // Permissions
            $sql = "SELECT auth_id, auth_value
                    FROM __auth_options
                    ORDER BY auth_id";
            $result = $db->query($sql);
            while ( $row = $db->fetch_record($result) )
            {
                $permissions[ $row['auth_id'] ] = $row['auth_value'];
            }
            $db->free_result($result);

            foreach ( $user_ids as $user_id )
            {
                $query = array();
                $sql = "INSERT INTO __auth_users (user_id, auth_id, auth_setting) VALUES ";
                foreach ( $permissions as $auth_id => $auth_value )
                {
                    $query[] = "('{$user_id}','{$auth_id}','" . (( $in->exists($auth_value) ) ? 'Y' : 'N') . "')";
                }
                $db->query($sql . implode(', ', $query));
            }

            $this->admin_die($user->lang['admin_set_perms_success']);
        }
        else
        {
            message_die('No users were selected for updating.');
        }
    }

    // ---------------------------------------------------------
    // Process (Mass) Delete
    // ---------------------------------------------------------
    function process_confirm()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;

        if ( $in->get('users', false) )
        {
            $user_ids = explode(',', $in->get('users'));
            // Make sure each of these is actually an integer
            foreach ( $user_ids as $k => $v )
            {
                $user_ids[$k] = intval($v);
            }
            // And right back where we started, implode them back together
            $user_ids = implode(',', $user_ids);
            
            // Find usernames for the pretty message at the end
            $usernames = array();
            $sql = "SELECT user_id, user_name
                    FROM __users
                    WHERE (`user_id` IN ({$user_ids}))
                    ORDER BY user_name";
            $result = $db->query($sql);
            while ( $row = $db->fetch_record($result) )
            {
                $usernames[] = $row['user_name'];
            }
            $db->free_result($result);
            
            // Delete from auth_user
            $sql = "DELETE FROM __auth_users
                    WHERE (`user_id` IN ({$user_ids}))";
            $db->query($sql);

            // Delete from users
            $sql = "DELETE FROM __users
                    WHERE (`user_id` IN ({$user_ids}))";
            $db->query($sql);

            // Delete from member users
            $sql = "DELETE FROM __member_user
                    WHERE (`user_id` IN ({$user_ids}))";
            $db->query($sql);

            // Success message
            $success_message = '';
            foreach ( $usernames as $username )
            {
                $success_message .= sprintf($user->lang['admin_delete_user_success'], sanitize($username)) . '<br />';
            }

            $link_list = array($user->lang['manage_users'] => path_default('admin/manage_users.php'));

            $this->admin_die($success_message, $link_list);
        }
        else
        {
            message_die('No users were selected for deleting.');
        }
    }

    // ---------------------------------------------------------
    // Process helper methods
    // ---------------------------------------------------------
    function update_auth_users($user_id, $auth_id, $auth_setting = 'N')
    {
        global $db;

        if ( (empty($auth_id)) || (empty($user_id)) )
        {
            return false;
        }
        
        $user_id = intval($user_id);
        $auth_id = intval($auth_id);
        $sql = "REPLACE INTO __auth_users (user_id, auth_id, auth_setting)
                VALUES ('{$user_id}', '{$auth_id}', '" . $db->escape($auth_setting) . "')";
        $db->query($sql);
    }

    // ---------------------------------------------------------
    // Display
    // ---------------------------------------------------------
    function display_list()
    {
        global $db, $eqdkp, $user, $tpl, $in;

        $sort_order = array(
            0 => array('u.user_name', 'u.user_name desc'),
            1 => array('u.user_email', 'u.user_email desc'),
            2 => array('u.user_lastvisit desc', 'u.user_lastvisit'),
            3 => array('u.user_active desc', 'u.user_active'),
            4 => array('s.session_id desc', 's.session_id')
        );

        $current_order = switch_order($sort_order);

        $total_users = $db->query_first("SELECT COUNT(*) FROM __users");
        $start = $in->get('start', 0);

        $sql = "SELECT u.user_id, u.user_name, u.user_email, u.user_lastvisit, u.user_active, s.session_id
                FROM __users AS u LEFT JOIN __sessions AS s ON u.`user_id` = s.`user_id`
                GROUP BY u.`user_name`
                ORDER BY {$current_order['sql']}
                LIMIT {$start},100";
        if ( !($result = $db->query($sql)) )
        {
            message_die('Could not obtain user information', '', __FILE__, __LINE__, $sql);
        }
        while ( $row = $db->fetch_record($result) )
        {
            $user_online = ( !empty($row['session_id']) ) ? $user->lang['yes'] : $user->lang['no'];
            $user_active = ( $row['user_active'] == '1' ) ? $user->lang['yes'] : $user->lang['no'];

            $tpl->assign_block_vars('users_row', array(
                'ROW_CLASS'     => $eqdkp->switch_row_class(),
                'U_MANAGE_USER' => edit_user_path($row['user_name']),
                'USER_ID'       => intval($row['user_id']),
                'NAME_STYLE'    => ( $user->check_auth('a_', false, $row['user_id']) ) ? 'font-weight: bold' : 'font-weight: none',
                'USERNAME'      => sanitize($row['user_name']),
                'U_MAIL_USER'   => ( !empty($row['user_email']) ) ? 'mailto:' . sanitize($row['user_email']) : '',
                'EMAIL'         => ( !empty($row['user_email']) ) ? sanitize($row['user_email']) : '&nbsp;',
                'LAST_VISIT'    => date($user->style['date_time'], $row['user_lastvisit']),
                'ACTIVE'        => $user_active,
                'ONLINE'        => $user_online
            ));
        }
        $db->free_result($result);

        $user_permissions = generate_permission_boxes();

        // Find out our auth defaults
        $auth_defaults = array();
        $sql = "SELECT auth_id, auth_value, auth_default
                FROM __auth_options
                ORDER BY auth_id";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $auth_defaults[ $row['auth_id'] ] = array(
                'auth_id'      => $row['auth_id'],
                'auth_value'   => $row['auth_value'],
                'auth_default' => $row['auth_default']
            );
        }
        $db->free_result($result);

        foreach ( $user_permissions as $group => $checks )
        {
            $tpl->assign_block_vars('permissions_row', array(
                'GROUP' => $group
            ));

            foreach ( $checks as $data )
            {
                $auth_setting = ( isset($auth_defaults[ $data['CBCHECKED'] ]) ) ? $auth_defaults[ $data['CBCHECKED'] ] : null;
                $tpl->assign_block_vars('permissions_row.check_group', array(
                    'CBNAME'    => $data['CBNAME'],
                    'CBCHECKED' => option_checked(!is_null($auth_setting) && $auth_setting['auth_default'] == 'Y'),
                    'TEXT'      => $data['TEXT']
                ));
            }
        }
        unset($user_permissions);

        $tpl->assign_vars(array(
            // Language
            'L_MANAGE_USERS'     => $user->lang['manage_users'],
            'L_USERNAME'         => $user->lang['username'],
            'L_EMAIL'            => $user->lang['email_address'],
            'L_LAST_VISIT'       => $user->lang['last_visit'],
            'L_ACTIVE'           => $user->lang['active'],
            'L_ONLINE'           => $user->lang['online'],
            'L_MASS_UPDATE'      => $user->lang['mass_update'],
            'L_MASS_UPDATE_NOTE' => $user->lang['mass_update_note'],
            'L_ACCOUNT_ENABLED'  => $user->lang['account_enabled'],
            'L_YES'              => $user->lang['yes'],
            'L_NO'               => $user->lang['no'],
            'L_MASS_DELETE'      => $user->lang['mass_delete'],

            // Sorting
            'O_USERNAME'   => $current_order['uri'][0],
            'O_EMAIL'      => $current_order['uri'][1],
            'O_LAST_VISIT' => $current_order['uri'][2],
            'O_ACTIVE'     => $current_order['uri'][3],
            'O_ONLINE'     => $current_order['uri'][4],

            // Page vars
            'U_MANAGE_USERS'      => edit_user_path() . '&amp;',
            'F_MASS_UPDATE'       => edit_user_path(),
            'START'               => $start,
            'LISTUSERS_FOOTCOUNT' => sprintf($user->lang['listusers_footcount'], $total_users, 100),
            'USER_PAGINATION'     => generate_pagination(edit_user_path() . path_params(URI_ORDER, $current_order['uri']['current']), $total_users, 100, $start)
        ));

        $eqdkp->set_vars(array(
            'page_title'    => page_title($user->lang['manage_users_title']),
            'template_file' => 'admin/listusers.html',
            'display'       => true
        ));
    }

    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;

        $user_id = $this->user_data['user_id'];

        $user_permissions = generate_permission_boxes();

        foreach ( $user_permissions as $group => $checks )
        {
            $tpl->assign_block_vars('permissions_row', array(
                'GROUP' => $group
            ));

            foreach ( $checks as $data )
            {
                $tpl->assign_block_vars('permissions_row.check_group', array(
                    'CBNAME'    => $data['CBNAME'],
                    'CBCHECKED' => option_checked($user->check_auth($data['CBNAME'], false, $user_id)),
                    'TEXT'      => $data['TEXT']
                ));
            }
        }
        unset($user_permissions);

        // Build member drop-down
        $sql = "SELECT m.member_id, m.member_name, mu.user_id
                FROM __members AS m LEFT JOIN __member_user AS mu ON m.`member_id` = mu.`member_id`
                GROUP BY m.member_name
                ORDER BY m.member_name";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $tpl->assign_block_vars('member_row', array(
                'VALUE'    => $row['member_id'],
                'SELECTED' => option_selected(isset($row['user_id']) && $row['user_id'] == $this->user_data['user_id']),
                'OPTION'   => $row['member_name']
            ));
        }
        $db->free_result($result);

        $tpl->assign_vars(array(
            // Form vars
            'F_SETTINGS'         => edit_user_path(),
            'S_CURRENT_PASSWORD' => false,
            'S_NEW_PASSWORD'     => true,
            'S_SETTING_ADMIN'    => true,
            'S_MU_TABLE'         => true,

            // Form values
            'NAME'                    => sanitize($in->get(URI_NAME), ENT),
            'USER_ID'                 => intval($this->user_data['user_id']),
            'USERNAME'                => sanitize($this->user_data['user_name'], ENT),
            'USER_EMAIL'              => sanitize($this->user_data['user_email'], ENT),
            'USER_ALIMIT'             => intval($this->user_data['user_alimit']),
            'USER_ELIMIT'             => intval($this->user_data['user_elimit']),
            'USER_ILIMIT'             => intval($this->user_data['user_ilimit']),
            'USER_NLIMIT'             => intval($this->user_data['user_nlimit']),
            'USER_RLIMIT'             => intval($this->user_data['user_rlimit']),
            'USER_ACTIVE_YES_CHECKED' => option_checked($this->user_data['user_active'] == '1'),
            'USER_ACTIVE_NO_CHECKED'  => option_checked($this->user_data['user_active'] == '0'),

            // Language
            'L_REGISTRATION_INFORMATION' => $user->lang['registration_information'],
            'L_REQUIRED_FIELD_NOTE'      => $user->lang['required_field_note'],
            'L_USERNAME'                 => $user->lang['username'],
            'L_EMAIL_ADDRESS'            => $user->lang['email_address'],
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
            'L_PERMISSIONS'              => $user->lang['permissions'],
            'L_S_ADMIN_NOTE'             => $user->lang['s_admin_note'],
            'L_ACCOUNT_ENABLED'          => $user->lang['account_enabled'],
            'L_YES'                      => $user->lang['yes'],
            'L_NO'                       => $user->lang['no'],
            'L_ASSOCIATED_MEMBERS'       => $user->lang['associated_members'],
            'L_MEMBERS'                  => $user->lang['members'],
            'L_SUBMIT'                   => $user->lang['submit'],
            'L_RESET'                    => $user->lang['reset'],

            // Form validation
            'FV_USERNAME'     => $this->fv->generate_error('username'),
            'FV_NEW_PASSWORD' => $this->fv->generate_error('new_user_password1'),
            'FV_USER_ALIMIT'  => $this->fv->generate_error('user_alimit'),
            'FV_USER_ELIMIT'  => $this->fv->generate_error('user_elimit'),
            'FV_USER_ILIMIT'  => $this->fv->generate_error('user_ilimit'),
            'FV_USER_NLIMIT'  => $this->fv->generate_error('user_nlimit'),
            'FV_USER_RLIMIT'  => $this->fv->generate_error('user_rlimit'),
            'FV_MEMBER_ID'    => $this->fv->generate_error('member_id'))
        );

        $pm->do_hooks('/admin/manage_users.php?action=settings');

        // Build the language drop-down
        foreach ( select_language($this->user_data['user_lang']) as $row )
        {
            $tpl->assign_block_vars('lang_row', $row);
        }
        
        // Build the style drop-down
        foreach ( select_style($this->user_data['user_style']) as $row )
        {
            $tpl->assign_block_vars('style_row', $row);
        }

        $eqdkp->set_vars(array(
            'page_title'    => page_title($user->lang['manage_users_title']),
            'template_file' => 'settings.html',
            'display'       => true
        ));
    }
}

$manage_users = new Manage_users;
$manage_users->process();
