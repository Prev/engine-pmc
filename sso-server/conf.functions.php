<?php
	
	function printError($message) {
		$obj = new StdClass();
		$obj->result = 'fail';
		$obj->error = new StdClass();
		$obj->error->message = $message;

		echo json_encode($obj);
		exit;
	}

	function execQuery($query) {
		$query = join($GLOBALS['db_prefix'], explode('(#)', $query));
		$result = mysql_query($query, $GLOBALS['db']);

		if ($result === false) return NULL;
		if (mysql_num_rows($result) === NULL) return $result;

		$arr = array();

		while ($fetch = mysql_fetch_object($result))
			array_push($arr, $fetch);
		
		return $arr;
	}

	function execQueryOne($query) {
		$result = execQuery($query);
		if (is_array($result) && count($result) !== 0)
			return $result[0];
		else
			return $result;
	}