<?php

/**
* Get database size
* Currently only mysql is supported
*/

define('EQDKP_INC', true);
define('IN_ADMIN', true);
$eqdkp_root_path = '../upload/';
include($eqdkp_root_path . 'common.php');

// The functions to test:

function new_get_database_size($row = false)
{
	global $db, $dbname, $user, $lang, $table_prefix;

	$database_size = false;

	// This code is influenced in part by phpBB3, and also in part by phpMyAdmin 2.11
	switch($db->sql_layer)
	{
		case 'mysql':
		case 'mysql4':
		case 'mysqli':

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


					// For versions < 4.1.2, the db engine type has the column name 'Type' instead of 'Engine'
					$engine = (version_compare($version, '4.1.2', '<')) ? 'Type' : 'Engine';

					$database_size = 0;
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


function old_get_database_size($row = false)
{
	global $db, $dbname, $user, $lang, $table_prefix;

	$database_size = false;

	// This code is influenced in part by phpBB3, and also in part by phpMyAdmin 2.11
	switch($db->sql_layer)
	{
		case 'mysql':
		case 'mysql4':
		case 'mysqli':

			if ($row)
			{
				$version = $row['mysql_version'];

				if (preg_match('#(3\.23|[45]\.)#', $version))
				{
					$db_name = (preg_match('#^(?:3\.23\.(?:[6-9]|[1-9]{2}))|[45]\.#', $version)) ? "`{$dbname}`" : $dbname;


					// For versions < 4.1.2, the db engine type has the column name 'Type' instead of 'Engine'
					$engine = (preg_match('#^(?:3\.23\.(?:[6-9]|[1-9]{2}))|(?:4\.0)|(?:4\.1\.[01])#', $version)) ? 'Type' : 'Engine';

					$database_size = 0;
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

echo "Starting... EQDKP version ";
echo EQDKP_VERSION . "<br />\n";

$sql = 'SELECT VERSION() AS mysql_version';
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

echo "MySQL version: ";
echo var_dump($row);
echo "<br /><br />\n";

set_time_limit(3600);

$s = microtime(1);
for ($i = 0; $i < 1000000; $i++) old_get_database_size($row);
$e = microtime(1);

echo "Old method: " . ($e - $s) . "<br />\n";


$s = microtime(1);
for ($i = 0; $i < 1000000; $i++) new_get_database_size($row);
$e = microtime(1);

echo "New method: " . ($e - $s) . "<br />\n";

?>