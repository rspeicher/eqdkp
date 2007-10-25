<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * lang_admin.php
 * Began: Fri January 3 2003
 * 
 * $Id: lang_admin.php 47 2007-10-15 01:48:40 dazza $
 * 
 ******************************/

if ( !defined('EQDKP_INC') )
{
     die('Do not access this file directly.');
}

// Initialize the language array if it isn't already
if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// %1\$<type> prevents a possible error in strings caused
//      by another language re-ordering the variables
// $s is a string, $d is an integer, $f is a float

$lang = array_merge($lang, array(
    // Titles
    'addadj_title'         => 'Add a Group Adjustment',
    'addevent_title'       => 'Add an Event',
    'addiadj_title'        => 'Add an Individual Adjustment',
    'additem_title'        => 'Add an Item Purchase',
    'addmember_title'      => 'Add a Guild Member',
    'addnews_title'        => 'Add a News Entry',
    'addraid_title'        => 'Add a Raid',
    'addturnin_title'      => "Add a Turn-in - Step %1\$d",
    'admin_index_title'    => 'EQdkp Administration',
    'config_title'         => 'Script Configuration',
    'manage_members_title' => 'Manage Guild Members',
    'manage_users_title'   => 'User Accounts and Permissions',
    'parselog_title'       => 'Parse a Log File',
    'plugins_title'        => 'Manage Plugins',
    'styles_title'         => 'Manage Styles',
    'viewlogs_title'       => 'Log Viewer',
    
    // Page Foot Counts
    'listusers_footcount'             => "... found %1\$d user(s) / %2\$d per page",
    'manage_members_footcount'        => "... found %1\$d member(s)",
    'online_footcount'                => "... %1\$d users are online",
    'viewlogs_footcount'              => "... found %1\$d log(s) / %2\$d per page",
    
    // Submit Buttons
    'add_adjustment'             => 'Add Adjustment',
    'add_account'                => 'Add Account',
    'add_event'                  => 'Add Event',
    'add_item'                   => 'Add Item',
    'add_member'                 => 'Add Member',
    'add_news'                   => 'Add News',
    'add_raid'                   => 'Add Raid',
    'add_style'                  => 'Add Style',
    'add_turnin'                 => 'Add Turn-in',
    'delete_adjustment'          => 'Delete Adjustment',
    'delete_event'               => 'Delete Event',
    'delete_item'                => 'Delete Item',
    'delete_member'              => 'Delete Member',
    'delete_news'                => 'Delete News',
    'delete_raid'                => 'Delete Raid',
    'delete_selected_members'    => 'Delete Selected Member(s)',
    'delete_style'               => 'Delete Style',
    'mass_delete'                => 'Mass Delete',
    'mass_update'                => 'Mass Update',
    'parse_log'                  => 'Parse Log',
    'search_existing'            => 'Search Existing',
    'select'                     => 'Select',
    'transfer_history'           => 'Transfer History',
    'update_adjustment'          => 'Update Adjustment',
    'update_event'               => 'Update Event',
    'update_item'                => 'Update Item',
    'update_member'              => 'Update Member',
    'update_news'                => 'Update News',
    'update_raid'                => 'Update Raid',
    'update_style'               => 'Update Style',
    
    // Misc
    'account_enabled'            => 'Account Enabled',
    'adjustment_value'           => 'Adjustment Value',
    'adjustment_value_note'      => 'May be negative',
    'code'                       => 'Code',
    'contact'                    => 'Contact',
    'create'                     => 'Create',
    'found_members'              => "Parsed %1\$d lines, found %2\$d members",
    'headline'                   => 'Headline',
    'hide'                       => 'Hide?',
    'install'                    => 'Install',
    'item_search'                => 'Item Search',
    'list_prefix'                => 'List Prefix',
    'list_suffix'                => 'List Suffix',
    'logs'                       => 'Logs',
    'log_find_all'               => 'Find all (including anonymous)',
    'manage_members'             => 'Manage Members',
    'manage_plugins'             => 'Manage Plugins',
    'manage_users'               => 'Manage Users',
    'mass_update_note'           => 'If you wish to apply changes to all of the items selected above, use these controls to change their properties and click on "Mass Update".' 
                                    . "\n" . 'To delete the selected accounts, just click on "Mass Delete".',
    'members'                    => 'Members',
    'member_rank'                => 'Member Rank',
    'message_body'               => 'Message Body',
    'results'                    => "%1\$d Results (\"%2\$s\")",
    'search'                     => 'Search',
    'search_members'             => 'Search Members',
    'should_be'                  => 'Should be',
    'styles'                     => 'Styles',
    'title'                      => 'Title',
    'uninstall'                  => 'Uninstall',
    'update_date_to'             => "Update date to<br />%1\$s?",
    'version'                    => 'Version',
    'x_members_s'                => "%1\$d member",
    'x_members_p'                => "%1\$d members",
    
    // Permission Messages
    'noauth_a_event_add'    => 'You do not have permission to add events.',
    'noauth_a_event_upd'    => 'You do not have permission to update events.',
    'noauth_a_event_del'    => 'You do not have permission to delete events.',
    'noauth_a_groupadj_add' => 'You do not have permission to add group adjustments.',
    'noauth_a_groupadj_upd' => 'You do not have permission to update group adjustments.',
    'noauth_a_groupadj_del' => 'You do not have permission to delete group adjustments.',
    'noauth_a_indivadj_add' => 'You do not have permission to add individual adjustments.',
    'noauth_a_indivadj_upd' => 'You do not have permission to update individual adjustments.',
    'noauth_a_indivadj_del' => 'You do not have permission to delete individual adjustments.',
    'noauth_a_item_add'     => 'You do not have permission to add items.',
    'noauth_a_item_upd'     => 'You do not have permission to update items.',
    'noauth_a_item_del'     => 'You do not have permission to delete items.',
    'noauth_a_news_add'     => 'You do not have permission to add news entries.',
    'noauth_a_news_upd'     => 'You do not have permission to update news entries.',
    'noauth_a_news_del'     => 'You do not have permission to delete news entries.',
    'noauth_a_raid_add'     => 'You do not have permission to add raids.',
    'noauth_a_raid_upd'     => 'You do not have permission to update raids.',
    'noauth_a_raid_del'     => 'You do not have permission to delete raids.',
    'noauth_a_turnin_add'   => 'You do not have permission to add turn-ins.',
    'noauth_a_config_man'   => 'You do not have permission to manage EQdkp configuration settings.',
    'noauth_a_members_man'  => 'You do not have permission to manage guild members.',
    'noauth_a_plugins_man'  => 'You do not have permission to manage EQdkp plugins.',
    'noauth_a_styles_man'   => 'You do not have permission to manage EQdkp styles.',
    'noauth_a_users_man'    => 'You do not have permission to manage user account settings.',
    'noauth_a_logs_view'    => 'You do not have permission to view EQdkp logs.',
    
    // Submission Success Messages
    'admin_add_adj_success'               => "A %1\$s adjustment of %2\$.2f has been added to the database for your guild.",
    'admin_add_admin_success'             => "An e-mail has been sent to %1\$s with their administrative information.",
    'admin_add_event_success'             => "A value preset of %1\$s for a raid on %2\$s has been added to the database for your guild.",
    'admin_add_iadj_success'              => "An individual %1\$s adjustment of %2\$.2f for %3\$s has been added to the database for your guild.",
    'admin_add_item_success'              => "An item purchase entry for %1\$s, purchased by %2\$s for %3\$.2f has been added to the database for your guild.",
    'admin_add_member_success'            => "%1\$s has been added as a member of your guild.",
    'admin_add_news_success'              => 'The news entry has been added to the database for your guild.',
    'admin_add_raid_success'              => "The %1\$s raid on %2\$s has been added to the database for your guild.",
    'admin_add_style_success'             => 'The new style has been added successfully.',
    'admin_add_turnin_success'            => "%1\$s has been transferred from %2\$s to %3\$s.",
    'admin_delete_adj_success'            => "The %1\$s adjustment of %2\$.2f has been deleted from the database for your guild.",
    'admin_delete_admins_success'         => "The selected administrators have been deleted.",
    'admin_delete_event_success'          => "The value preset of %1\$s for a raid on %2\$s has been deleted from the database for your guild.",
    'admin_delete_iadj_success'           => "The individual %1\$s adjustment of %2\$.2f for %3\$s has been deleted from the database for your guild.",
    'admin_delete_item_success'           => "The item purchase entry for %1\$s, purchased by %2\$s for %3\$.2f has been deleted from the database for your guild.",
    'admin_delete_members_success'        => "%1\$s, including all of his/her history, has been deleted from the database for your guild.",
    'admin_delete_news_success'           => 'The news entry has been deleted from the database for your guild.',
    'admin_delete_raid_success'           => 'The raid and any items associated with it have been deleted from the database for your guild.',
    'admin_delete_style_success'          => 'The style has been deleted successfully.',
    'admin_delete_user_success'           => "The account with a username of %1\$s has been deleted.",
    'admin_set_perms_success'             => "All administrative permissions have been updated.",
    'admin_transfer_history_success'      => "All of %1\$s's history has been transferred to %2\$s and %1\$s has been deleted from the database for your guild.",
    'admin_update_account_success'        => "Your account settings have been updated in the database.",
    'admin_update_adj_success'            => "The %1\$s adjustment of %2\$.2f has been updated in the database for your guild.",
    'admin_update_event_success'          => "The value preset of %1\$s for a raid on %2\$s has been updated in the database for your guild.",
    'admin_update_iadj_success'           => "The individual %1\$s adjustment of %2\$.2f for %3\$s has been updated in the database for your guild.",
    'admin_update_item_success'           => "The item purchase entry for %1\$s, purchased by %2\$s for %3\$.2f has been updated in the database for your guild.",
    'admin_update_member_success'         => "Membership settings for %1\$s have been updated.",
    'admin_update_news_success'           => 'The news entry has been updated in the database for your guild.',
    'admin_update_raid_success'           => "The %1\$s raid on %2\$s has been updated in the database for your guild.",
    'admin_update_style_success'          => 'The style has been updated successfully.',
    
    'admin_raid_success_hideinactive'     => 'Updating active/inactive player status...',
    
    // Delete Confirmation Texts
    'confirm_delete_adj'     => 'Are you sure you want to delete this group adjustment?',
    'confirm_delete_admins'  => 'Are you sure you want to delete the selected administrator(s)?',
    'confirm_delete_event'   => 'Are you sure you want to delete this event?',
    'confirm_delete_iadj'    => 'Are you sure you want to delete this individual adjustment?',
    'confirm_delete_item'    => 'Are you sure you want to delete this item?',
    'confirm_delete_members' => 'Are you sure you want to delete the following members?',
    'confirm_delete_news'    => 'Are you sure you want to delete this news entry?',
    'confirm_delete_raid'    => 'Are you sure you want to delete this raid?',
    'confirm_delete_style'   => 'Are you sure you want to delete this style?',
    'confirm_delete_users'   => 'Are you sure you want to delete the following user accounts?',
    
    // Log Actions
    'action_event_added'      => 'Event Added',
    'action_event_deleted'    => 'Event Deleted',
    'action_event_updated'    => 'Event Updated',
    'action_groupadj_added'   => 'Group Adjustment Added',
    'action_groupadj_deleted' => 'Group Adjustment Deleted',
    'action_groupadj_updated' => 'Group Adjustment Updated',
    'action_history_transfer' => 'Member History Transfer',
    'action_indivadj_added'   => 'Individual Adjustment Added',
    'action_indivadj_deleted' => 'Individual Adjustment Deleted',
    'action_indivadj_updated' => 'Individual Adjustment Updated',
    'action_item_added'       => 'Item Added',
    'action_item_deleted'     => 'Item Deleted',
    'action_item_updated'     => 'Item Updated',
    'action_member_added'     => 'Member Added',
    'action_member_deleted'   => 'Member Deleted',
    'action_member_updated'   => 'Member Updated',
    'action_news_added'       => 'News Entry Added',
    'action_news_deleted'     => 'News Entry Deleted',
    'action_news_updated'     => 'News Entry Updated',
    'action_raid_added'       => 'Raid Added',
    'action_raid_deleted'     => 'Raid Deleted',
    'action_raid_updated'     => 'Raid Updated',
    'action_turnin_added'     => 'Turn-in Added',
    
    // Before/After
    'adjustment_after'  => 'Adjustment After',
    'adjustment_before' => 'Adjustment Before',
    'attendees_after'   => 'Attendees After',
    'attendees_before'  => 'Attendees Before',
    'buyers_after'      => 'Buyer After',
    'buyers_before'     => 'Buyer Before',
    'class_after'       => 'Class After',
    'class_before'      => 'Class Before',
    'earned_after'      => 'Earned After',
    'earned_before'     => 'Earned Before',
    'event_after'       => 'Event After',
    'event_before'      => 'Event Before',
    'headline_after'    => 'Headline After',
    'headline_before'   => 'Headline Before',
    'level_after'       => 'Level After',
    'level_before'      => 'Level Before',
    'members_after'     => 'Members After',
    'members_before'    => 'Members Before',
    'message_after'     => 'Message After',
    'message_before'    => 'Message Before',
    'name_after'        => 'Name After',
    'name_before'       => 'Name Before',
    'note_after'        => 'Note After',
    'note_before'       => 'Note Before',
    'race_after'        => 'Race After',
    'race_before'       => 'Race Before',
    'raid_id_after'     => 'Raid ID After',
    'raid_id_before'    => 'Raid ID Before',
    'reason_after'      => 'Reason After',
    'reason_before'     => 'Reason Before',
    'spent_after'       => 'Spent After',
    'spent_before'      => 'Spent Before',
    'value_after'       => 'Value After',
    'value_before'      => 'Value Before',
    
    // Configuration
    'general_settings'          => 'General Settings',
    'guildtag'                  => 'Guildtag / Alliance Name',
    'guildtag_note'             => 'Used in the title of nearly every page',
    'parsetags'                 => 'Guildtags to Parse',
    'parsetags_note'            => 'Those listed will be available as options when parsing raid logs.',
    'domain_name'               => 'Domain Name',
    'server_port'               => 'Server Port',
    'server_port_note'          => 'Your webserver\'s port. Usually 80',
    'script_path'               => 'Script Path',
    'script_path_note'          => 'Path where EQdkp is located, relative to the domain name',
    'site_name'                 => 'Site Name',
    'site_description'          => 'Site Description',
    'point_name'                => 'Point Name',
    'point_name_note'           => 'Ex: DKP, RP, etc.',
    'enable_account_activation' => 'Enable Account Activation',
    'none'                      => 'None',
    'admin'                     => 'Admin',
    'default_language'          => 'Default Language',
    'default_locale'            => 'Default Locale (character set only, does not affect language)',
    'default_game'              => 'Default Game',
    'default_game_warn'         => 'Changing the default game may void other changes in this session.',
    'default_style'             => 'Default Style',
    'default_page'              => 'Default Index Page',
    'hide_inactive'             => 'Hide Inactive Members',
    'hide_inactive_note'        => 'Hide members that haven\'t attended a raid in [inactive period] days?',
    'inactive_period'           => 'Inactive Period',
    'inactive_period_note'      => 'Number of days a member can miss a raid and still be considered active',
    'inactive_point_adj'        => 'Inactive Point Adjustment',
    'inactive_point_adj_note'   => 'Point adjustment to make on a member when they become inactive.',
    'active_point_adj'          => 'Active Point Adjustment',
    'active_point_adj_note'     => 'Point Adjustment to make on a member when they become active.',
    'enable_gzip'               => 'Enable Gzip Compression',
    'show_item_stats'           => 'Show Item Stats',
    'show_item_stats_note'      => 'Tries to grab item stats from the Internet.  May impact speed of certain pages',
    'default_permissions'       => 'Default Permissions',
    'default_permissions_note'  => 'These are the permissions for users who are not logged in and are given to new users when they register. Items in <b>bold</b> are administrative permissions, '
                                   . 'it is highly recommended to not set any of those items as the default. Items in <i>italics</i> are used exclusively by plugins.  You can later change an individual user\'s permissions by going to Manage Users.',
    'plugins'                   => 'Plugins',
    'cookie_settings'           => 'Cookie Settings',
    'cookie_domain'             => 'Cookie Domain',
    'cookie_name'               => 'Cookie Name',
    'cookie_path'               => 'Cookie Path',
    'session_length'            => 'Session Length (seconds)',
    'email_settings'            => 'E-Mail Settings',
    'admin_email'               => 'Administrator E-Mail Address',
    'backup_options'            => 'Backup Options',
    
    // Admin Index
    'anonymous'          => 'Anonymous',
    'database_size'      => 'Database Size',
    'eqdkp_started'      => 'EQdkp Started',
    'ip_address'         => 'IP Address',
    'items_per_day'      => 'Items per Day',
    'last_update'        => 'Last Update',
    'location'           => 'Location',
    'new_version_notice' => "EQdkp version %1\$s is <a href=\"http://sourceforge.net/project/showfiles.php?group_id=69529\" target=\"_blank\">available for download</a>.",
    'number_of_items'    => 'Number of Items',
    'number_of_logs'     => 'Number of Log Entries',
    'number_of_members'  => 'Number of Members (Active / Inactive)',
    'number_of_raids'    => 'Number of Raids',
    'raids_per_day'      => 'Raids per Day',
    'statistics'         => 'Statistics',
    'totals'             => 'Totals',
    'version_update'     => 'Version Update',
    'who_online'         => 'Who\'s Online',
    
    // Style Management
    'style_settings'     => 'Style Settings',
    'style_name'         => 'Style Name',
    'template'           => 'Template',
    'element'            => 'Element',
    'background_color'   => 'Background Color',
    'fontface1'          => 'Font Face 1',
    'fontface1_note'     => 'Default font face',
    'fontface2'          => 'Font Face 2',
    'fontface2_note'     => 'Input field font face',
    'fontface3'          => 'Font Face 3',
    'fontface3_note'     => 'Not currently used',
    'fontsize1'          => 'Font Size 1',
    'fontsize1_note'     => 'Small',
    'fontsize2'          => 'Font Size 2',
    'fontsize2_note'     => 'Medium',
    'fontsize3'          => 'Font Size 3',
    'fontsize3_note'     => 'Large',
    'fontcolor1'         => 'Font Color 1',
    'fontcolor1_note'    => 'Default color',
    'fontcolor2'         => 'Font Color 2',
    'fontcolor2_note'    => 'Color used outside tables (menus, titles, copyright)',
    'fontcolor3'         => 'Font Color 3',
    'fontcolor3_note'    => 'Input field font color',
    'fontcolor_neg'      => 'Negative Font Color',
    'fontcolor_neg_note' => 'Color for negative/bad numbers',
    'fontcolor_pos'      => 'Positive Font Color',
    'fontcolor_pos_note' => 'Color for positive/good numbers',
    'body_link'          => 'Link Color',
    'body_link_style'    => 'Link Style',
    'body_hlink'         => 'Hover Link Color',
    'body_hlink_style'   => 'Hover Link Style',
    'header_link'        => 'Header Link',
    'header_link_style'  => 'Header Link Style',
    'header_hlink'       => 'Hover Header Link',
    'header_hlink_style' => 'Hover Header Link Style',
    'tr_color1'          => 'Table Row Color 1',
    'tr_color2'          => 'Table Row Color 2',
    'th_color1'          => 'Table Header Color',
    'table_border_width' => 'Table Border Width',
    'table_border_color' => 'Table Border Color',
    'table_border_style' => 'Table Border Style',
    'input_color'        => 'Input Field Background Color',
    'input_border_width' => 'Input Field Border Width',
    'input_border_color' => 'Input Field Border Color',
    'input_border_style' => 'Input Field Border Style',
    'style_configuration'    => 'Style Configuration',
    'style_date_note'    => 'For date/time fields, the syntax used is identical to the PHP <a href="http://www.php.net/manual/en/function.date.php" target="_blank">date()</a> function.',
    'attendees_columns'  => 'Attendees Columns',
    'attendees_columns_note' => 'Number of columns to use for attendees when viewing a raid',
    'date_notime_long'   => 'Date without Time (long)',
    'date_notime_short'  => 'Date without Time (short)',
    'date_time'          => 'Date with Time',
    'logo_path'          => 'Logo Filename',
    
    // Errors
    'error_invalid_adjustment' => 'A valid adjustment was not provided.',
    'error_invalid_plugin'     => 'A valid plugin was not provided.',
    'error_invalid_style'      => 'A valid style was not provided.',
    
    // Verbose log entry lines
    'new_actions'           => 'Newest Admin Actions',
    'vlog_event_added'      => "%1\$s added the event '%2\$s' worth %3\$.2f points.",
    'vlog_event_updated'    => "%1\$s updated the event '%2\$s'.",
    'vlog_event_deleted'    => "%1\$s deleted the event '%2\$s'.",
    'vlog_groupadj_added'   => "%1\$s added a group adjustment of %2\$.2f points.",
    'vlog_groupadj_updated' => "%1\$s updated a group adjustment of %2\$.2f points.",
    'vlog_groupadj_deleted' => "%1\$s deleted a group adjustment of %2\$.2f points.",
    'vlog_history_transfer' => "%1\$s transferred %2\$s's history to %3\$s.",
    'vlog_indivadj_added'   => "%1\$s added an individual adjustment of %2\$.2f to %3\$d member(s).",
    'vlog_indivadj_updated' => "%1\$s updated an individual adjustment of %2\$.2f to %3\$s.",
    'vlog_indivadj_deleted' => "%1\$s deleted an individual adjustment of %2\$.2f to %3\$s.",
    'vlog_item_added'       => "%1\$s added the item '%2\$s' charged to %3\$d member(s) for %4\$.2f points.",
    'vlog_item_updated'     => "%1\$s updated the item '%2\$s' charged to %3\$d member(s).",
    'vlog_item_deleted'     => "%1\$s deleted the item '%2\$s' charged to %3\$d member(s).",
    'vlog_member_added'     => "%1\$s added the member %2\$s.",
    'vlog_member_updated'   => "%1\$s updated the member %2\$s.",
    'vlog_member_deleted'   => "%1\$s deleted the member %2\$s.",
    'vlog_news_added'       => "%1\$s added the news entry '%2\$s'.",
    'vlog_news_updated'     => "%1\$s updated the news entry '%2\$s'.",
    'vlog_news_deleted'     => "%1\$s deleted the news entry '%2\$s'.",
    'vlog_raid_added'       => "%1\$s added a raid on '%2\$s'.",
    'vlog_raid_updated'     => "%1\$s updated a raid on '%2\$s'.",
    'vlog_raid_deleted'     => "%1\$s deleted a raid on '%2\$s'.",
    'vlog_turnin_added'     => "%1\$s added a turn-in from %2\$s to %3\$s for '%4\$s'.",
    
    // Location messages
    'adding_groupadj'     => 'Adding a Group Adjustment',
    'adding_indivadj'     => 'Adding an Individual Adjustment',
    'adding_item'         => 'Adding an Item',
    'adding_news'         => 'Adding a News Entry',
    'adding_raid'         => 'Adding a Raid',
    'adding_turnin'       => 'Adding a Turn-in',
    'editing_groupadj'    => 'Editing Group Adjustment',
    'editing_indivadj'    => 'Editing Individual Adjustment',
    'editing_item'        => 'Editing Item',
    'editing_news'        => 'Editing News Entry',
    'editing_raid'        => 'Editing Raid',
    'listing_events'      => 'Listing Events',
    'listing_groupadj'    => 'Listing Group Adjustments',
    'listing_indivadj'    => 'Listing Individual Adjustments',
    'listing_itemhist'    => 'Listing Item History',
    'listing_itemvals'    => 'Listing Item Values',
    'listing_members'     => 'Listing Members',
    'listing_raids'       => 'Listing Raids',
    'managing_config'     => 'Managing EQdkp Configuration',
    'managing_members'    => 'Managing Guild Members',
    'managing_plugins'    => 'Managing Plugins',
    'managing_styles'     => 'Managing Styles',
    'managing_users'      => 'Managing User Accounts',
    'parsing_log'         => 'Parsing a Log',
    'viewing_admin_index' => 'Viewing Admin Index',
    'viewing_event'       => 'Viewing Event',
    'viewing_item'        => 'Viewing Item',
    'viewing_logs'        => 'Viewing Logs',
    'viewing_member'      => 'Viewing Member',
    'viewing_mysql_info'  => 'Viewing MySQL Information',
    'viewing_news'        => 'Viewing News',
    'viewing_raid'        => 'Viewing Raid',
    'viewing_stats'       => 'Viewing Stats',
    'viewing_summary'     => 'Viewing Summary',
    
    // Help lines
    'b_help' => 'Bold text: [b]text[/b] (alt+b)',
    'i_help' => 'Italic text: [i]text[/i] (alt+i)',
    'u_help' => 'Underlined text: [u]text[/u] (alt+u)',
    'q_help' => 'Quote text: [quote]text[/quote] (alt+q)',
    'c_help' => 'Center text: [center]text[/center] (alt+c)',
    'p_help' => 'Insert image: [img]http://image_url[/img] (alt+p)',
    'w_help' => 'Insert URL: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)',
    
    // Manage Members Menu (yes, MMM)
    'add_member'           => 'Add New Member',
    'list_edit_del_member' => 'List, Edit or Delete Members',
    'edit_ranks'           => 'Edit Membership Ranks',
    'transfer_history'     => 'Transfer Member History',
    
    // MySQL info
    'mysql'        => 'MySQL',
    'mysql_info'   => 'MySQL Info',
    'eqdkp_tables' => 'EQdkp Tables',
    'table_name'   => 'Table Name',
    'rows'         => 'Rows',
    'table_size'   => 'Table Size',
    'index_size'   => 'Index Size',
    'num_tables'   => "%d tables",
    
    //Backup
    'backup'            => 'Backup',
    'backup_title'      => 'Create a database backup',
    'create_table'      => 'Add \'CREATE TABLE\' statements?',
    'skip_nonessential' => 'Skip non essential data?<br />Will not produce insert rows for eqdkp_sessions.',
    'gzip_content'      => 'GZIP Content?<br />Will produce a smaller file if GZIP is enabled.',
    'backup_database'   => 'Backup Database',
    
    // Form validation
    'fv_turnin_noitems' => "%1\$s has no purchased items to transfer.",
));