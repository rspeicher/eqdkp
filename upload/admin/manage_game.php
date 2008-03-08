<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        manage_game.php
 * Began:       Sun Dec 2 2007
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

class EQdkp_Manage_Game extends EQdkp_Admin
{
    function eqdkp_manage_game()
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
        
        return $this->fv->is_error();
    }

    // ---------------------------------------------------------
    // Process submit
    // ---------------------------------------------------------
    function process_submit()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;

        $game_id      = $in->get('new_game','');
        $current_game = $eqdkp->config['current_game'];
        $redirect_url = path_default('admin/manage_game.php');

        if (empty($game_id))
        {
            // FIXME: I don't quite know how I should cause an error here.
            //$fv->error
            meta_refresh(3, $redirect_url);
            trigger_error('You must choose a game.', E_USER_ERROR);
        }

        // Create the game installer
        if (!class_exists('Game_Installer'))
        {
            include($eqdkp->root_path . 'games/game_installer.php');
        }
        $gm = new Game_Installer();
        
        $newgame_id = $gm->set_current_game($game_id);
        if ($newgame_id === false || strpos($game_id, $newgame_id) !== 0)
        {
            // FIXME: I don't quite know how I should cause an error here.
            // FIXME: New language string required.
            meta_refresh(3, $redirect_url);
            trigger_error('The selected game is not a valid EQdkp game.', E_USER_ERROR);
        }
        
        // Run the installation
        $newgame = $gm->get_game_data();
        $result = $gm->install_game();
        
        // Update EQdkp's configuration
        $eqdkp->config_set(array(
            'current_game'      => $db->sql_escape($game_id),
            'current_game_name' => isset($newgame['name']) ? $db->sql_escape($newgame['name']) : $db->sql_escape($game_id),
        ));            
        
        // meta_refresh(3, $redirect_url);
        // FIXME: New language string required.
        //trigger_error('Game successfully updated to ' . $newgame['name']);
        header('Location: ' . $redirect_url);
    }


    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $eqdkp_root_path, $user, $tpl, $in;
        global $gm, $pm;

        $games = $gm->list_games();

        // FIXME: These values *MUST* be discovered from the game language files. Perhaps another task for the game manager...?
        $tpl->assign_vars(array(
            // Form vars
            'F_MANAGE_GAME' => path_default('admin/manage_game.php'),
            
            // Form values
            'CURRENT_GAME_ID'   => sanitize($eqdkp->config['current_game'], ENT),
            'CURRENT_GAME_NAME' => sanitize($eqdkp->config['current_game_name'], ENT),
            
            // Language
            'L_GAME'            => $user->lang['game'],
            'L_GAME_SETTINGS'   => $user->lang['game_settings'],
            'L_CURRENT_GAME'    => $user->lang['current_game'],
            'L_NEW_GAME'        => $user->lang['new_game'],

            'L_YES'         => $user->lang['yes'],
            'L_NO'          => $user->lang['no'],
            'L_SUBMIT'      => $user->lang['submit'],
            'L_RESET'       => $user->lang['reset']
        ));

        // Build the game list
        foreach ($games as $game_id => $game_info)
        {
            $tpl->assign_block_vars('game_row', array(
                'VALUE'    => $game_id,
                'SELECTED' => option_selected($eqdkp->config['current_game'] == $game_id),
                'OPTION'   => $game_info['name'],
            ));
        }

        // Set EQdkp page variables
        $eqdkp->set_vars(array(
            'page_title'    => page_title($user->lang['manage_game_title']),
            'template_file' => 'admin/manage_game.html',
            'display'       => true
        ));
    }
}

$eqdkp_manage_game = new EQdkp_Manage_Game;
$eqdkp_manage_game->process();