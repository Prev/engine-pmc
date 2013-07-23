<?php
	
	/**
	 * @author prevdev@gmail.com
	 * @2013.06
	 *
	 *
	 * DBHandler Class
	 * Control all about database and excute queries
	 */
	
	class DBHandler extends ORM {
		
		static public $db;
		static public $type;
		static public $prefix;

		// @override
		static public function for_table($table_name, $connection_name = self::DEFAULT_CONNECTION, $appendPrefix=true) {
			if ($appendPrefix) $table_name = self::$prefix . $table_name;
			self::_setup_db($connection_name);
			return new self($table_name, array(), $connection_name);
		}

		// @override
		protected function _quote_identifier($identifier) {
			$parts = explode('.', $identifier);
			if (count($parts) >= 2) $parts[0] = self::$prefix . $parts[0];
			$parts = array_map(array($this, '_quote_identifier_part'), $parts);
			return join('.', $parts);
		}

		// @override
		protected function _add_simple_condition($type, $column_name, $separator, $value) {
			if (count($this->_join_sources) > 0 && strpos($column_name, '.') === false)
				$column_name = self::$prefix . "{$this->_table_name}.{$column_name}";
			$column_name = $this->_quote_identifier($column_name);
			return $this->_add_condition($type, "{$column_name} {$separator} ?", $value);
		}

		// @override
		protected function _add_join_source($join_operator, $table, $constraint, $table_alias=null) {
			return parent::_add_join_source($join_operator, self::$prefix . $table, $constraint, $table_alias);
		}

		// @override
		protected function _create_instance_from_row($row) {
			$instance = self::for_table($this->_table_name, $this->_connection_name, false);
			$instance->use_id_column($this->_instance_id_column);
			$instance->hydrate($row);
			return $instance;
		}

		// @override
		public function limit($args1, $args2=NULL) {
			if (isset($args2)) {
				$this->_offset = $args1;
				$this->_limit = $args2;
			}else
				$this->_limit = $args1;

			return $this;
		}
		public function getQuery() {
			$query = parent::_build_select();
			for ($i=0; $i < count($this->_values); $i++)
				$query = preg_replace('/(\?)/', $this->_values[$i], $query, 1);
			return $query;
		}
		public function getData() {
			if (is_array($this->_data))
				return (object) $this->_data;
			return $this->_data;
		}


		static public function init($info) {
			self::$type = $info->type;
			self::$prefix = $info->prefix;

			self::configure('mysql:host=' . $info->host . ';dbname=' . $info->database_name);
			self::configure('username', $info->username);
			self::configure('password', $info->password);

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
		
		static public function rawQuery($query, $fetchType='object') {
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
					if (!isset($result->num_rows)) return $result;
					
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
					if ($result === true) return $result;
					
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
		
		static public function rawQueryOne($query, $fetchType='object') {
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
			switch(self::$type) {
				case 'mysqli':
					return self::$db->real_escape_string($string);
				break;
				case 'mysql' : 
				default :
					return mysql_real_escape_string($string);
			 }
		}
	}
	