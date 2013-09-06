<?php
	
	/**
	 * @author prevdev@gmail.com, luaviskang@gmail.com
	 * @2013.07
	 *
	 *
	 * User Class
	 */
	class User {

		static private $userSingleTon;
		static private $masterAdmin;

		private $id;
		private $inputId;
		private $input_id;
		private $userId;
		private $user_id;
		private $nickName;
		private $nick_name;
		private $userName;
		private $user_name;
		private $emailAddress;
		private $email_address;
		private $phoneNumber;
		private $phone_number;
		private $lastLoginedIp;
		private $last_logined_ip;
		private $extraVars;
		private $extra_vars;
		private $groups;

		public function __construct($data) {
			if (is_object($data) || is_array($data)) {
				if (isset($data->id) && isset($data->inputId)){
					foreach ($data as $key => $value) {
						$this->{$key} = $value;
					}
					if (isset($this->groups)) {
						for ($i=0; $i<count($this->groups); $i++) {
							$this->groups[$i]->nameLocale = fetchLocale($this->groups[$i]->nameLocales);
							$this->groups[$i]->name_locale = $this->groups[$i]->nameLocale;
						}
					}
					$this->id = (int) $this->id;
				}
				else
					Context::printWarning('User class is not initialize with User record data');
			}else {
				Context::printWarning('Unknown type of param $data - in User::__construct');
			}
		}

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

		static public function getMasterAdmin() {
			if (isset(self::$masterAdmin)) return self::$masterAdmin;

			$arr = DBHandler::for_table('user_group')
				->select('name')
				->where('is_admin', 1)
				->find_many();

			$arr2 = array();
			for ($i=0; $i<count($arr); $i++) 
				array_push($arr2, $arr[$i]->name);
			
			self::$masterAdmin = $arr2;
			return $arr2;
		}

		public function __get($name) {
			return $this->{$name};
		}

		public function checkGroup($targetGroups) {
			if (is_array($targetGroups)) {
				for ($i=0; $i<count($targetGroups); $i++) {
					if ($targetGroups[$i] == '*')
						return true;

					for ($j=0; $j<count($this->groups); $j++) { 
						if ($targetGroups[$i] == $this->groups[$j]->name || $this->groups[$j]->is_admin)
							return true;
					}
				}
			}else {
				if ($targetGroups == '*')
					return true;
				
				for ($j=0; $j<count($this->groups); $j++) { 
					if ($targetGroups == $this->groups[$j]->name || $this->groups[$j]->is_admin)
						return true;
				}
			}
			return false;
		}
	}
