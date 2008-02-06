<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        lang_install.php
 * Began:       Day Jan 1 2003
 * Date:        $Date: 2007-06-19 07:29:11 +1000 (D, d m Y) $
 * -----------------------------------------------------------------------
 * @author      $Author: tsigo $
 * @copyright   2002-2007 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev: 46 $
 */

if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

// Initialize the language array if it isn't already
if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// %1\$<type> prevents a possible error in strings caused
// by another language re-ordering the variables
// $s is a string, $d is an integer, $f is a float

$lang = array_merge($lang, array(
    'YES'        => 'Yes',
    'NO'         => 'No',
    
    'ADMIN_CONFIG'                            => 'Administrator configuration',
    'ADMIN_EMAIL'                             => 'Administrator email',
    'ADMIN_EMAIL_CONFIRM'                     => 'Confirm administrator email',
    'ADMIN_PASSWORD'                          => 'Administrator password',
    'ADMIN_PASSWORD_CONFIRM'                  => 'Confirm administrator password',
    'ADMIN_PASSWORD_EXPLAIN'                  => 'Please enter a password between 6 and 30 characters in length.',
    'ADMIN_TEST'                              => 'Check administrator settings',
    'ADMIN_USERNAME'                          => 'Administrator username',
    'ADMIN_USERNAME_EXPLAIN'                  => 'Please enter a username between 3 and 20 characters in length.',
    'ADMINISTRATOR_TITLE'                     => 'Administrator Settings',
    'AUTH_SALT'                               => 'Password Salt',
    'AUTH_SALT_EXPLAIN'                       => 'A password salt is a small string used to make passwords more secure. For more information, visit <a href="http://en.wikipedia.org/wiki/Salt_(cryptography)" target="_blank">Salt (Cryptography)</a> [Wikipedia.org].<br />Please enter a string of alpha-numeric characters between 3 and 20 characters in length.',
    'AVAILABLE'                               => 'Available',
    
    'CAT_INSTALL'                             => 'Install',
    'CAT_OVERVIEW'                            => 'Overview',
    'CAT_UPDATE'                              => 'Update',
    'CREATE_DATABASE_TABLES_TITLE'            => 'Create Database Tables',

    'CONFIG_FILE'                             => 'Configuration File',
    'CONFIG_FILE_EXPLAIN'                     => '',
    'CONFIG_FILE_UNWRITABLE_EXPLAIN'          => 'The config file does not exist and could not be created in EQdkp\'s root folder. You must create an empty config.php file on your server before proceeding.',
    'CONFIG_FILE_CREATED_EXPLAIN'             => 'The config file has been created in EQdkp\'s root folder. Deleting this file will interfere with the operation of your EQdkp installation.',
    'CONFIG_FILE_PERMISSIONS_INVALID_EXPLAIN' => 'The config file is not set to be readable/writeable and could not be changed automatically. Please change the permissions to 0666 manually by executing <strong>chmod 0666 config.php</strong> on your server.',
    'CONFIG_FILE_PERMISSIONS_CREATED_EXPLAIN' => 'The config file has been set to be readable/writeable in order to let this installer write your configuration file automatically.',
    'CONFIG_FILE_UNABLE_WRITE'                => 'It was not possible to write the configuration file. Alternative methods for this file to be created are presented below.',
    'CONFIG_FILE_WRITTEN'                     => 'The configuration file has been written. You may now proceed to the next step of the installation.',
    'CONFIG_PHPBB_EMPTY'                      => 'The phpBB3 config variable for "%s" is empty.',
    'CONFIG_RETRY'                            => 'Retry',

    'DATABASE'                                => 'Database',
    'DATABASE_TITLE'                          => 'Database Settings',
    'DATABASE_BODY'                           => '',
    'DB_CONFIG'                               => 'Database Configuration',
    'DB_CONNECTION'                           => 'Database connection',
    'DB_ERR_INSERT'                           => 'Error while processing <code>INSERT</code> query.',
    'DB_ERR_LAST'                             => 'Error while processing <var>query_last</var>.',
    'DB_ERR_QUERY_FIRST'                      => 'Error while executing <var>query_first</var>.',
    'DB_ERR_QUERY_FIRST_TABLE'                => 'Error while executing <var>query_first</var>, %s ("%s").',
    'DB_ERR_SELECT'                           => 'Error while running <code>SELECT</code> query.',
    'DB_TYPE'                                 => 'Database Type',
    'DB_HOST'                                 => 'Database Host',
    'DB_NAME'                                 => 'Database Name',
    'DB_PORT'                                 => 'Database server port',
    'DB_PORT_EXPLAIN'                         => 'Leave this as its default value unless you know the server operates on a non-standard port.',
    'DB_USERNAME'                             => 'Database Username',
    'DB_PASSWORD'                             => 'Database Password',
    'DB_TEST'                                 => 'Test connection',
    'DB_TEST_NOTE'                            => 'Before proceeding, please verify that the database name you provided is already created and that the user you provided has permission to create tables in that database.',

    'DEFAULT_CONFIG'                          => 'Default Configuration Settings',
    'DEFAULT_LANG'                            => 'Default Language',
    'DEFAULT_LOCALE'                          => 'Default Locale',
    'DKP_NAME'                                => 'DKP Name',
    'DKP_NAME_EXPLAIN'                        => '',
    'DLL_MYSQL'                               => 'MySQL',
    'DL_CONFIG'                               => 'Download config',
    'DL_CONFIG_EXPLAIN'                       => 'You may download the complete config.php to your own PC. You will then need to upload the file manually, replacing any existing config.php in your phpBB 3.0 root directory. Please remember to upload the file in ASCII format (see your FTP application documentation if you are unsure how to achieve this). When you have uploaded the config.php please click “Done” to move to the next stage.',
    'DL_DOWNLOAD'                             => 'Download',
    'DONE'                                    => 'Done',

    'EQDKP_INFO'                              => 'EQDKP Information',
    'EQDKP_INFO_EXPLAIN'                      => '',
    'EQDKP_SETTINGS'                          => 'EQDKP Settings',
    'EQDKP_SETTINGS_TITLE'                    => 'EQDKP and Game Settings',
    'EQDKP_SETTINGS_BODY'                     => 'Here you may select the game to use with EQdkp, in addition to entering your guild\'s information and other EQdkp-related settings.',
    'EQDKP_TEST'                              => 'Check EQDKP Settings',
    'EQDKP_VER_CURRENT'                       => 'EQDKP Installation Version',
    'EQDKP_VER_LATEST'                        => 'EQDKP Latest Version',
    'EQDKP_VER_CHECK_CONN_FAIL'               => 'Unknown [Connection to EQdkp.com failed.]',
    'EQDKP_VER_CHECK_FAIL'                    => 'Unknown [Version retrieval from EQdkp.com failed.]',

    'FILES_OPTIONAL'                          => 'Optional files and directories',
    'FILES_OPTIONAL_EXPLAIN'                  => '<strong>Optional</strong> - These files, directories or permission settings are not required. The installation system will attempt to use various techniques to create them if they do not exist or cannot be written to. However, the presence of these will speed installation.',
    'FILES_REQUIRED'                          => 'Files and Directories',
    'FILES_REQUIRED_EXPLAIN'                  => '<strong>Required</strong> - In order to function correctly, EQdkp needs to be able to access or write to certain files or directories. If you see “Not Found” you need to create the relevant file or directory. If you see “Unwritable” you need to change the permissions on the file or directory to allow EQdkp to write to it.',
    'FOUND'                                   => 'Found',

    'GAME'                                    => 'Game',
    'GAME_INFO'                               => 'Game Information',
    'GAME_NAME'                               => 'Game',
    'GAME_NAME_EXPLAIN'                       => '',
    'GAME_CONFIG'                             => 'Game Settings',
    'GAME_MANAGEMENT'                         => 'Game Management',
    'GUILD_NAME'                              => 'Guild Name',
    'GUILD_NAME_EXPLAIN'                      => '',

    'INSTALL_CONGRATS'                        => 'Congratulations!',
    'INSTALL_CONGRATS_EXPLAIN'                => '
        <p>You have now successfully installed EQdkp %1$s.</p>
        <p>Clicking the button below will take you to the Administration section of your EQdkp. For EQdkp support, you can visit the <a href="http://forums.eqdkp.com/">EQdkp forums</a> and request assistance in the appropriate forum sections.</p><p><strong>Please now delete, move or rename the install directory before you use EQdkp.</strong></p>',
    'INSTALL_INTRO'                           => 'Welcome to EQdkp Installation',
    'INSTALL_INTRO_BODY'                      => 'The following process will install EQdkp onto your server.</p>
    <p>In order to proceed, you will need your database settings. If you do not know your database settings, please contact your host and ask for them. You will not be able to continue without them. You need:</p>
    
    <ul>
        <li>The Database Type - the database you will be using.</li>
        <li>The Database server hostname or DSN - the address of the database server.</li>
        <li>The Database server port - the port of the database server (most of the time this is not needed).</li>
        <li>The Database name - the name of the database on the server.</li>
        <li>The Database username and Database password - the login data to access the database.</li>
    </ul>
    
    <p>EQdkp supports the following databases:</p>
    <ul>
        <li>MySQL 4.04 or above</li>
    </ul>
    
    <p>Only those databases supported on your server will be displayed.',
    
    'INSTALL_INTRO_NEXT'            => 'To commence the installation, please press the button below.',
    'INSTALL_LOGIN'                 => 'Login',
    'INSTALL_MINREQ_PASS'           => 'EQdkp has scanned your server and determined that it meets the minimum requirements in order to install.',
    'INSTALL_MINREQ_FAIL'           => 'Sorry, your server does not meet the minimum requirements for EQdkp.',
    'INSTALL_NEXT'                  => 'Next stage',
    'INSTALL_NEXT_FAIL'             => 'Some tests failed and you should correct these problems before proceeding to the next stage. Failure to do so may result in an incomplete installation.',
    'INSTALL_NEXT_PASS'             => 'All the basic tests have been passed and you may proceed to the next stage of installation. If you have changed any permissions, modules, etc. and wish to re-test you can do so if you wish.',
    'INSTALL_PANEL'                 => 'Installation Panel',
    'INSTALL_SEND_CONFIG'           => 'Unfortunately EQdkp could not write the configuration information directly to your config.php. This may be because the file does not exist or is not writable. A number of options will be listed below enabling you to complete installation of config.php.',
    'INSTALL_START'                 => 'Start install',
    'INSTALL_TEST'                  => 'Test again',

    'INST_ERR'                      => 'Installation error',
    'INST_ERR_DB_CONNECT'           => 'Could not connect to the database, see error message below.',
    'INST_ERR_DB_FORUM_PATH'        => 'The database file specified is within your board directory tree. You should put this file in a non web-accessible location.',
    'INST_ERR_DB_NO_ERROR'          => 'No error message given.',
    'INST_ERR_DB_NO_MYSQLI'         => 'The version of MySQL installed on this machine is incompatible with the “MySQL with MySQLi Extension” option you have selected. Please try the “MySQL” option instead.',
    'INST_ERR_DB_NO_SQLITE'         => 'The version of the SQLite extension you have installed is too old, it must be upgraded to at least 2.8.2.',
    'INST_ERR_DB_NO_ORACLE'         => 'The version of Oracle installed on this machine requires you to set the <var>NLS_CHARACTERSET</var> parameter to <var>UTF8</var>. Either upgrade your installation to 9.2+ or change the parameter.',
    'INST_ERR_DB_NO_FIREBIRD'       => 'The version of Firebird installed on this machine is older than 2.0, please upgrade to a newer version.',
    'INST_ERR_DB_NO_FIREBIRD_PS'    => 'The database you selected for Firebird has a page size less than 8192, it must be at least 8192.',
    'INST_ERR_DB_NO_POSTGRES'       => 'The database you have selected was not created in <var>UNICODE</var> or <var>UTF8</var> encoding. Try installing with a database in <var>UNICODE</var> or <var>UTF8</var> encoding.',
    'INST_ERR_DB_NO_NAME'           => 'No database name specified.',
    'INST_ERR_EMAIL_INVALID'        => 'The e-mail address you entered is invalid.',
    'INST_ERR_EMAIL_MISMATCH'       => 'The e-mails you entered did not match.',
    'INST_ERR_FATAL'                => 'Fatal installation error',
    'INST_ERR_FATAL_DB'             => 'A fatal and unrecoverable database error has occurred. This may be because the specified user does not have appropriate permissions to <code>CREATE TABLES</code> or <code>INSERT</code> data, etc. Further information may be given below. Please contact your hosting provider in the first instance or the support forums of phpBB for further assistance.',
    'INST_ERR_FTP_PATH'             => 'Could not change to the given directory, please check the path.',
    'INST_ERR_FTP_LOGIN'            => 'Could not login to FTP server, check your username and password.',
    'INST_ERR_MISSING_DATA'         => 'You must fill out all fields in this block.',
    'INST_ERR_NO_DB'                => 'Cannot load the PHP module for the selected database type.',
    'INST_ERR_PASSWORD_MISMATCH'    => 'The passwords you entered did not match.',
    'INST_ERR_PASSWORD_TOO_LONG'    => 'The password you entered is too long. The maximum length is 30 characters.',
    'INST_ERR_PASSWORD_TOO_SHORT'   => 'The password you entered is too short. The minimum length is 6 characters.',
    'INST_ERR_PREFIX'               => 'Tables with the specified prefix already exist, please choose an alternative.',
    'INST_ERR_PREFIX_INVALID'       => 'The table prefix you have specified is invalid for your database. Please try another, removing characters such as the hyphen.',
    'INST_ERR_PREFIX_TOO_LONG'      => 'The table prefix you have specified is too long. The maximum length is %d characters.',
    'INST_ERR_SALT_TOO_LONG'        => 'The salt string you entered is too long. The maximum length is 20 characters.',
    'INST_ERR_SALT_TOO_SHORT'       => 'The salt string you entered is too short. The minimum length is 3 characters.',
    'INST_ERR_SALT_INVALID'         => 'The salt you entered is invalid. Please enter only alpha-numeric characters.',
    'INST_ERR_USER_TOO_LONG'        => 'The username you entered is too long. The maximum length is 20 characters.',
    'INST_ERR_USER_TOO_SHORT'       => 'The username you entered is too short. The minimum length is 3 characters.',
    'INVALID_PRIMARY_KEY'           => 'Invalid primary key : %s',

    'NEXT_STEP'                     => 'Proceed to next step',
    'NOT_FOUND'                     => 'Not Found',

    'OTHER_SETTINGS'                => 'Other Settings',

    'PHP_OPTIONAL_MODULE'           => 'Optional modules',
    'PHP_OPTIONAL_MODULE_EXPLAIN'   => '<strong>Optional</strong> - These modules or applications are optional. However, if they are available they will enable extra features.',
    'PHP_SUPPORTED_DB'              => 'Supported databases',
    'PHP_SUPPORTED_DB_EXPLAIN'      => '<strong>Required</strong> - EQdkp requires a MySQL datbase and the PHP module for MySQL to be available. If MySQL is shown as unavailable below you should contact your hosting provider or review the relevant PHP installation documentation for advice as to how to enable it.',
    'PHP_REGISTER_GLOBALS'          => 'PHP setting <var>register_globals</var> is disabled',
    'PHP_REGISTER_GLOBALS_EXPLAIN'  => 'EQdkp will still run if this setting is enabled, but if possible, it is recommended that register_globals is disabled on your PHP install for security reasons.',
    'PHP_SAFE_MODE'                 => 'Safe mode',
    'PHP_SETTINGS'                  => 'PHP version and settings',
    'PHP_SETTINGS_EXPLAIN'          => '<strong>Required</strong> - You must be running at least version %s of PHP in order to install EQdkp. If <var>safe mode</var> is displayed below, your PHP installation is running in that mode.',
    'PHP_URL_FOPEN_SUPPORT'         => 'PHP setting <var>allow_url_fopen</var> is enabled',
    'PHP_URL_FOPEN_SUPPORT_EXPLAIN' => '<strong>Optional</strong> - Certain EQdkp functions and plugins like itemstats will not work properly without this setting enabled. ',
    'PHP_VERSION_REQD'              => 'PHP version >= %s',

    'REQUIREMENTS_TITLE'            => 'Installation compatibility',
    'REQUIREMENTS_EXPLAIN'          => 'Before proceeding with the full installation EQdkp will carry out some tests on your server configuration and files to ensure that you are able to install and run EQdkp. Please ensure you read through the results thoroughly and do not proceed until all the required tests are passed. If you wish to use any of the features depending on the optional tests, you should ensure that these tests are passed also.',

    'STAGE_ADMINISTRATOR'           => 'Administrator details',
    'STAGE_ADVANCED'                => 'Advanced settings',
    'STAGE_ADVANCED_EXPLAIN'        => 'The settings on this page are only necessary to set if you know that you require something different from the default. If you are unsure, just proceed to the next page, as these settings can be altered from the Administration Control Panel later.',
    'STAGE_CONFIG_FILE'             => 'Configuration file',
    'STAGE_CREATE_TABLE'            => 'Create database tables',
    'STAGE_CREATE_TABLE_EXPLAIN'    => 'The database tables used by EQdkp have been created and populated with some initial data. Proceed to the next screen to finish installing EQdkp.',
    'STAGE_DATABASE'                => 'Database settings',
    'STAGE_EQDKP_SETTINGS'          => 'EQDKP Settings',
    'STAGE_FINAL'                   => 'Final stage',
    'STAGE_GAME_SETTINGS'           => 'Game Settings',
    'STAGE_INTRO'                   => 'Introduction',
    'STAGE_IN_PROGRESS'             => 'Conversion in progress',
    'STAGE_REQUIREMENTS'            => 'Requirements',
    'STAGE_SETTINGS'                => 'Settings',

    'SERVER_NAME'                   => 'Domain Name',
    'SERVER_PATH'                   => 'Script Path',
    'SERVER_PATH_EXPLAIN'           => 'The path where EQdkp is located relative to the domain name, e.g. <samp>/eqdkp</samp>.',
    'SELECT_LANG'                   => 'Select language',
    'SERVER_CONFIG'                 => 'Server configuration',
    'SITE_NAME'                     => 'Site Name',
    'SITE_DESC'                     => 'Site Description',
    'SUB_INTRO'                     => 'Introduction',
    'SUCCESSFUL_CONNECT'            => 'Successful connection',

    'TABLE_PREFIX'                  => 'Table Prefix',
    'TABLES_MISSING'                => 'Could not find these tables<br />» <strong>%s</strong>.',
    'TABLE_PREFIX'                  => 'Prefix for tables in database',
    'TABLE_PREFIX_SAME'             => 'The table prefix needs to be the one used by the software you are converting from.<br />» Specified table prefix was %s.',
    'TESTS_PASSED'                  => 'Tests passed',
    'TESTS_FAILED'                  => 'Tests failed',

    'UNABLE_WRITE_LOCK'             => 'Unable to write lock file.',
    'UNAVAILABLE'                   => 'Unavailable',
    'UNWRITABLE'                    => 'Unwritable',
    'UNKNOWN'                       => 'Unknown',
    'UNWRITABLE'                    => 'Unwritable',

    'VERSION'                       => 'Version',

    'WRITABLE'                      => 'Writable',
));
