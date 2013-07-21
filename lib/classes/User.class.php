<?php
	
	/**
	 * @ author luaviskang@gmail.com, prevdev@gmail.com
	 * @ 2013.07
	 *
	 *
	 * User Class
	 */
	class User {

		static private $userSingleTon;

		public $id;
		public $input_id;
		public $nick_name;
		public $user_name;
		public $email_address;
		public $phone_number;
		public $permission;
		public $last_logined_ip;
		public $extra_vars;
		public $group;

		static public function getCurrent() {
			return self::$userSingleTon;
		}

		static public function initCurrent($data = NULL) {
			if (!isset($data))
				self::$userSingleTon = isset($_SESSION['pmc_user']) ?
					new User((object)$_SESSION['pmc_user']) :
					NULL;
			else
				self::$userSingleTon = new User($data);
		}

		public function __construct($data) {
			if (isset($data->input_id) &&
				isset($data->nick_name) &&
				isset($data->user_name) &&
				isset($data->email_address) &&
				isset($data->phone_number)
			){
				foreach ($data as $key => $value) {
					$this->{$key} = $value;
				}
			}
			else {
				Context::printWarning('User class is not initialize with User record data');
			}
		}

		public function __get($name) {			
			$ret = $this->user->{$name};
			return isset($ret) ? $ret : $this->group->{$name};
		}

		public function __isset($name) {
			return isset($this->user{$name}) || isset($this->group->{$name});
		}
	}