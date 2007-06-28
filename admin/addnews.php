<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * addnews.php
 * Began: Wed December 25 2002
 *
 * $Id: addnews.php 46 2007-06-19 07:29:11Z tsigo $
 *
 ******************************/

define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = './../';
include_once($eqdkp_root_path . 'common.php');

class Add_News extends EQdkp_Admin
{
    var $news     = array();            // Holds news data if URI_NEWS is set               @var news
    var $old_news = array();            // Holds news data from before POST                 @var old_news

    function add_news()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        parent::eqdkp_admin();

        $this->news = array(
            'news_headline' => post_or_db('news_headline'),
            'news_message'  => post_or_db('news_message')
        );

        // Vars used to confirm deletion
        $this->set_vars(array(
            'confirm_text'  => $user->lang['confirm_delete_news'],
            'uri_parameter' => URI_NEWS)
        );

        $this->assoc_buttons(array(
            'add' => array(
                'name'    => 'add',
                'process' => 'process_add',
                'check'   => 'a_news_add'),
            'update' => array(
                'name'    => 'update',
                'process' => 'process_update',
                'check'   => 'a_news_upd'),
            'delete' => array(
                'name'    => 'delete',
                'process' => 'process_delete',
                'check'   => 'a_news_del'),
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_news_'))
        );

        // Build the news array
        // ---------------------------------------------------------
        if ( $this->url_id )
        {
            $sql = 'SELECT news_headline, news_message
                    FROM ' . NEWS_TABLE . "
                    WHERE news_id='" . $this->url_id . "'";
            $result = $db->query($sql);
            if ( !$row = $db->fetch_record($result) )
            {
                message_die($user->lang['error_invalid_news_provided']);
            }
            $db->free_result($result);

            $this->time = time();
            $this->news = array(
                'news_headline' => post_or_db('news_headline', $row),
                'news_message'  => post_or_db('news_message', $row)
            );
        }
    }

    function error_check()
    {
        global $user;

        $this->fv->is_filled(array(
            'news_headline' => $user->lang['fv_required_headline'],
            'news_message'  => $user->lang['fv_required_message'])
        );

        return $this->fv->is_error();
    }

    // ---------------------------------------------------------
    // Process Add
    // ---------------------------------------------------------
    function process_add()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        //
        // Insert the news
        //
        $query = $db->build_query('INSERT', array(
            'news_headline' => stripslashes($_POST['news_headline']),
            'news_message'  => stripslashes($_POST['news_message']),
            'news_date'     => $this->time,
            'user_id'       => $user->data['user_id'])
        );
        $db->query('INSERT INTO ' . NEWS_TABLE . $query);
        $this_news_id = $db->insert_id();

        //
        // Logging
        //
        $log_action = array(
            'header'           => '{L_ACTION_NEWS_ADDED}',
            'id'               => $this_news_id,
            '{L_HEADLINE}'     => $_POST['news_headline'],
            '{L_MESSAGE_BODY}' => nl2br($_POST['news_message']),
            '{L_ADDED_BY}'     => $this->admin_user);
        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action)
        );

        //
        // Success message
        //
        $success_message = $user->lang['admin_add_news_success'];
        $link_list = array(
            $user->lang['add_news']  => 'addnews.php' . $SID,
            $user->lang['list_news'] => 'listnews.php' . $SID);
        $this->admin_die($success_message, $link_list);
    }

    // ---------------------------------------------------------
    // Process Update
    // ---------------------------------------------------------
    function process_update()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        //
        // Get the old data
        //
        $this->get_old_data();

        //
        // Update the news table
        //
        if ( isset($_POST['update_date']) )
        {
            $query = $db->build_query('UPDATE', array(
                'news_headline' => stripslashes($_POST['news_headline']),
                'news_message'  => stripslashes($_POST['news_message']),
                'news_date'     => $this->time)
            );
        }
        else
        {
            $query = $db->build_query('UPDATE', array(
                'news_headline' => stripslashes($_POST['news_headline']),
                'news_message'  => stripslashes($_POST['news_message']))
            );
        }
        $db->query('UPDATE ' . NEWS_TABLE . ' SET ' . $query . " WHERE news_id='" . $this->url_id . "'");

        //
        // Logging
        //
        $log_action = array(
            'header'              => '{L_ACTION_NEWS_UPDATED}',
            'id'                  => $this->url_id,
            '{L_HEADLINE_BEFORE}' => $this->old_news['news_headline'],
            '{L_MESSAGE_BEFORE}'  => nl2br($this->old_news['news_message']),
            '{L_HEADLINE_AFTER}'  => $this->find_difference($this->old_news['news_headline'], $_POST['news_headline']),
            '{L_MESSAGE_AFTER}'   => nl2br($_POST['news_message']),
            '{L_UPDATED_BY}'      => $this->admin_user);
        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action)
        );

        //
        // Success message
        //
        $success_message = $user->lang['admin_update_news_success'];
        $link_list = array(
            $user->lang['add_news']  => 'addnews.php' . $SID,
            $user->lang['list_news'] => 'listnews.php' . $SID);
        $this->admin_die($success_message, $link_list);
    }

    // ---------------------------------------------------------
    // Process Delete (confirmed)
    // ---------------------------------------------------------
    function process_confirm()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        //
        // Get the old data
        //
        $this->get_old_data();

        //
        // Remove the news entry
        //
        $sql = 'DELETE FROM ' . NEWS_TABLE . "
                WHERE news_id='" . $this->url_id . "'";
        $db->query($sql);

        //
        // Logging
        //
        $log_action = array(
            'header'           => '{L_ACTION_NEWS_DELETED}',
            'id'               => $this->url_id,
            '{L_HEADLINE}'     => $this->old_news['news_headline'],
            '{L_MESSAGE_BODY}' => nl2br($this->old_news['news_message']));
        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action)
        );

        //
        // Success message
        //
        $success_message = $user->lang['admin_delete_news_success'];
        $link_list = array(
            $user->lang['add_news']  => 'addnews.php' . $SID,
            $user->lang['list_news'] => 'listnews.php' . $SID);
        $this->admin_die($success_message, $link_list);
    }

    // ---------------------------------------------------------
    // Process helper methods
    // ---------------------------------------------------------
    function get_old_data()
    {
        global $db;

        $sql = 'SELECT news_headline, news_message
                FROM ' . NEWS_TABLE . "
                WHERE news_id='" . $this->url_id . "'";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $this->old_news = array(
                'news_headline' => addslashes($row['news_headline']),
                'news_message'  => addslashes($row['news_message'])
            );
        }
        $db->free_result($result);
    }

    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        $tpl->assign_vars(array(
            // Form vars
            'F_ADD_NEWS' => 'addnews.php' . $SID,
            'NEWS_ID'    => $this->url_id,
            'S_UPDATE'   => ( $this->url_id ) ? true : false,

            // Form values
            'HEADLINE' => stripslashes(htmlspecialchars($this->news['news_headline'])),
            'MESSAGE'  => stripmultslashes($this->news['news_message']),

            // Language (General)
            'L_HEADLINE'       => $user->lang['headline'],
            'L_MESSAGE_BODY'   => $user->lang['message_body'],
            'L_ADD_NEWS'       => $user->lang['add_news'],
            'L_RESET'          => $user->lang['reset'],
            'L_UPDATE_NEWS'    => $user->lang['update_news'],
            'L_DELETE_NEWS'    => $user->lang['delete_news'],
            'L_UPDATE_DATE_TO' => sprintf($user->lang['update_date_to'], date('m/d/y h:ia T', time())),

            // Language (Help messages)
            'L_B_HELP' => $user->lang['b_help'],
            'L_I_HELP' => $user->lang['i_help'],
            'L_U_HELP' => $user->lang['u_help'],
            'L_Q_HELP' => $user->lang['q_help'],
            'L_C_HELP' => $user->lang['c_help'],
            'L_P_HELP' => $user->lang['p_help'],
            'L_W_HELP' => $user->lang['w_help'],

            // Form validation
            'FV_HEADLINE' => $this->fv->generate_error('news_headline'),
            'FV_MESSAGE'  => $this->fv->generate_error('news_message'),

            // Javascript messages
            'MSG_HEADLINE_EMPTY' => $user->lang['fv_required_headline'],
            'MSG_MESSAGE_EMPTY'  => $user->lang['fv_required_message'],

            // Buttons
            'S_ADD' => ( !$this->url_id ) ? true : false)
        );

        $eqdkp->set_vars(array(
            'page_title'    => sprintf($user->lang['admin_title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']).': '.$user->lang['addnews_title'],
            'template_file' => 'admin/addnews.html',
            'display'       => true)
        );
    }
}

$add_news = new Add_News;
$add_news->process();
?>
