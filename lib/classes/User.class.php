<?php
	
	/**
	 * @author luaviskang@gmail.com, prevdev@gmail.com
	 * @2013.07
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
		public $groups;

		static public function getCurrent() {
			return self::$userSingleTon;
		}

		static public function initCurrent() {
			if (isset($_SESSION['pmc_sso_data'])) {
				$ssoData = (object)$_SESSION['pmc_sso_data'];
				
				// expired
				if (strtotime($ssoData->expireTime) < time()) {
					unset($_SESSION['pmc_sso_data']);
					self::$userSingleTon = NULL;
					return;
				}
				
				$userData = $ssoData->userData;
				self::$userSingleTon = new User($userData);
			}else
				self::$userSingleTon = NULL;
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

		public function checkGroup($groups) {
			if (is_array($groups)) {
				for ($i=0; $i<count($groups); $i++) {
					for ($j=0; $j<count($this->groups); $j++) { 
						if ($groups[$i] == $this->groups[$j]->name)
							return true;
					}
				}
			}else {
				for ($j=0; $j<count($this->groups); $j++) { 
					if ($groups == $this->groups[$j]->name)
						return true;
				}
			}
			return false;
		}
	}