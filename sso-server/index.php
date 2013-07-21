<?php
	
	/*
	 * @ author prevdev@gmail.com
	 * @ 2013.05 ~ 07
	 *
	 *
	 * Single-Sign-On Server of engine P.M.C
	 *
	 */

	define('PMC-SSO', true);
	define('SSO_DIR', dirname(__FILE__));

	require SSO_DIR . '/conf.config.php';
	require SSO_DIR . '/conf.functions.php';
	require SSO_DIR . '/conf.database.php';
	
	$sessKey = mysql_real_escape_string($_GET['sess_key']);
	
	if (!$sessKey || strpos($_SERVER['HTTP_USER_AGENT'], SSO_AGENT_KEY) === false) {
		header('HTTP/1.1 403 Access Denied');
		printError('access denied');
		return;
	}
	
	$sessionData = execQueryOne('
		SELECT * FROM (#)session
		WHERE session_key="'.$sessKey.'"
		AND expire_time > now()
	');
	
	if (!$sessionData) {
		printError('session key does not exist');
		return;
	}
	
	$user_id = $sessionData->user_id;
	$expire_time = $sessionData->expire_time;

	$userData = execQueryOne('
		SELECT * FROM (#)user
		WHERE id="'.$user_id.'"
	');

	if (!$userData) {
		printError('user does not exist');
		return;
	}

	$obj = new StdClass();
	$obj->result = 'success';
	$obj->userData = new StdClass();
	$obj->expireTime = $expire_time;

	foreach ($userData as $key => $value) {
		if ($key === 'password' || $key === 'password_salt') continue;
		if ($key == 'input_id')
			$obj->userData->user_id = $value;

		$obj->userData->{$key} = $value;
	}

	$groupDatas = execQuery("
		SELECT (#)user_group_user.*, (#)user_group.*
		FROM (#)user_group_user, (#)user_group
		WHERE (#)user_group_user.user_id='${user_id}'
		AND (#)user_group.id = (#)user_group_user.group_id
	");

	$obj->userData->groups = array();	
	for ($i=0; $i < count($groupDatas); $i++) {
		$tmp = $groupDatas[$i];
		$tmp->name_locales = json_decode($tmp->name_locales);
		unset($tmp->id);
		unset($tmp->user_id);

		array_push($obj->userData->groups, $tmp);
	}
	
	echo json_encode($obj);