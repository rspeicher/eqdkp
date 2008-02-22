<?php
define('EQDKP_INC', true);
$eqdkp_root_path = '../upload/';
include($eqdkp_root_path . 'common.php');

$table_prefix = 'eqdkp_inputsec_testing_500_';

$old_class_dbtable      = $table_prefix . 'old_classes';
$new_class_dbtable      = $table_prefix . 'new_classes';
$new_armor_dbtable      = $table_prefix . 'new_armor_types';
$new_classarmor_dbtable = $table_prefix . 'new_class_armor';

// This is the thing that controls what we're doing.
$action = '';

if (isset($_REQUEST['normalize_old_tables']))
{
	$action = 'normalize_old_tables';
}
else if (isset($_REQUEST['create_old_tables']))
{
	$action = 'create_old_tables';
}

// Some functions that'll do the work
// ----------------------------------

function the_start()
{
?><html><body>
<form action="normalize_gamedb.php" method="post" id="gamedb">
<input type="submit" id="create_old_tables" name="create_old_tables" value="Create Old Tables" />
</form>
</body></html>
<?php
}

function create_dbtable()
{
	global $db;
	global $old_class_dbtable;

	$sql = "CREATE TABLE IF NOT EXISTS `" . $old_class_dbtable . "` (
	  `c_index` smallint(3) unsigned NOT NULL auto_increment,
	  `class_id` smallint(3) unsigned NOT NULL,
	  `class_name` varchar(50) NOT NULL,
	  `class_min_level` smallint(3) NOT NULL default '0',
	  `class_max_level` smallint(3) NOT NULL default '999',
	  `class_armor_type` varchar(50) NOT NULL,
	  `class_hide` enum('0','1') NOT NULL default '0',
	  PRIMARY KEY  (`c_index`)
	) ENGINE=InnoDB";
	
	$db->sql_query($sql);
	
	// Clear out any old values
	$db->sql_query("TRUNCATE TABLE `" . $old_class_dbtable . "`");
	
	// Populate it with some default data.
	$db->sql_query("INSERT INTO `" . $old_class_dbtable . "` (`class_id`, `class_name`, `class_min_level`, `class_max_level`, `class_armor_type`, `class_hide`) VALUES(0, 'Unknown', 0, 70, 'Unknown', '0')");
	$db->sql_query("INSERT INTO `" . $old_class_dbtable . "` (`class_id`, `class_name`, `class_min_level`, `class_max_level`, `class_armor_type`, `class_hide`) VALUES(1, 'Druid', 0, 70, 'Leather', '0')");
	$db->sql_query("INSERT INTO `" . $old_class_dbtable . "` (`class_id`, `class_name`, `class_min_level`, `class_max_level`, `class_armor_type`, `class_hide`) VALUES(2, 'Hunter', 0, 70, 'Leather', '0')");
	$db->sql_query("INSERT INTO `" . $old_class_dbtable . "` (`class_id`, `class_name`, `class_min_level`, `class_max_level`, `class_armor_type`, `class_hide`) VALUES(3, 'Hunter', 40, 70, 'Chain', '1')");
	$db->sql_query("INSERT INTO `" . $old_class_dbtable . "` (`class_id`, `class_name`, `class_min_level`, `class_max_level`, `class_armor_type`, `class_hide`) VALUES(4, 'Mage', 0, 70, 'Cloth', '0')");
	$db->sql_query("INSERT INTO `" . $old_class_dbtable . "` (`class_id`, `class_name`, `class_min_level`, `class_max_level`, `class_armor_type`, `class_hide`) VALUES(5, 'Paladin', 0, 70, 'Chain', '0')");
	$db->sql_query("INSERT INTO `" . $old_class_dbtable . "` (`class_id`, `class_name`, `class_min_level`, `class_max_level`, `class_armor_type`, `class_hide`) VALUES(6, 'Paladin', 40, 70, 'Plate', '1')");
	$db->sql_query("INSERT INTO `" . $old_class_dbtable . "` (`class_id`, `class_name`, `class_min_level`, `class_max_level`, `class_armor_type`, `class_hide`) VALUES(7, 'Priest', 0, 70, 'Cloth', '0')");
	$db->sql_query("INSERT INTO `" . $old_class_dbtable . "` (`class_id`, `class_name`, `class_min_level`, `class_max_level`, `class_armor_type`, `class_hide`) VALUES(8, 'Rogue', 0, 70, 'Leather', '0')");
	$db->sql_query("INSERT INTO `" . $old_class_dbtable . "` (`class_id`, `class_name`, `class_min_level`, `class_max_level`, `class_armor_type`, `class_hide`) VALUES(9, 'Shaman', 0, 70, 'Leather', '0')");
	$db->sql_query("INSERT INTO `" . $old_class_dbtable . "` (`class_id`, `class_name`, `class_min_level`, `class_max_level`, `class_armor_type`, `class_hide`) VALUES(10, 'Shaman', 40, 70, 'Chain', '1')");
	$db->sql_query("INSERT INTO `" . $old_class_dbtable . "` (`class_id`, `class_name`, `class_min_level`, `class_max_level`, `class_armor_type`, `class_hide`) VALUES(11, 'Warlock', 0, 70, 'Cloth', '0')");
	$db->sql_query("INSERT INTO `" . $old_class_dbtable . "` (`class_id`, `class_name`, `class_min_level`, `class_max_level`, `class_armor_type`, `class_hide`) VALUES(12, 'Warrior', 0, 70, 'Chain', '0')");
	$db->sql_query("INSERT INTO `" . $old_class_dbtable . "` (`class_id`, `class_name`, `class_min_level`, `class_max_level`, `class_armor_type`, `class_hide`) VALUES(13, 'Warrior', 40, 70, 'Plate', '1')");

	
?><html><body>
<form action="normalize_gamedb.php" method="post" id="gamedb">
<input type="submit" id="normalize_old_tables" name="normalize_old_tables" value="Normalize Old Tables" />
</form>
</body></html><?php
}

function normalize_dbtable()
{
	global $db;
	global $old_class_dbtable, $new_class_dbtable, $new_armor_dbtable, $new_classarmor_dbtable;

	// Create the tables if they're not there already
	$sql = "CREATE TABLE IF NOT EXISTS `" . $new_class_dbtable . "` (
	  `class_id` smallint(3) unsigned NOT NULL,
	  `class_name` varchar(50) NOT NULL,
	  `class_key` varchar(30) NOT NULL,
	  `class_hide` enum('0','1') NOT NULL DEFAULT '0',
	  PRIMARY KEY (`class_id`)
	)TYPE=InnoDB";
	$db->sql_query($sql);

	$sql = "CREATE TABLE IF NOT EXISTS `" . $new_armor_dbtable . "` (
	  `armor_type_id` smallint(3) unsigned NOT NULL UNIQUE,
	  `armor_type_name` varchar(50) NOT NULL,
	  `armor_type_key` varchar(30) NOT NULL,
	  PRIMARY KEY (`armor_type_id`)
	)TYPE=InnoDB";
	$db->sql_query($sql);

	$sql = "CREATE TABLE IF NOT EXISTS `" . $new_classarmor_dbtable . "` (
	  `class_id` smallint(3) unsigned NOT NULL,
	  `armor_type_id` smallint(3) unsigned NOT NULL,
	  `armor_min_level` smallint(3) NOT NULL DEFAULT '0',
	  `armor_max_level` smallint(3),
	  PRIMARY KEY (`class_id`, `armor_type_id`),
	  INDEX classes (`class_id`),
	  INDEX armor_types (`armor_type_id`)
	)TYPE=InnoDB";
	$db->sql_query($sql);
	
	// Clear out the tables
	$db->sql_query("TRUNCATE TABLE " . $new_class_dbtable);
	$db->sql_query("TRUNCATE TABLE " . $new_armor_dbtable);
	$db->sql_query("TRUNCATE TABLE " . $new_classarmor_dbtable);

	//
	// Start populating the new armor and class tables
	//
	
	$armortype_sql = array();
	$class_sql = array();
	
	// Armor
	$sql = "SELECT DISTINCT `class_armor_type` FROM `" . $old_class_dbtable . "` ORDER BY `c_index`";
	$result = $db->sql_query($sql);

	$armor_type_count = 1;	
	while ($row = $db->sql_fetchrow($result))
	{
		if (strcasecmp($row['class_armor_type'], 'Unknown') == 0)
		{
			$armortype_sql[] = "INSERT INTO `" . $new_armor_dbtable . "` (`armor_type_id`, `armor_type_name`, `armor_type_key`) VALUES ('0', 'None', 'none')";
			continue;
		}
	
		$armortype_sql[] = "INSERT INTO `" . $new_armor_dbtable . "` (
						`armor_type_id`, 
						`armor_type_name`, 
						`armor_type_key`
					) 
		            VALUES (
					    '" . $armor_type_count . "', 
						'" . $row['class_armor_type'] . "', 
						'" . preg_replace("/[^\w0-9]/", "_", strtolower($row['class_armor_type'])) . "'
					)";
		$armor_type_count++;
	}
	$db->sql_freeresult($result);
	
	foreach ($armortype_sql as $sql)
	{
		$db->sql_query($sql);
	}
	
	// Classes
	$sql = "SELECT DISTINCT `class_name` FROM `" . $old_class_dbtable . "` ORDER BY `c_index`";
	$result = $db->sql_query($sql);

	$class_count = 1;	
	while ($row = $db->sql_fetchrow($result))
	{
		// We've already added Unknown.
		if (strcasecmp($row['class_name'], 'Unknown') == 0)
		{
			$class_sql[] = "INSERT INTO `" . $new_class_dbtable . "` (`class_id`, `class_name`, `class_key`) VALUES ('0', 'Unknown', 'unknown')";
			continue;
		}
	
		$class_sql[] = "INSERT INTO `" . $new_class_dbtable . "` (
						`class_id`, 
						`class_name`, 
						`class_key`
					) 
		            VALUES (
					    '" . $class_count . "', 
						'" . $row['class_name'] . "', 
						'" . preg_replace("/[^\w0-9]/", "_", strtolower($row['class_name'])) . "'
					)";
		$class_count++;
	}
	$db->sql_freeresult($result);
	
	foreach ($class_sql as $sql)
	{
		$db->sql_query($sql);
	}
	
	
	// Now we'll do a few table joins to get our class-armor mappings.
	$classarmor_sql = array();
	
	$sql = "SELECT oldclass.`class_min_level`, oldclass.`class_max_level`, at.`armor_type_id` AS new_armor_type_id, class.`class_id` AS new_class_id 
				FROM `" . $old_class_dbtable . "` AS oldclass
				LEFT JOIN `" . $new_class_dbtable . "` AS class ON oldclass.`class_name` = class.`class_name`
				LEFT JOIN `" . $new_armor_dbtable . "` AS at ON oldclass.`class_armor_type` = at.`armor_type_name`";
	$result = $db->sql_query($sql);
	
	// Construct the SQL for the class-armor mappings, and add it to an array of SQL statements to execute later.
	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['new_class_id'] == 0)
		{
#			$classarmor_sql[] = 'INSERT INTO ' . $new_classarmor_dbtable . " (class_id, armor_type_id, armor_min_level, armor_max_level) VALUES ('0', '0', '0', NULL)";
			continue;
		}

		$query = $db->build_query('INSERT', array(
			'class_id'        => $row['new_class_id'],
			'armor_type_id'   => $row['new_armor_type_id'],
			'armor_min_level' => $row['class_min_level'],
			'armor_max_level' => $row['class_max_level'],
		));
		$classarmor_sql[] = 'INSERT INTO ' . $new_classarmor_dbtable . ' ' . $query;
	}
	$db->sql_freeresult($result);


	// To offer more robustness, we're going to make an additional mapping for all classes with the 'None' armor type.
	// NOTE: We're selecting from the NEW class table, so we're only getting unique IDs.
	$sql = "SELECT class.`class_id`, at.`armor_type_id` 
				FROM `" . $new_class_dbtable . "` AS class, `" . $new_armor_dbtable . "` AS at 
				WHERE at.`armor_type_key` = 'none'";
	$result = $db->sql_query($sql);
	
	// Construct the SQL for the class-armor mappings, and add it to an array of SQL statements to execute later.
	while ($row = $db->sql_fetchrow($result))
	{
		$query = $db->build_query('INSERT', array(
			'class_id'        => $row['class_id'],
			'armor_type_id'   => $row['armor_type_id'],
			'armor_min_level' => 0,
			'armor_max_level' => NULL,
		));
		$classarmor_sql[] = 'INSERT INTO ' . $new_classarmor_dbtable . ' ' . $query;
	}
	$db->sql_freeresult($result);


	// Now add all the class-armor mappings to the database
	foreach ($classarmor_sql as $sql)
	{
		$db->sql_query($sql);
	}
	
	// And we're done!
}


function add_armor_type_id(&$class_armor_mappings, $armor_types = array())
{
	foreach ($class_armor_mappings as $item)
	{
		
	}
}

// Main procedure
// --------------
switch($action)
{
	case 'create_old_tables':
		create_dbtable();
		break;
		
	case 'normalize_old_tables':
		normalize_dbtable();
		break;
		
	default:
		the_start();
		break;
}

?>