<?php

/*
	$eqdkp_root_path = './../';
	$dbms = 'mysql';

    $db_structure_file = $eqdkp_root_path . 'dbal/structure/' . $dbms . '_structure.sql';
    $db_data_file      = $eqdkp_root_path . 'dbal/structure/' . $dbms . '_data.sql';

    // Parse structure file and create database tables
    $sql = @fread(@fopen($db_structure_file, 'r'), @filesize($db_structure_file));

	echo var_dump($sql);
*/

	$test = fopen('../install/schemas/mysql_structure.sql', 'r');
	
	echo var_dump($test);
	