<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        functions_admin.php
 * Began:       Sat Oct 13 2007
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */
 
if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

function generate_permission_boxes()
{
    global $user, $pm;
    
    // TODO: Remove the CBCHECKED globals and just use the strings (e.g., 'a_event_add')
    $retval = array(
        // Events
        $user->lang['events'] => array(
            array('CBNAME' => 'a_event_add',  'CBCHECKED' => A_EVENT_ADD,  'TEXT' => '<b>' . $user->lang['add'] . '</b>'),
            array('CBNAME' => 'a_event_upd',  'CBCHECKED' => A_EVENT_UPD,  'TEXT' => '<b>' . $user->lang['update'] . '</b>'),
            array('CBNAME' => 'a_event_del',  'CBCHECKED' => A_EVENT_DEL,  'TEXT' => '<b>' . $user->lang['delete'] . '</b>'),
            array('CBNAME' => 'u_event_list', 'CBCHECKED' => U_EVENT_LIST, 'TEXT' => $user->lang['list']),
            array('CBNAME' => 'u_event_view', 'CBCHECKED' => U_EVENT_VIEW, 'TEXT' => $user->lang['view'])
        ),
        // Group adjustments
        $user->lang['group_adjustments'] => array(
            array('CBNAME' => 'a_groupadj_add', 'CBCHECKED' => A_GROUPADJ_ADD, 'TEXT' => '<b>' . $user->lang['add'] . '</b>'),
            array('CBNAME' => 'a_groupadj_upd', 'CBCHECKED' => A_GROUPADJ_UPD, 'TEXT' => '<b>' . $user->lang['update'] . '</b>'),
            array('CBNAME' => 'a_groupadj_del', 'CBCHECKED' => A_GROUPADJ_DEL, 'TEXT' => '<b>' . $user->lang['delete'] . '</b>')
        ),
        // Individual adjustments
        $user->lang['individual_adjustments'] => array(
            array('CBNAME' => 'a_indivadj_add', 'CBCHECKED' => A_INDIVADJ_ADD, 'TEXT' => '<b>' . $user->lang['add'] . '</b>'),
            array('CBNAME' => 'a_indivadj_upd', 'CBCHECKED' => A_INDIVADJ_UPD, 'TEXT' => '<b>' . $user->lang['update'] . '</b>'),
            array('CBNAME' => 'a_indivadj_del', 'CBCHECKED' => A_INDIVADJ_DEL, 'TEXT' => '<b>' . $user->lang['delete'] . '</b>')
        ),
        // Items
        $user->lang['items'] => array(
            array('CBNAME' => 'a_item_add',  'CBCHECKED' => A_ITEM_ADD,  'TEXT' => '<b>' . $user->lang['add'] . '</b>'),
            array('CBNAME' => 'a_item_upd',  'CBCHECKED' => A_ITEM_UPD,  'TEXT' => '<b>' . $user->lang['update'] . '</b>'),
            array('CBNAME' => 'a_item_del',  'CBCHECKED' => A_ITEM_DEL,  'TEXT' => '<b>' . $user->lang['delete'] . '</b>'),
            array('CBNAME' => 'u_item_list', 'CBCHECKED' => U_ITEM_LIST, 'TEXT' => $user->lang['list']),
            array('CBNAME' => 'u_item_view', 'CBCHECKED' => U_ITEM_VIEW, 'TEXT' => $user->lang['view'])
        ),
        // News
        $user->lang['news'] => array(
            array('CBNAME' => 'a_news_add', 'CBCHECKED' => A_NEWS_ADD, 'TEXT' => '<b>' . $user->lang['add'] . '</b>'),
            array('CBNAME' => 'a_news_upd', 'CBCHECKED' => A_NEWS_UPD, 'TEXT' => '<b>' . $user->lang['update'] . '</b>'),
            array('CBNAME' => 'a_news_del', 'CBCHECKED' => A_NEWS_DEL, 'TEXT' => '<b>' . $user->lang['delete'] . '</b>')
        ),
        // Raids
        $user->lang['raids'] => array(
            array('CBNAME' => 'a_raid_add',  'CBCHECKED' => A_RAID_ADD,  'TEXT' => '<b>' . $user->lang['add'] . '</b>'),
            array('CBNAME' => 'a_raid_upd',  'CBCHECKED' => A_RAID_UPD,  'TEXT' => '<b>' . $user->lang['update'] . '</b>'),
            array('CBNAME' => 'a_raid_del',  'CBCHECKED' => A_RAID_DEL,  'TEXT' => '<b>' . $user->lang['delete'] . '</b>'),
            array('CBNAME' => 'u_raid_list', 'CBCHECKED' => U_RAID_LIST, 'TEXT' => $user->lang['list']),
            array('CBNAME' => 'u_raid_view', 'CBCHECKED' => U_RAID_VIEW, 'TEXT' => $user->lang['view'])
        ),
        // Turn-ins
        $user->lang['turn_ins'] => array(
            array('CBNAME' => 'a_turnin_add', 'CBCHECKED' => A_TURNIN_ADD, 'TEXT' => '<b>' . $user->lang['add'] . '</b>')
        ),
        // Members
        $user->lang['members'] => array(
            array('CBNAME' => 'a_members_man', 'CBCHECKED' => A_MEMBERS_MAN, 'TEXT' => '<b>' . $user->lang['manage'] . '</b>'),
            array('CBNAME' => 'u_member_list', 'CBCHECKED' => U_MEMBER_LIST, 'TEXT' => $user->lang['list']),
            array('CBNAME' => 'u_member_view', 'CBCHECKED' => U_MEMBER_VIEW, 'TEXT' => $user->lang['view'])
        ),
        // Manage
        $user->lang['manage'] => array(
            array('CBNAME' => 'a_config_man',  'CBCHECKED' => A_CONFIG_MAN,  'TEXT' => '<b>' . $user->lang['configuration'] . '</b>'),
            array('CBNAME' => 'a_plugins_man', 'CBCHECKED' => A_PLUGINS_MAN, 'TEXT' => '<b>' . $user->lang['plugins'] . '</b>'),
            array('CBNAME' => 'a_styles_man',  'CBCHECKED' => A_STYLES_MAN,  'TEXT' => '<b>' . $user->lang['styles'] . '</b>'),
            array('CBNAME' => 'a_users_man',   'CBCHECKED' => A_USERS_MAN,   'TEXT' => '<b>' . $user->lang['users'] . '</b>')
        ),
        // Logs
        $user->lang['logs'] => array(
            array('CBNAME' => 'a_logs_view', 'CBCHECKED' => A_LOGS_VIEW, 'TEXT' => '<b>' . $user->lang['view'] . '</b>')
        ),
        // Backup Database
        $user->lang['backup'] => array(
            array('CBNAME' => 'a_backup', 'CBCHECKED' => A_BACKUP, 'TEXT' => '<b>' . $user->lang['backup_database'] . '</b>')
        )
    );

    // Add plugin checkboxes to our array
    $pm->generate_permission_boxes($retval);
    
    return $retval;
}




/**
* Get database size
* Currently only mysql is supported
*/
function get_database_size()
{
    global $db, $dbname, $user, $table_prefix;

    $database_size = false;

    // This code is influenced in part by phpBB3, and also in part by phpMyAdmin 2.11
    switch($db->sql_layer)
    {
        case 'mysql':
        case 'mysql4':
        case 'mysqli':

            $sql = 'SELECT VERSION() AS mysql_version';
            $result = $db->sql_query($sql);
            $row = $db->sql_fetchrow($result);
            $db->sql_freeresult($result);

            if ($row)
            {
                $version = $row['mysql_version'];

                // Convert $version into a PHP comparable version
                $matches = array();
                if (preg_match('#[^\d\.]#', $version, $matches) > 0)
                {
                    $version = substr($version, 0, strpos($version, $matches[0]));
                }

                if (version_compare($version, '3.23', '>='))
                {
                    $db_name = (version_compare($version, '3.23.6', '>=')) ? "`{$dbname}`" : $dbname;

                    $sql = 'SHOW TABLE STATUS
                        FROM ' . $db_name;
                    $result = $db->sql_query($sql);

                    // For versions < 4.1.2, the db engine type has the column name 'Type' instead of 'Engine'
                    $engine = (version_compare($version, '4.1.2', '<')) ? 'Type' : 'Engine';

                    $database_size = 0;
                    while ($row = $db->sql_fetchrow($result))
                    {
                        switch($row[$engine])
                        {
                            case 'MRG_MyISAM':
                                // Do Nothing
                            break;
                            
                            case 'MyISAM':
                            case 'InnoDB':
                            default:
                                
                                if ($table_prefix != '')
                                {
                                    if (strpos($row['Name'], $table_prefix) !== false)
                                    {
                                        $database_size += $row['Data_length'] + $row['Index_length'];
                                    }
                                }
                                // If we dont have a table prefix, we're just gonna lump every table in the database into this calculation.
                                else
                                {
                                    $database_size += $row['Data_length'] + $row['Index_length'];
                                }
                                
                            break;
                        }
                    }
                    $db->sql_freeresult($result);
                }
            }
        break;
    }

    if ($database_size !== false)
    {
        $database_size = ($database_size >= 1048576) ? sprintf('%.2f ' . $user->lang['MB'], ($database_size / 1048576)) : (($database_size >= 1024) ? sprintf('%.2f ' . $user->lang['KB'], ($database_size / 1024)) : sprintf('%.2f ' . $user->lang['BYTES'], $database_size));
    }
    else
    {
        $database_size = $user->lang['not_available'];
    }

    return $database_size;

}
