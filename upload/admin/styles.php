<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        styles.php
 * Began:       Thu Jan 16 2003
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

class Manage_Styles extends EQdkp_Admin
{
    var $style = array();
    
    function manage_styles()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;
        
        parent::eqdkp_admin();
        
        $defaults = array(
            'attendees_columns' => 8,
            'date_notime_long'  => 'F j, Y',
            'date_notime_short' => 'm/d/y',
            'date_time'         => 'm/d/y h:ia T',
            'logo_path'         => 'logo.gif'
        );
        
        $this->style = array(
            'style_name'         => $in->get('style_name'),
            'template_path'      => $in->get('template_path'),
            'body_background'    => $in->get('body_background'),
            'body_link'          => $in->get('body_link'),
            'body_link_style'    => $in->get('body_link_style'),
            'body_hlink'         => $in->get('body_hlink'),
            'body_hlink_style'   => $in->get('body_hlink_style'),
            'header_link'        => $in->get('header_link'),
            'header_link_style'  => $in->get('header_link_style'),
            'header_hlink'       => $in->get('header_hlink'),
            'header_hlink_style' => $in->get('header_hlink_style'),
            'tr_color1'          => $in->get('tr_color1'),
            'tr_color2'          => $in->get('tr_color2'),
            'th_color1'          => $in->get('th_color1'),
            'fontface1'          => $in->get('fontface1'),
            'fontface2'          => $in->get('fontface2'),
            'fontface3'          => $in->get('fontface3'),
            'fontsize1'          => $in->get('fontsize1', 0),
            'fontsize2'          => $in->get('fontsize2', 0),
            'fontsize3'          => $in->get('fontsize3', 0),
            'fontcolor1'         => $in->get('fontcolor1'),
            'fontcolor2'         => $in->get('fontcolor2'),
            'fontcolor3'         => $in->get('fontcolor3'),
            'fontcolor_neg'      => $in->get('fontcolor_neg'),
            'fontcolor_pos'      => $in->get('fontcolor_pos'),
            'table_border_width' => $in->get('table_border_width', 0),
            'table_border_color' => $in->get('table_border_color'),
            'table_border_style' => $in->get('table_border_style'),
            'input_color'        => $in->get('input_color'),
            'input_border_width' => $in->get('input_border_width', 0),
            'input_border_color' => $in->get('input_border_color'),
            'input_border_style' => $in->get('input_border_style'),
            'attendees_columns'  => $in->get('attendees_columns', $defaults['attendees_columns']),
            'date_notime_long'   => $in->get('date_notime_long',  $defaults['date_notime_long']),
            'date_notime_short'  => $in->get('date_notime_short', $defaults['date_notime_short']),
            'date_time'          => $in->get('date_time',         $defaults['date_time']),
            'logo_path'          => $in->get('logo_path',         $defaults['logo_path'])
        );
        
        // Vars used to confirm deletion
        $this->set_vars(array(
            'confirm_text'  => $user->lang['confirm_delete_style'],
            'uri_parameter' => 'styleid'
        ));
        
        $this->assoc_buttons(array(
            'add' => array(
                'name'    => 'add',
                'process' => 'process_add',
                'check'   => 'a_styles_man'
            ),
            'update' => array(
                'name'    => 'update',
                'process' => 'process_update',
                'check'   => 'a_styles_man'
            ),
            'delete' => array(
                'name'    => 'delete',
                'process' => 'process_delete',
                'check'   => 'a_styles_man'
            ),
            'form' => array(
                'name'    => '',
                'process' => 'display_list',
                'check'   => 'a_styles_man'
            )
        ));
        
        $this->assoc_params(array(
            'create' => array(
                'name'    => 'mode',
                'value'   => 'create',
                'process' => 'display_form',
                'check'   => 'a_styles_man'
            ),
            'edit' => array(
                'name'    => 'styleid',
                'process' => 'display_form',
                'check'   => 'a_styles_man'
            )
        ));
        
        // Build the style array
        // ---------------------------------------------------------
        if ( $this->url_id )
        {
            $sql = "SELECT s.*, c.*
                    FROM __styles AS s, __style_config AS c
                    WHERE (s.style_id = c.style_id)
                    AND (s.`style_id` = '{$this->url_id}')";
            $result = $db->query($sql);
            if ( !$row = $db->fetch_record($result) )
            {
                message_die($user->lang['error_invalid_style']);
            }
            $db->free_result($result);
            
            $this->style = array(
                'style_name'         => $in->get('style_name',         $row['style_name']),
                'template_path'      => $in->get('template_path',      $row['template_path']),
                'body_background'    => $in->get('body_background',    $row['body_background']),
                'body_link'          => $in->get('body_link',          $row['body_link']),
                'body_link_style'    => $in->get('body_link_style',    $row['body_link_style']),
                'body_hlink'         => $in->get('body_hlink',         $row['body_hlink']),
                'body_hlink_style'   => $in->get('body_hlink_style',   $row['body_hlink_style']),
                'header_link'        => $in->get('header_link',        $row['header_link']),
                'header_link_style'  => $in->get('header_link_style',  $row['header_link_style']),
                'header_hlink'       => $in->get('header_hlink',       $row['header_hlink']),
                'header_hlink_style' => $in->get('header_hlink_style', $row['header_hlink_style']),
                'tr_color1'          => $in->get('tr_color1',          $row['tr_color1']),
                'tr_color2'          => $in->get('tr_color2',          $row['tr_color2']),
                'th_color1'          => $in->get('th_color1',          $row['th_color1']),
                'fontface1'          => $in->get('fontface1',          $row['fontface1']),
                'fontface2'          => $in->get('fontface2',          $row['fontface2']),
                'fontface3'          => $in->get('fontface3',          $row['fontface3']),
                'fontsize1'          => $in->get('fontsize1',          intval($row['fontsize1'])),
                'fontsize2'          => $in->get('fontsize2',          intval($row['fontsize2'])),
                'fontsize3'          => $in->get('fontsize3',          intval($row['fontsize3'])),
                'fontcolor1'         => $in->get('fontcolor1',         $row['fontcolor1']),
                'fontcolor2'         => $in->get('fontcolor2',         $row['fontcolor2']),
                'fontcolor3'         => $in->get('fontcolor3',         $row['fontcolor3']),
                'fontcolor_neg'      => $in->get('fontcolor_neg',      $row['fontcolor_neg']),
                'fontcolor_pos'      => $in->get('fontcolor_pos',      $row['fontcolor_pos']),
                'table_border_width' => $in->get('table_border_width', intval($row['table_border_width'])),
                'table_border_color' => $in->get('table_border_color', $row['table_border_color']),
                'table_border_style' => $in->get('table_border_style', $row['table_border_style']),
                'input_color'        => $in->get('input_color',        $row['input_color']),
                'input_border_width' => $in->get('input_border_width', intval($row['input_border_width'])),
                'input_border_color' => $in->get('input_border_color', $row['input_border_color']),
                'input_border_style' => $in->get('input_border_style', $row['input_border_style']),
                'attendees_columns'  => $in->get('attendees_columns',  intval($row['attendees_columns'])),
                'date_notime_long'   => $in->get('date_notime_long',   $row['date_notime_long']),
                'date_notime_short'  => $in->get('date_notime_short',  $row['date_notime_short']),
                'date_time'          => $in->get('date_time',          $row['date_time']),
                'logo_path'          => $in->get('logo_path',          $row['logo_path'])
            );
        }
    }
    
    function error_check()
    {
        return false;
    }
    
    // ---------------------------------------------------------
    // Process Add
    // ---------------------------------------------------------
    function process_add()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;

        $query = $db->build_query('INSERT', array(
            'style_name'         => $in->get('style_name'),
            'template_path'      => $in->get('template_path', 'default'),
            'body_background'    => $in->get('body_background'),
            'body_link'          => $in->get('body_link'),
            'body_link_style'    => $in->get('body_link_style'),
            'body_hlink'         => $in->get('body_hlink'),
            'body_hlink_style'   => $in->get('body_hlink_style'),
            'header_link'        => $in->get('header_link'),
            'header_link_style'  => $in->get('header_link_style'),
            'header_hlink'       => $in->get('header_hlink'),
            'header_hlink_style' => $in->get('header_hlink_style'),
            'tr_color1'          => $in->get('tr_color1'),
            'tr_color2'          => $in->get('tr_color2'),
            'th_color1'          => $in->get('th_color1'),
            'fontface1'          => $in->get('fontface1'),
            'fontface2'          => $in->get('fontface2'),
            'fontface3'          => $in->get('fontface3'),
            'fontsize1'          => $in->get('fontsize1', 0),
            'fontsize2'          => $in->get('fontsize2', 0),
            'fontsize3'          => $in->get('fontsize3', 0),
            'fontcolor1'         => $in->get('fontcolor1'),
            'fontcolor2'         => $in->get('fontcolor2'),
            'fontcolor3'         => $in->get('fontcolor3'),
            'fontcolor_neg'      => $in->get('fontcolor_neg'),
            'fontcolor_pos'      => $in->get('fontcolor_pos'),
            'table_border_width' => $in->get('table_border_width', 0),
            'table_border_color' => $in->get('table_border_color'),
            'table_border_style' => $in->get('table_border_style'),
            'input_color'        => $in->get('input_color'),
            'input_border_width' => $in->get('input_border_width', 0),
            'input_border_color' => $in->get('input_border_color'),
            'input_border_style' => $in->get('input_border_style'),
        ));
        $db->query("INSERT INTO __styles {$query}");
        $style_id = $db->insert_id();
        
        $query = $db->build_query('INSERT', array(
            'style_id'          => $in->get('style_id', intval($eqdkp->config['default_style'])),
            'attendees_columns' => $in->get('attendees_columns', 8),
            'date_notime_long'  => $in->get('date_notime_long'),
            'date_notime_short' => $in->get('date_notime_short'),
            'date_time'         => $in->get('date_time'),
            'logo_path'         => $in->get('logo_path')
        ));
        $db->query("INSERT INTO __style_config {$query}");
        
        message_die($user->lang['admin_add_style_success']);
    }
    
    // ---------------------------------------------------------
    // Process Update
    // ---------------------------------------------------------
    function process_update()
    {
        global $db, $eqdkp, $user, $tpl, $pm, $in;
        
        $query = $db->build_query('UPDATE', array(
            'style_name'         => $in->get('style_name'),
            'template_path'      => $in->get('template_path'),
            'body_background'    => $in->get('body_background'),
            'body_link'          => $in->get('body_link'),
            'body_link_style'    => $in->get('body_link_style'),
            'body_hlink'         => $in->get('body_hlink'),
            'body_hlink_style'   => $in->get('body_hlink_style'),
            'header_link'        => $in->get('header_link'),
            'header_link_style'  => $in->get('header_link_style'),
            'header_hlink'       => $in->get('header_hlink'),
            'header_hlink_style' => $in->get('header_hlink_style'),
            'tr_color1'          => $in->get('tr_color1'),
            'tr_color2'          => $in->get('tr_color2'),
            'th_color1'          => $in->get('th_color1'),
            'fontface1'          => $in->get('fontface1'),
            'fontface2'          => $in->get('fontface2'),
            'fontface3'          => $in->get('fontface3'),
            'fontsize1'          => $in->get('fontsize1', 0),
            'fontsize2'          => $in->get('fontsize2', 0),
            'fontsize3'          => $in->get('fontsize3', 0),
            'fontcolor1'         => $in->get('fontcolor1'),
            'fontcolor2'         => $in->get('fontcolor2'),
            'fontcolor3'         => $in->get('fontcolor3'),
            'fontcolor_neg'      => $in->get('fontcolor_neg'),
            'fontcolor_pos'      => $in->get('fontcolor_pos'),
            'table_border_width' => $in->get('table_border_width', 0),
            'table_border_color' => $in->get('table_border_color'),
            'table_border_style' => $in->get('table_border_style'),
            'input_color'        => $in->get('input_color'),
            'input_border_width' => $in->get('input_border_width', 0),
            'input_border_color' => $in->get('input_border_color'),
            'input_border_style' => $in->get('input_border_style'),
        ));
        $db->query("UPDATE __styles SET {$query} WHERE (`style_id` = '{$this->url_id}')");
        
        $query = $db->build_query('UPDATE', array(
            'attendees_columns' => $in->get('attendees_columns', 8),
            'date_notime_long'  => $in->get('date_notime_long'),
            'date_notime_short' => $in->get('date_notime_short'),
            'date_time'         => $in->get('date_time'),
            'logo_path'         => $in->get('logo_path')
        ));
        $db->query("UPDATE __style_config SET {$query} WHERE (`style_id` = '{$this->url_id}')");
        
        message_die($user->lang['admin_update_style_success']);
    }
    
    // ---------------------------------------------------------
    // Process Delete (confirmed)
    // ---------------------------------------------------------
    function process_confirm()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        
        $db->query("DELETE FROM __styles       WHERE (`style_id` = '{$this->url_id}')");
        $db->query("DELETE FROM __style_config WHERE (`style_id` = '{$this->url_id}')");
        
        message_die($user->lang['admin_delete_style_success']);
    }
    
    // ---------------------------------------------------------
    // Process helper methods
    // ---------------------------------------------------------
    
    // ---------------------------------------------------------
    // Display
    // ---------------------------------------------------------
    function display_list()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        
        $sql = "SELECT style_id, style_name, template_path, COUNT(u.user_id) AS users
                FROM __styles AS s LEFT JOIN __users AS u ON u.`user_style` = s.`style_id`
                GROUP BY s.`style_id`
                ORDER BY s.`style_name`";
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $tpl->assign_block_vars('styles_row', array(
                'ROW_CLASS'    => $eqdkp->switch_row_class(),
                'U_EDIT_STYLE' => path_default('styles.php', true) . path_params('styleid', $row['style_id']),
                'NAME'         => sanitize($row['style_name']),
                'TEMPLATE'     => sanitize($row['template_path']),
                'USERS'        => intval($row['users']),
                'U_PREVIEW'    => path_default('styles.php', true) . path_params('style', $row['style_id'])
            ));
        }
        $db->free_result($result);
        
        $tpl->assign_vars(array(
            'L_NAME'     => $user->lang['name'],
            'L_TEMPLATE' => $user->lang['template'],
            'L_USERS'    => $user->lang['users'],
            'L_PREVIEW'  => $user->lang['preview']
        ));
        
        $eqdkp->set_vars(array(
            'page_title'    => page_title($user->lang['styles_title']),
            'template_file' => 'admin/styles.html',
            'display'       => true
        ));
    }
    
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        
        $text_decoration = array(
            'none',
            'underline',
            'overline',
            'line-through',
            'blink'
        );
        $border_style = array(
            'none',
            'hidden',
            'dotted',
            'dashed',
            'solid',
            'double',
            'groove',
            'ridge',
            'inset',
            'outset'
        );
        
        // Available templates
        foreach ( select_template($this->style['template_path']) as $row )
        {
            $tpl->assign_block_vars('template_row', $row);
        }
        
        //
        // Text decorations
        //
        $decoration_blocks = array('body_link_style', 'body_hlink_style', 'header_link_style', 'header_hlink_style');
        foreach ( $text_decoration as $k => $v )
        {
            foreach ( $decoration_blocks as $block )
            {
                $tpl->assign_block_vars("{$block}_row", array(
                    'VALUE'    => $v,
                    'SELECTED' => option_selected($this->style[$block] == $v),
                    'OPTION'   => $v
                ));
            }
        }
        unset($decoration_blocks, $text_decoration);
        
        //
        // Border styles
        //
        $border_blocks = array('table_border_style', 'input_border_style');
        foreach ( $border_style as $k => $v )
        {
            foreach ( $border_blocks as $block )
            {
                $tpl->assign_block_vars("{$block}_row", array(
                    'VALUE'    => $v,
                    'SELECTED' => option_selected($this->style[$block] == $v),
                    'OPTION'   => $v
                ));
            }
        }
        unset($border_blocks, $border_style);
        
        //
        // Attendees columns
        //
        for ( $i = 1; $i < 11; $i++)
        {
            $tpl->assign_block_vars('attendees_columns_row', array(
                'VALUE'    => $i,
                'SELECTED' => option_selected($this->style['attendees_columns'] == $i),
                'OPTION'   => $i
            ));
        }
        
        $tpl->assign_vars(array(
            // Form vars
            'F_ADD_STYLE' => path_default('styles.php', true),
            'STYLE_ID'    => $this->url_id,
            
            // Form Values
            'STYLE_NAME'         => sanitize($this->style['style_name'], ENT),
            'BODY_BACKGROUND'    => sanitize($this->style['body_background'], ENT),
            'BODY_LINK'          => sanitize($this->style['body_link'], ENT),
            'BODY_HLINK'         => sanitize($this->style['body_hlink'], ENT),
            'HEADER_LINK'        => sanitize($this->style['header_link'], ENT),
            'HEADER_HLINK'       => sanitize($this->style['header_hlink'], ENT),
            'TR_COLOR1'          => sanitize($this->style['tr_color1'], ENT),
            'TR_COLOR2'          => sanitize($this->style['tr_color2'], ENT),
            'TH_COLOR1'          => sanitize($this->style['th_color1'], ENT),
            'FONTFACE1'          => sanitize($this->style['fontface1'], ENT),
            'FONTFACE2'          => sanitize($this->style['fontface2'], ENT),
            'FONTFACE3'          => sanitize($this->style['fontface3'], ENT),
            'FONTSIZE1'          => sanitize($this->style['fontsize1'], ENT),
            'FONTSIZE2'          => sanitize($this->style['fontsize2'], ENT),
            'FONTSIZE3'          => sanitize($this->style['fontsize3'], ENT),
            'FONTCOLOR1'         => sanitize($this->style['fontcolor1'], ENT),
            'FONTCOLOR2'         => sanitize($this->style['fontcolor2'], ENT),
            'FONTCOLOR3'         => sanitize($this->style['fontcolor3'], ENT),
            'FONTCOLOR_NEG'      => sanitize($this->style['fontcolor_neg'], ENT),
            'FONTCOLOR_POS'      => sanitize($this->style['fontcolor_pos'], ENT),
            'TABLE_BORDER_WIDTH' => sanitize($this->style['table_border_width'], ENT),
            'TABLE_BORDER_COLOR' => sanitize($this->style['table_border_color'], ENT),
            'TABLE_BORDER_STYLE' => sanitize($this->style['table_border_style'], ENT),
            'INPUT_COLOR'        => sanitize($this->style['input_color'], ENT),
            'INPUT_BORDER_WIDTH' => sanitize($this->style['input_border_width'], ENT),
            'INPUT_BORDER_COLOR' => sanitize($this->style['input_border_color'], ENT),
            'INPUT_BORDER_STYLE' => sanitize($this->style['input_border_style'], ENT),
            'DATE_NOTIME_LONG'   => sanitize($this->style['date_notime_long'], ENT),
            'DATE_NOTIME_SHORT'  => sanitize($this->style['date_notime_short'], ENT),
            'DATE_TIME'          => sanitize($this->style['date_time'], ENT),
            'STYLE_LOGO_PATH'    => sanitize($this->style['logo_path'], ENT),
            
            // Language
            'L_STYLE_SETTINGS'         => $user->lang['style_settings'],
            'L_STYLE_NAME'             => $user->lang['style_name'],
            'L_TEMPLATE'               => $user->lang['template'],
            'L_ELEMENT'                => $user->lang['element'],
            'L_VALUE'                  => $user->lang['value'],
            'L_BACKGROUND_COLOR'       => $user->lang['background_color'],
            'L_FONTFACE1'              => $user->lang['fontface1'],
            'L_FONTFACE1_NOTE'         => $user->lang['fontface1_note'],
            'L_FONTFACE2'              => $user->lang['fontface2'],
            'L_FONTFACE2_NOTE'         => $user->lang['fontface2_note'],
            'L_FONTFACE3'              => $user->lang['fontface3'],
            'L_FONTFACE3_NOTE'         => $user->lang['fontface3_note'],
            'L_FONTSIZE1'              => $user->lang['fontsize1'],
            'L_FONTSIZE1_NOTE'         => $user->lang['fontsize1_note'],
            'L_FONTSIZE2'              => $user->lang['fontsize2'],
            'L_FONTSIZE2_NOTE'         => $user->lang['fontsize2_note'],
            'L_FONTSIZE3'              => $user->lang['fontsize3'],
            'L_FONTSIZE3_NOTE'         => $user->lang['fontsize3_note'],
            'L_FONTCOLOR1'             => $user->lang['fontcolor1'],
            'L_FONTCOLOR1_NOTE'        => $user->lang['fontcolor1_note'],
            'L_FONTCOLOR2'             => $user->lang['fontcolor2'],
            'L_FONTCOLOR2_NOTE'        => $user->lang['fontcolor2_note'],
            'L_FONTCOLOR3'             => $user->lang['fontcolor3'],
            'L_FONTCOLOR3_NOTE'        => $user->lang['fontcolor3_note'],
            'L_FONTCOLOR_NEG'          => $user->lang['fontcolor_neg'],
            'L_FONTCOLOR_NEG_NOTE'     => $user->lang['fontcolor_neg_note'],
            'L_FONTCOLOR_POS'          => $user->lang['fontcolor_pos'],
            'L_FONTCOLOR_POS_NOTE'     => $user->lang['fontcolor_pos_note'],
            'L_BODY_LINK'              => $user->lang['body_link'],
            'L_BODY_LINK_STYLE'        => $user->lang['body_link_style'],
            'L_BODY_HLINK'             => $user->lang['body_hlink'],
            'L_BODY_HLINK_STYLE'       => $user->lang['body_hlink_style'],
            'L_HEADER_LINK'            => $user->lang['header_link'],
            'L_HEADER_LINK_STYLE'      => $user->lang['header_link_style'],
            'L_HEADER_HLINK'           => $user->lang['header_hlink'],
            'L_HEADER_HLINK_STYLE'     => $user->lang['header_hlink_style'],
            'L_TR_COLOR1'              => $user->lang['tr_color1'],
            'L_TR_COLOR2'              => $user->lang['tr_color2'],
            'L_TH_COLOR1'              => $user->lang['th_color1'],
            'L_TABLE_BORDER_WIDTH'     => $user->lang['table_border_width'],
            'L_TABLE_BORDER_COLOR'     => $user->lang['table_border_color'],
            'L_TABLE_BORDER_STYLE'     => $user->lang['table_border_style'],
            'L_INPUT_COLOR'            => $user->lang['input_color'],
            'L_INPUT_BORDER_WIDTH'     => $user->lang['input_border_width'],
            'L_INPUT_BORDER_COLOR'     => $user->lang['input_border_color'],
            'L_INPUT_BORDER_STYLE'     => $user->lang['input_border_style'],
            'L_STYLE_CONFIGURATION'    => $user->lang['style_configuration'],
            'L_STYLE_DATE_NOTE'        => $user->lang['style_date_note'],
            'L_ATTENDEES_COLUMNS'      => $user->lang['attendees_columns'],
            'L_ATTENDEES_COLUMNS_NOTE' => $user->lang['attendees_columns_note'],
            'L_DATE_NOTIME_LONG'       => $user->lang['date_notime_long'],
            'L_DATE_NOTIME_SHORT'      => $user->lang['date_notime_short'],
            'L_DATE_TIME'              => $user->lang['date_time'],
            'L_LOGO_PATH'              => $user->lang['logo_path'],
            'L_ADD_STYLE'              => $user->lang['add_style'],
            'L_RESET'                  => $user->lang['reset'],
            'L_UPDATE_STYLE'           => $user->lang['update_style'],
            'L_DELETE_STYLE'           => $user->lang['delete_style'],
            
            // Buttons
            'S_ADD' => ( !$this->url_id ) ? true : false
        ));
        
        $eqdkp->set_vars(array(
            'page_title'    => page_title($user->lang['styles_title']),
            'template_file' => 'admin/addstyle.html',
            'display'       => true
        ));
    }
}

$manage_styles = new Manage_Styles;
$manage_styles->process();