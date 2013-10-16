<?php
	
	function printError($message) {
		$obj = new StdClass();
		$obj->result = 'fail';
		$obj->error = new StdClass();
		$obj->error->message = $message;

		echo json_encode($obj);
		exit;
	}

	function execQuery($query, $fetchType='object') {
		$query = join($GLOBALS['db_prefix'], explode('(#)', $query));
		$result = mysql_query($query, $GLOBALS['db']);

		if (!$result || $result === true) return NULL;
		if (mysql_num_rows($result) === NULL) return $result;

		$arr = array();

		switch ($fetchType) {
			case 'object':
				while ($fetch = mysql_fetch_object($result))
					array_push($arr, $fetch);
				break;
			
			case 'array':
				while ($fetch = mysql_fetch_array($result))
					array_push($arr, $fetch);
				break;

			case 'row' :
				while ($fetch = mysql_fetch_row($result))
					array_push($arr, $fetch);
				break;
		}
		return $arr;
	}

	function execQueryOne($query, $fetchType='object') {
		$result = execQuery($query, $fetchType);
		if (is_array($result) && count($result) !== 0)
			return $result[0];
		else
			return $result;
	}