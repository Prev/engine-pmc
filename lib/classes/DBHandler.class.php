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

		static public $primary_keys;
		static private $db_info;

		// 주로 table prefix 문제를 해결하기 위해 override함
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

		// @override
		// PRIMARY_KEY 가 'id' 가 아닐때 update문이 안먹는 버그 픽스
		protected function __construct($table_name, $data = array(), $connection_name = parent::DEFAULT_CONNECTION) {
			$this->_table_name = $table_name;
			$this->_data = $data;

			$this->_connection_name = $connection_name;
			parent::_setup_db_config($connection_name);

			if (isset(self::$primary_keys->{$table_name}))
				parent::$_config[$connection_name]['id_column'] = self::$primary_keys->{$table_name};
			else {
				$primaryRow = DBHandler::get_db($connection_name)
					->query('SHOW KEYS FROM '.$table_name.' WHERE Key_name =  "PRIMARY"');
				
				foreach ($primaryRow as $row) {
					self::$primary_keys->{$table_name} = $row['Column_name'];
					if ($row['Column_name'] != 'id')
						parent::$_config[$connection_name]['id_column'] = $row['Column_name'];
				}
			}
		}
		// @override
		// \" -> "
		protected function _run() {
			$rows = parent::_run();
			if ($rows) {
				for ($i=0; $i<count($rows); $i++) {
					if (is_array($rows[$i]))
						foreach ($rows[$i] as $key => $value)
							if (strpos($value, '\\"') !== false)
								$rows[$i][$key] = join('"', explode('\\"', $value));
					else if (is_string($rows))
						if (strpos($rows[$i], '\\"') !== false)
							$rows[$i] = join('"', explode('\\"', $rows[$i]));
					else
						ErrorLogger::log('bug in DBHandler::_run');

				}		
			}
			return $rows;
        }

        public function getConfig() {
        	return parent::$_config;
        }

		public function getQuery() {
			$query = parent::_build_select();
			for ($i=0; $i < count($this->_values); $i++) {
				$value = $this->_values[$i];
				$value = join('\\"', explode('"', $value));
				$value = '"' . $value . '"';

				$query = preg_replace('/(\?)/', $value, $query, 1);
			}
			return $query;
		}

		public function dumpQuery() {
			var_dump2($this->getQuery());
			return $this;
		}

		public function getData() {
			if (is_array($this->_data))
				return (object) $this->_data;
			return $this->_data;
		}



		static public function init($info) {
			self::$db_info = $info;

			self::$type = $info->type;
			self::$prefix = $info->prefix;
			self::$primary_keys = new StdClass();

			$charset = join('', explode('-', TEXT_ENCODING));

			@self::$db = mysql_connect($info->host, $info->username, $info->password);
			$e = mysql_error();
			
			if (!$e) {
				$r = mysql_select_db($info->database_name,  self::$db);
				mysql_query('set names ' . $charset);
				
				if (!$r)
					$e = 'Cannot connect to database "'.$info->database_name.'"';
			}
			
			if ($e) Context::printErrorPage(array(
				'en' => 'Fail connecting database - ' . $e,
				'kr' => '데이터베이스에 연결에 실패하였습니다 - ' . $e
			));

			self::configure('mysql:host=' . $info->host . ';dbname=' . $info->database_name);
			self::configure('username', $info->username);
			self::configure('password', $info->password);
		}


		static public function set_database($dabase_name, $connection_name=parent::DEFAULT_CONNECTION) {
			$info = self::$db_info;
			
			parent::$_db[$connection_name] = array();
			self::configure('mysql:host=' . $info->host . ';dbname=' . $dabase_name, NULL, $connection_name);
			self::configure('username', $info->username, $connection_name);
			self::configure('password', $info->password, $connection_name);
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
	