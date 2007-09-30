<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * lang_main.php
 * begin: Wed December 18 2002
 *
 * $Id: lang_main.php 46 2007-06-19 07:29:11Z tsigo $
 *
 ******************************/

if ( !defined('EQDKP_INC') )
{
     die('Do not access this file directly.');
}

// %1\$<type> prevents a possible error in strings caused
//      by another language re-ordering the variables
// $s is a string, $d is an integer, $f is a float

$lang['ENCODING'] = 'iso-8859-1';
$lang['XML_LANG'] = 'en';

// Titles
$lang['admin_title_prefix']   = "%1\$s %2\$s Admin";
$lang['listadj_title']        = 'Group Adjustment Listing';
$lang['listevents_title']     = 'Event Values';
$lang['listiadj_title']       = 'Individual Adjustment Listing';
$lang['listitems_title']      = 'Item Values';
$lang['listnews_title']       = 'News Entries';
$lang['listmembers_title']    = 'Member Standings';
$lang['listpurchased_title']  = 'Item History';
$lang['listraids_title']      = 'Raids Listing';
$lang['login_title']          = 'Login';
$lang['message_title']        = 'EQdkp: Message';
$lang['register_title']       = 'Register';
$lang['settings_title']       = 'Account Settings';
$lang['stats_title']          = "%1\$s Stats";
$lang['summary_title']        = 'News Summary';
$lang['title_prefix']         = "%1\$s %2\$s";
$lang['viewevent_title']      = "Viewing Recorded Raid History for %1\$s";
$lang['viewitem_title']       = "Viewing Purchase History for %1\$s";
$lang['viewmember_title']     = "History for %1\$s";
$lang['viewraid_title']       = 'Raid Summary';

// Main Menu
$lang['menu_admin_panel'] = 'Administration Panel';
$lang['menu_events'] = 'Events';
$lang['menu_itemhist'] = 'Item History';
$lang['menu_itemval'] = 'Item Values';
$lang['menu_news'] = 'News';
$lang['menu_raids'] = 'Raids';
$lang['menu_register'] = 'Register';
$lang['menu_settings'] = 'Settings';
$lang['menu_standings'] = 'Standings';
$lang['menu_stats'] = 'Stats';
$lang['menu_summary'] = 'Summary';

// Column Headers
$lang['account'] = 'Account';
$lang['action'] = 'Action';
$lang['active'] = 'Active';
$lang['add'] = 'Add';
$lang['added_by'] = 'Added By';
$lang['adjustment'] = 'Adjustment';
$lang['administration'] = 'Administration';
$lang['administrative_options'] = 'Administrative Options';
$lang['admin_index'] = 'Admin Index';
$lang['attendance_by_event'] = 'Attendance by Event';
$lang['attended'] = 'Attended';
$lang['attendees'] = 'Attendees';
$lang['average'] = 'Average';
$lang['buyer'] = 'Buyer';
$lang['buyers'] = 'Buyers';
$lang['class'] = 'Class';
$lang['armor'] = 'Armor';
$lang['type'] = 'Armor';
$lang['class_distribution'] = 'Class Distribution';
$lang['class_summary'] = "Class Summary: %1\$s to %2\$s";
$lang['configuration'] = 'Configuration';
$lang['current'] = 'Current';
$lang['date'] = 'Date';
$lang['delete'] = 'Delete';
$lang['delete_confirmation'] = 'Delete Confirmation';
$lang['dkp_value'] = "%1\$s Value";
$lang['drops'] = 'Drops';
$lang['earned'] = 'Earned';
$lang['enter_dates'] = 'Enter Dates';
$lang['eqdkp_index'] = 'EQdkp Index';
$lang['eqdkp_upgrade'] = 'EQdkp Upgrade';
$lang['event'] = 'Event';
$lang['events'] = 'Events';
$lang['filter'] = 'Filter';
$lang['first'] = 'First';
$lang['rank'] = 'Rank';
$lang['general_admin'] = 'General Admin';
$lang['get_new_password'] = 'Get a New Password';
$lang['group_adj'] = 'Group Adj.';
$lang['group_adjustments'] = 'Group Adjustments';
$lang['individual_adjustments'] = 'Individual Adjustments';
$lang['individual_adjustment_history'] = 'Individual Adjustment History';
$lang['indiv_adj'] = 'Indiv. Adj.';
$lang['ip_address'] = 'IP Address';
$lang['item'] = 'Item';
$lang['items'] = 'Items';
$lang['item_purchase_history'] = 'Item Purchase History';
$lang['last'] = 'Last';
$lang['lastloot'] = 'Last Loot';
$lang['lastraid'] = 'Last Raid';
$lang['last_visit'] = 'Last Visit';
$lang['level'] = 'Level';
$lang['log_date_time'] = 'Date/Time of this Log';
$lang['loot_factor'] = 'Loot Factor';
$lang['loots'] = 'Loots';
$lang['manage'] = 'Manage';
$lang['member'] = 'Member';
$lang['members'] = 'Members';
$lang['members_present_at'] = "Members Present at %1\$s on %2\$s";
$lang['miscellaneous'] = 'Miscellaneous';
$lang['name'] = 'Name';
$lang['news'] = 'News';
$lang['note'] = 'Note';
$lang['online'] = 'Online';
$lang['options'] = 'Options';
$lang['paste_log'] = 'Paste a Log Below';
$lang['percent'] = 'Percent';
$lang['permissions'] = 'Permissions';
$lang['per_day'] = 'Per Day';
$lang['per_raid'] = 'Per Raid';
$lang['pct_earned_lost_to'] = '% Earned Lost to';
$lang['preferences'] = 'Preferences';
$lang['purchase_history_for'] = "Purchase History for %1\$s";
$lang['quote'] = 'Quote';
$lang['race'] = 'Race';
$lang['raid'] = 'Raid';
$lang['raids'] = 'Raids';
$lang['raid_id'] = 'Raid ID';
$lang['raid_attendance_history'] = 'Raid Attendance History';
$lang['raids_lifetime'] = "Lifetime (%1\$s - %2\$s)";
$lang['raids_x_days'] = "Last %1\$d Days";
$lang['rank_distribution'] = 'Rank Distribution';
$lang['recorded_raid_history'] = "Recorded Raid History for %1\$s";
$lang['reason'] = 'Reason';
$lang['registration_information'] = 'Registration Information';
$lang['result'] = 'Result';
$lang['session_id'] = 'Session ID';
$lang['settings'] = 'Settings';
$lang['spent'] = 'Spent';
$lang['summary_dates'] = "Raid Summary: %1\$s to %2\$s";
$lang['themes'] = 'Themes';
$lang['time'] = 'Time';
$lang['total'] = 'Total';
$lang['total_earned'] = 'Total Earned';
$lang['total_items'] = 'Total Items';
$lang['total_raids'] = 'Total Raids';
$lang['total_spent'] = 'Total Spent';
$lang['transfer_member_history'] = 'Transfer Member History';
$lang['turn_ins'] = 'Turn-ins';
$lang['type'] = 'Type';
$lang['update'] = 'Update';
$lang['updated_by'] = 'Updated By';
$lang['user'] = 'User';
$lang['username'] = 'Username';
$lang['value'] = 'Value';
$lang['view'] = 'View';
$lang['view_action'] = 'View Action';
$lang['view_logs'] = 'View Logs';

// Page Foot Counts
$lang['listadj_footcount']               = "... found %1\$d adjustment(s) / %2\$d per page";
$lang['listevents_footcount']            = "... found %1\$d events / %2\$d per page";
$lang['listiadj_footcount']              = "... found %1\$d individual adjustment(s) / %2\$d per page";
$lang['listitems_footcount']             = "... found %1\$d unique items / %2\$d per page";
$lang['listmembers_active_footcount']    = "... found %1\$d active members / %2\$sshow all</a>";
$lang['listmembers_compare_footcount']   = "... comparing %1\$d members";
$lang['listmembers_footcount']           = "... found %1\$d members";
$lang['listnews_footcount']              = "... found %1\$d news entries / %2\$d per page";
$lang['listpurchased_footcount']         = "... found %1\$d item(s) / %2\$d per page";
$lang['listraids_footcount']             = "... found %1\$d raid(s) / %2\$d per page";
$lang['stats_active_footcount']          = "... found %1\$d active member(s) / %2\$sshow all</a>";
$lang['stats_footcount']                 = "... found %1\$d member(s)";
$lang['viewevent_footcount']             = "... found %1\$d raid(s)";
$lang['viewitem_footcount']              = "... found %1\$d item(s)";
$lang['viewmember_adjustment_footcount'] = "... found %1\$d individual adjustment(s)";
$lang['viewmember_item_footcount']       = "... found %1\$d purchased item(s) / %2\$d per page";
$lang['viewmember_raid_footcount']       = "... found %1\$d attended raid(s) / %2\$d per page";
$lang['viewraid_attendees_footcount']    = "... found %1\$d attendee(s)";
$lang['viewraid_drops_footcount']        = "... found %1\$d drop(s)";

// Submit Buttons
$lang['close_window'] = 'Close Window';
$lang['compare_members'] = 'Compare Members';
$lang['create_news_summary'] = 'Create News Summary';
$lang['login'] = 'Login';
$lang['logout'] = 'Logout';
$lang['log_add_data'] = 'Add Data to Form';
$lang['lost_password'] = 'Lost Password';
$lang['no'] = 'No';
$lang['proceed'] = 'Proceed';
$lang['reset'] = 'Reset';
$lang['set_admin_perms'] = 'Set Administrative Permissions';
$lang['submit'] = 'Submit';
$lang['upgrade'] = 'Upgrade';
$lang['yes'] = 'Yes';

// Form Element Descriptions
$lang['admin_login'] = 'Administrator Login';
$lang['confirm_password'] = 'Confirm Password';
$lang['confirm_password_note'] = 'You only need to confirm your new password if you changed it above';
$lang['current_password'] = 'Current Password';
$lang['current_password_note'] = 'You must confirm your current password if you wish to change your username or password';
$lang['email'] = 'Email';
$lang['email_address'] = 'Email Address';
$lang['ending_date'] = 'Ending Date';
$lang['from'] = 'From';
$lang['guild_tag'] = 'Guild Tag';
$lang['language'] = 'Language';
$lang['new_password'] = 'New Password';
$lang['new_password_note'] = 'You only need to supply a new password if you want to change it';
$lang['password'] = 'Password';
$lang['remember_password'] = 'Remember me (cookie)';
$lang['starting_date'] = 'Starting Date';
$lang['style'] = 'Style';
$lang['to'] = 'To';
$lang['username'] = 'Username';
$lang['users'] = 'Users';

// Pagination
$lang['next_page'] = 'Next Page';
$lang['page'] = 'Page';
$lang['previous_page'] = 'Previous Page';

// Permission Messages
$lang['noauth_default_title'] = 'Permission Denied';
$lang['noauth_u_event_list'] = 'You do not have permission to list events.';
$lang['noauth_u_event_view'] = 'You do not have permission to view events.';
$lang['noauth_u_item_list'] = 'You do not have permission to list items.';
$lang['noauth_u_item_view'] = 'You do not have permission to view items.';
$lang['noauth_u_member_list'] = 'You do not have permission to view member standings.';
$lang['noauth_u_member_view'] = 'You do not have permission to view member history.';
$lang['noauth_u_raid_list'] = 'You do not have permission to list raids.';
$lang['noauth_u_raid_view'] = 'You do not have permission to view raids.';

// Submission Success Messages
$lang['add_itemvote_success'] = 'Your vote on the item has been recorded.';
$lang['update_itemvote_success'] = 'Your vote on the item has been updated.';
$lang['update_settings_success'] = 'The user settings have been updated.';

// Form Validation Errors
$lang['fv_alpha_attendees'] = 'Characters\' names in EverQuest contain only alphabetic characters.';
$lang['fv_already_registered_email'] = 'That e-mail address is already registered.';
$lang['fv_already_registered_username'] = 'That username is already registered.';
$lang['fv_difference_transfer'] = 'A history transfer must be made between two different people.';
$lang['fv_difference_turnin'] = 'A turn-in must be made between two different people.';
$lang['fv_invalid_email'] = 'The e-mail address does not appear to be valid.';
$lang['fv_match_password'] = 'The password fields must match.';
$lang['fv_member_associated']  = "%1\$s is already associated with another user account.";
$lang['fv_number'] = 'Must be a number.';
$lang['fv_number_adjustment'] = 'The adjustment value field must be a number.';
$lang['fv_number_alimit'] = 'The adjustments limit field must be a number.';
$lang['fv_number_ilimit'] = 'The items limit field must be a number.';
$lang['fv_number_inactivepd'] = 'The inactive period must be a number.';
$lang['fv_number_pilimit'] = 'The purchased items limit must be a number.';
$lang['fv_number_rlimit'] = 'The raids limit must be a number.';
$lang['fv_number_value'] = 'The value field must be a number.';
$lang['fv_number_vote'] = 'The vote field must be a number.';
$lang['fv_range_day'] = 'The day field must be an integer between 1 and 31.';
$lang['fv_range_hour'] = 'The hour field must be an integer between 0 and 23.';
$lang['fv_range_minute'] = 'The minute field must be an integer between 0 and 59.';
$lang['fv_range_month'] = 'The month field must be an integer between 1 and 12.';
$lang['fv_range_second'] = 'The second field must be an integer between 0 and 59.';
$lang['fv_range_year'] = 'The year field must be an integer with a value of at least 1998.';
$lang['fv_required'] = 'Required Field';
$lang['fv_required_acro'] = 'The guild acronym field is required.';
$lang['fv_required_adjustment'] = 'The adjustment value field is required.';
$lang['fv_required_attendees'] = 'There must be at least one attendee on this raid.';
$lang['fv_required_buyer'] = 'A buyer must be selected.';
$lang['fv_required_buyers'] = 'At least one buyer must be selected.';
$lang['fv_required_email'] = 'The e-mail address field is required.';
$lang['fv_required_event_name'] = 'An event must be selected.';
$lang['fv_required_guildtag'] = 'The guildtag field is required.';
$lang['fv_required_headline'] = 'The headline field is required.';
$lang['fv_required_inactivepd'] = 'If the hide inactive members field is set to Yes, a value for the inactive period must also be set.';
$lang['fv_required_item_name'] = 'The item name field must be filled out or an existing item must be selected.';
$lang['fv_required_member'] = 'A member must be selected.';
$lang['fv_required_members'] = 'At least one member must be selected.';
$lang['fv_required_message'] = 'The message field is required.';
$lang['fv_required_name'] = 'The name field is required.';
$lang['fv_required_password'] = 'The password field is required.';
$lang['fv_required_raidid'] = 'A raid must be selected.';
$lang['fv_required_user'] = 'The username field is required.';
$lang['fv_required_value'] = 'The value field is required.';
$lang['fv_required_vote'] = 'The vote field is required.';

// Miscellaneous
$lang['added'] = 'Added';
$lang['additem_raidid_note'] = "Only raids less than two weeks old are shown / %1\$sshow all</a>";
$lang['additem_raidid_showall_note'] = 'Showing all raids';
$lang['addraid_datetime_note'] = 'If you parse a log, the date and time will be found automatically.';
$lang['addraid_value_note'] = 'for a one-time bonus; preset value for the event selected is used if left blank';
$lang['add_items_from_raid'] = 'Add Items from this Raid';
$lang['deleted'] = 'Deleted';
$lang['done'] = 'Done';
$lang['enter_new'] = 'Enter New';
$lang['error'] = 'Error';
$lang['head_admin'] = 'Head Admin';
$lang['hold_ctrl_note'] = 'Hold CTRL to select multiple';
$lang['list'] = 'List';
$lang['list_groupadj'] = 'List Group Adjustments';
$lang['list_events'] = 'List Events';
$lang['list_indivadj'] = 'List Individual Adjustments';
$lang['list_items'] = 'List Items';
$lang['list_members'] = 'List Members';
$lang['list_news'] = 'List News';
$lang['list_raids'] = 'List Raids';
$lang['may_be_negative_note'] = 'may be negative';
$lang['not_available'] = 'Not Available';
$lang['no_news'] = 'No news entries found.';
$lang['of_raids'] = "%1\$d%% of raids";
$lang['or'] = 'Or';
$lang['powered_by'] = 'Powered by';
$lang['preview'] = 'Preview';
$lang['required_field_note'] = 'Items marked with a * are required fields.';
$lang['select_1ofx_members'] = "Select 1 of %1\$d members...";
$lang['select_existing'] = 'Select Existing';
$lang['select_version'] = 'Select the EQdkp version that you are upgrading from';
$lang['success'] = 'Success';
$lang['s_admin_note'] = 'These fields cannot be modified by the users.';
$lang['transfer_member_history_description'] = 'This transfers all of a member\'s history (raids, items, adjustments) to another member.';
$lang['updated'] = 'Updated';
$lang['upgrade_complete'] = 'Your EQdkp installation has been successfully upgraded.<br /><br /><b class="negative">For extra security, remove this file!</b>';

// Settings
$lang['account_settings'] = 'Account Settings';
$lang['adjustments_per_page'] = 'Adjustments per Page';
$lang['basic'] = 'Basic';
$lang['events_per_page'] = 'Events per Page';
$lang['items_per_page'] = 'Items per Page';
$lang['news_per_page'] = 'News Entries per Page';
$lang['raids_per_page'] = 'Raids per Page';
$lang['associated_members'] = 'Associated Members';
$lang['guild_members'] = 'Guild Member(s)';
$lang['default_locale'] = 'Default Locale';


// Error messages
$lang['error_account_inactive'] = 'Your account is inactive.';
$lang['error_already_activated'] = 'That account has already been activated.';
$lang['error_invalid_email'] = 'A valid e-mail address was not provided.';
$lang['error_invalid_event_provided'] = 'A valid event id was not provided.';
$lang['error_invalid_item_provided'] = 'A valid item id was not provided.';
$lang['error_invalid_key'] = 'You have provided an invalid activation key.';
$lang['error_invalid_name_provided'] = 'A valid member name was not provided.';
$lang['error_invalid_news_provided'] = 'A valid news id was not provided.';
$lang['error_invalid_raid_provided'] = 'A valid raid id was not provided.';
$lang['error_user_not_found'] = 'A valid username was not provided';
$lang['incorrect_password'] = 'Incorrect password';
$lang['invalid_login'] = 'You have provided an incorrect or invalid username or password';
$lang['not_admin'] = 'You are not an administrator';

// Registration
$lang['account_activated_admin']   = 'The account has been activated. An e-mail has been sent to the user informing them of this change.';
$lang['account_activated_user']    = "Your account has been activated and you can now %1\$slog in%2\$s.";
$lang['password_sent'] = 'Your new account password has been e-mailed to you.';
$lang['register_activation_self']  = "Your account has been created, but before you can use it you need to activate it.<br /><br />An e-mail has been sent to %1\$s with information on how to activate your account.";
$lang['register_activation_admin'] = "Your account has been created, but before you can use it an administrator needs to activate it.<br /><br />An e-mail has been sent to %1\$s with more information.";
$lang['register_activation_none']  = "Your account has been created and you can now %1\$slog in%2\$s.<br /><br />An e-mail has been sent to %3\$s with more information.";

// lua 
$lang['lua'] = "CT_RaidTracker Import";
$lang['lua_parse'] = "Import LUA";
$lang['import_lua_data'] = "Import CT_RaidTracker Data";
$lang['lua_step1_pagetitel'] = "CT_Raidtracker Import";
$lang['lua_step1_th'] = "Paste the Log Below";
$lang['lua_step1_button_parselog'] = "Parse Log";
$lang['lua_step1_invalidstring_titel'] = "Invalid DKP String";
$lang['lua_step1_invalidstring_msg'] = "The DKP String is not valid.";
$lang['lua_step1_button_parselog'] = "Parse Log";
$lang['lua_step2_pagetitel'] = "CT_Raidtracker Import";
$lang['lua_step2_foundraids'] = "Found Raids";
$lang['lua_step2_dkpvaluetip'] = "Add Item value/attendees";
$lang['lua_step2_insertraids'] = "Insert Raid(s)";
$lang['lua_step2_raidsdropsdetails'] = "Raid/Drop Details";
$lang['lua_step3_pagetitel'] = "CT_Raidtracker Import";
$lang['lua_step3_titel'] = "Action log<br>\n";
$lang['lua_step3_alreadyexist'] = "%s (%s, %s DKP) was already added, skipping<br>\n";
$lang['lua_step3_raidadded'] = "%s (%s, %s DKP) was added<br>\n";
$lang['lua_step3_memberadded'] = "%s (race: %s, class: %s, level: %s, rank: %s) was added to the Members<br>\n";
$lang['lua_step3_attendeesadded'] = "%s attendees were added<br>\n";
$lang['lua_step3_lootadded'] = "%s (%s DKP) was added to %s<br>\n";

?>
