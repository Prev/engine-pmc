<?php
	
	class ORMExtended extends ORM {
		
		static public function open($host, $user, $password, $db) {
			self::configure('mysql:host=' . $host . ';dbname=' . $db);
			self::configure('username', $user);
			self::configure('password', $password);
		}
		
		static public function for_table($tableName, $connection = self::DEFAULT_CONNECTION) {
			$tableName = DBHandler::$prefix . $tableName;
			return parent::for_table($tableName, $connection);
		}

	}