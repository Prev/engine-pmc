<?php
	
	/**
	 * @ author luaviskang@gmail.com
	 * @ 2013.07
	 *
	 *
	 * User Class
	 */
	class User {
		static private $userSingleTon;

		private $user;
		private $group;

		static public function getCurrentUser() {
			return self::$userSingleTon;
		}

		static public function init($user = null, $group = null) {
			if(!isset($user)||!isset($group))
				self::$userSingleTon = isset($_SESSION['pmc_user']) ? $_SESSION['pmc_user'] : null;
			else
				self::$userSingleTon = new User($user, $group);
		}

		public function __construct($user, $group) {
			if( isset($user->user_id) &&
				isset($user->nick_name) &&
				isset($user->user_name) &&
				isset($user->email_address) &&
				isset($user->phone_number)) {
				$this->user = $user;
				$this->group = $group;
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