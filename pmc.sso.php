<?php
	
	header('Content-Type: application/json; charset=UTF-8');
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Pragma: no-cache');
	
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
	header('Access-Control-Allow-Headers: *');
	
	define('ROOT_DIR', dirname(__FILE__));
	
	if (is_file('./config/database.php')) require './config/database.php';
	else if (is_file('sso.config.database.php')) require 'pmc.sso.conf-database.php';
	else {
		printError('NEED "/config/database.php" OR pmc.sso.conf-database.php');
	}
	
	function printError($message) {
		$obj = new StdClass();
		$obj->result = 'fail';
		$obj->error = new StdClass();
		$obj->error->message = $message;
		
		echo json_encode($obj);
		exit;
	}
	
	$db_info = getDBInfo();
	if (!$db_info) printError('db info does not exist');
	
	
	$db = mysql_connect($db_info->host, $db_info->username, $db_info->password);
	mysql_select_db($db_info->database_name, $db);
	mysql_query('set names utf8', $db);
	
	$sses_key = mysql_real_escape_string($_GET['sses_key']);
	$prefix = $db_info->prefix;
	
	if (!$sses_key || strpos($_SERVER['HTTP_USER_AGENT'], 'PMC-SSO') === false) {
		header('HTTP/1.1 403 Access Denied');
		printError('access denied');
		return;
	}
		
	$queryResult = mysql_query("SELECT * FROM ${prefix}session WHERE session_key='$sses_key' AND expire_time > now()", $db);
	if (!$queryResult || mysql_num_rows($queryResult) == 0) printError('session key does not exist');
	else {
		$row = mysql_fetch_object($queryResult);
		
		$user_id = $row->user_id;
		$queryResult = mysql_query("SELECT * FROM ${prefix}user WHERE id='$user_id'", $db);
		
		if (!$queryResult || mysql_num_rows($queryResult) == 0) printError('user does not exist');
		else {
			$obj = new StdClass();
			$obj->result = 'success';
			$obj->user_data = new StdClass();
			$obj->expire_time = $row->expire_time;
			
			$temp = mysql_fetch_object($queryResult);
			foreach ($temp as $key => $value) {
				if ($key === 'id' || $key === 'password' || $key === 'password_salt') continue;
				if ($key == 'input_id')
					$obj->user_data->user_id = $value;
				
				$obj->user_data->{$key} = $value;
			}
			
			$queryResult = mysql_query("
				SELECT ${prefix}user_group_user.*, ${prefix}user_group.*
				FROM ${prefix}user_group_user, ${prefix}user_group
				WHERE ${prefix}user_group_user.user_id='${user_id}'
					AND ${prefix}user_group.id = ${prefix}user_group_user.group_id
			", $db);

			$obj->user_data->groups = array();
			while ($row = mysql_fetch_object($queryResult))
				array_push($obj->user_data->groups, $row);


			mysql_query("DELETE FROM ${prefix}session WHERE expire_time < now()", $db);
			echo json_encode($obj);
		}
	}
	
	
	
	