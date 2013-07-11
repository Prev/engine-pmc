<?php
	
	/**
	 * @ author prevdev@gmail.com
	 * @ 2013.06
	 *
	 *
	 * DBHandler Class
	 * Control all about database and excute queries
	 */
	
	class DBHandler extends Handler {
		
		static public $db;
		static public $type;
		static public $prefix;
		
		static public function init($info) {
			self::$type = $info->type;
			self::$prefix = $info->prefix;
			
			$charset = join('', explode('-', TEXT_ENCODING));
			
			switch (self::$type) {
				case 'mysqli':
					@self::$db = new MySQLi($info->host, $info->username, $info->password, $info->database_name);
					$e = mysqli_connect_error();
					
					if (!$e) self::$db->set_charset($charset);
					
					break;
					
				case 'mysql':
					@self::$db = mysql_connect($info->host, $info->username, $info->password);
					$e = mysql_error();
					
					if (!$e) {
						mysql_select_db($info->database_name,  self::$db);
						mysql_query('set names ' . $charset);
					}
					break;
					
				default :
					Context::printErrorPage(array(
						'en' => 'database type is not supported',
						'kr' => '지원되지 않는 데이터베이스 종류입니다'
					));
					break;
			}
			
			if ($e) Context::printErrorPage(array(
				'en' => 'Fail connecting database - ' . $e,
				'kr' => '데이터베이스에 연결에 실패하였습니다 - ' . $e
			));
		}
		
		static public function execQuery($query, $fetchType='object') {
			$arr = array();
			$query = join(DBHandler::$prefix, explode('(#)', $query));
			$backtrace = debug_backtrace();
			
			$backtrace_path = getFilePathClear($backtrace[0]['file']);
			
			switch (self::$type) {
				case 'mysqli':
					$result = self::$db->query($query);
					
					if ($result === false) {
						Context::printWarning(array(
							'en' => 'Fail to excute query "<b>'.$query."</b>\" in <b>${backtrace_path}</b> on line <b>{$backtrace[0]['line']}</b>",
							'kr' => '쿼리 실행에 실패했습니다 "<b>'.$query."</b>\" in <b>${backtrace_path}</b> on line <b>{$backtrace[0]['line']}</b>"
						));
						return NULL;
					}
					if ($result->num_rows === NULL) return $result;
					
					switch ($fetchType) {
						case 'object':
							while ($fetch = $result->fetch_object())
								array_push($arr, $fetch);
							break;
						
						case 'array':
							while ($fetch = $result->fetch_array())
								array_push($arr, $fetch);
							break;
							
						default:
							Context::printWarning(array(
								'en' => 'unknown fetchType in DBHandler::execQuery method',
								'kr' => 'DBHandler::execQuery 메소드에서 알수없는 fetchType 을 입력했습니다'
							));
							return;
						
					}
					break;
					
				case 'mysql':
					$result = mysql_query($query, self::$db);
					
					if ($result === false) {
						Context::printWarning(array(
							'en' => 'Fail to excute query "<b>'.$query."</b>\" in <b>${backtrace_path}</b> on line <b>{$backtrace[0]['line']}</b>",
							'kr' => '쿼리 실행에 실패했습니다 "<b>'.$query."</b>\" in <b>${backtrace_path}</b> on line <b>{$backtrace[0]['line']}</b>"
						));
						return NULL;
					}
					if (mysql_num_rows($result) === NULL) return $result;
					
					switch ($fetchType) {
						case 'object':
							while ($fetch = mysql_fetch_object($result))
								array_push($arr, $fetch);
							break;
						
						case 'array':
							while ($fetch = mysql_fetch_array($result))
								array_push($arr, $fetch);
							break;
							
						default:
							Context::printWarning(array(
								'en' => 'unknown fetchType in DBHandler::execQuery method',
								'kr' => 'DBHandler::execQuery 메소드에서 알수없는 fetchType 을 입력했습니다'
							));
							return;
						
					}
					break;
			}
			return $arr;
		}
		
		static public function execQueryOne($query, $fetchType='object') {
			$result = self::execQuery($query, $fetchType);
			if (is_array($result) && count($result) !== 0)
				return $result[0];
			else
				return $result;
		}
		
		static public function getInsertId() {
			return self::$db->$insert_id;
		}
		
		static public function escapeString($string) {
			return self::$db->real_escape_string($string);
		}
	}
	