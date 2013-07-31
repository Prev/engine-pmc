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

		private $id;
		private $inputId;
		private $userId;
		private $nickName;
		private $userName;
		private $emailAddress;
		private $phoneNumber;
		private $lastLoginedIp;
		private $extraVars;
		private $groups;

		public function __construct($data) {
			if (is_object($data) || is_array($data)) {
				if (isset($data->id) && isset($data->inputId)){
					foreach ($data as $key => $value) {
						$this->{$key} = $value;
					}
					if (isset($this->groups)) {
						for ($i=0; $i<count($this->groups); $i++) 
							$this->groups[$i]->nameLocale = fetchLocale($this->groups[$i]->nameLocales);
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

		public function __get($name) {
			return $this->{$name};
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
