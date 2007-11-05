<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        upgrade-1.1.0.php
 * Began:       Sun Nov  4 2007
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     upgrade
 * @version     $Rev$
 */
 
if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

$VERSION = '1.1.0';

if ( class_exists('Upgrade') && Upgrade::should_run($VERSION) )
{
    Upgrade::set_version($VERSION);
    Upgrade::progress("Completed upgrade to $VERSION.");
}

$queries = array(
    "CREATE TABLE IF NOT EXISTS __member_flags (
       flag_id smallint(3) unsigned NOT NULL UNIQUE,
       flag_name varchar(50) NOT NULL));",
    "INSERT INTO __member_flags (flag_id, flag_name) VALUES ('0', '');",
    "INSERT INTO __member_flags (flag_id, flag_name) VALUES ('1', 'Member');",
    "ALTER TABLE __members ADD member_flag smallint(3) NOT NULL default '0' AFTER member_class;",
    "INSERT INTO __config (config_name, config_value) VALUES ('parsetags', '');",
    "INSERT INTO __styles (style_id, style_name, template_path, body_background, body_link, body_link_style, body_hlink, body_hlink_style, header_link, header_link_style, header_hlink, header_hlink_style, tr_color1, tr_color2, th_color1, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, fontcolor_neg, fontcolor_pos, table_border_width, table_border_color, table_border_style, input_color, input_border_width, input_border_color, input_border_style) VALUES (10, 'dkpUA', 'default', '253546', 'C6C6C6', 'underline', '576695', 'underline', 'C6C6C6', 'none', 'C6C6C6', 'underline', '39495A', '283846', '1F2F3D', 'Verdana', 'Verdana', 'Verdana', 10, 11, 12, 'C6C6C6', 'C6C6C6', '000000', 'FF0000', '00C000', 1, '60707E', 'solid', 'FFFFFF', 1, '60707E', 'solid');",
    "INSERT INTO __styles (style_id, style_name, template_path, body_background, body_link, body_link_style, body_hlink, body_hlink_style, header_link, header_link_style, header_hlink, header_hlink_style, tr_color1, tr_color2, th_color1, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, fontcolor_neg, fontcolor_pos, table_border_width, table_border_color, table_border_style, input_color, input_border_width, input_border_color, input_border_style) VALUES (11, 'subSilver', 'default', 'FFFFFF', '006699', 'underline', 'DD6900', 'underline', 'FFA34F', 'none', 'FFA34F', 'underline', 'DEE3E7', 'EFEFEF', '1073A5', 'Verdana, Arial', 'Verdana, Arial', 'Verdana, Arial', 10, 11, 12, '000000', '000000', '000000', 'F80000', '008800', 1, '006699', 'solid', 'FFFFFF', 1, '000000', 'solid');",
    "INSERT INTO __styles (style_id, style_name, template_path, body_background, body_link, body_link_style, body_hlink, body_hlink_style, header_link, header_link_style, header_hlink, header_hlink_style, tr_color1, tr_color2, th_color1, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, fontcolor_neg, fontcolor_pos, table_border_width, table_border_color, table_border_style, input_color, input_border_width, input_border_color, input_border_style) VALUES (12, 'EQdkp VB2', 'default', 'FFFFFF', '000000', 'underline', 'FF4400', 'underline', 'FFF788', 'none', 'FFF788', 'underline', 'F1F1F1', 'DFDFDF', '8080A6', 'Verdana, Arial', 'Verdana, Arial', 'Verdana, Arial', 10, 11, 12, '000000', '000000', '000000', 'F80000', '008800', 1, '555576', 'solid', 'FFFFFF', 1, '000000', 'solid');",
    "INSERT INTO __styles (style_id, style_name, template_path, body_background, body_link, body_link_style, body_hlink, body_hlink_style, header_link, header_link_style, header_hlink, header_hlink_style, tr_color1, tr_color2, th_color1, fontface1, fontface2, fontface3, fontsize1, fontsize2, fontsize3, fontcolor1, fontcolor2, fontcolor3, fontcolor_neg, fontcolor_pos, table_border_width, table_border_color, table_border_style, input_color, input_border_width, input_border_color, input_border_style) VALUES (13, 'EQCPS', 'default', '7B7984', '151F41', 'underline', '800000', 'underline', 'FFFFFF', 'none', 'FFFFFF', 'none', 'CECBCE', 'BDBABD', '424952', 'Verdana, Arial', 'Verdana, Arial', 'Verdana, Arial', 10, 11, 12, '000000', '000000', '000000', '800000', '008000', 1, '000000', 'solid', 'C0C0C0', 1, '000000', 'solid');",
    "INSERT INTO __style_config (style_id, attendees_columns, logo_path) VALUES (10, '8', 'dkpua_logo.gif');",
    "INSERT INTO __style_config (style_id, attendees_columns, logo_path) VALUES (11, '8', 'subsilver_logo.gif');",
    "INSERT INTO __style_config (style_id, attendees_columns, logo_path) VALUES (12, '8', 'logo.gif');",
    "INSERT INTO __style_config (style_id, attendees_columns, logo_path) VALUES (13, '8', 'logo.gif');"
);