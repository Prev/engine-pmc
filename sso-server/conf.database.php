<?php
	
	global $db, $db_prefix;

	$db_host = 'localhost';
	$db_username = '';
	$db_password = '';
	$db_database = '';
	$db_prefix = '';


	if (empty($db_username) && file_exists('../config/database.php')) {
		require '../config/database.php';
		$dbInfo = getDBInfo();

		$db_host = $dbInfo->host;
		$db_username = $dbInfo->username;
		$db_password = $dbInfo->password;
		$db_database = $dbInfo->database_name;
		$db_prefix = $dbInfo->prefix;
	}
	

	$db = @mysql_connect($db_host, $db_username, $db_password);
	
	$e = mysql_error();
	if (!empty($e)) printError('db info does not exist');

	mysql_select_db($db_database, $db);
	mysql_query('set names utf8', $db);