<?php
	
	/**
	 * @ author luaviskang@gmail.com
	 * @ 2013.07
	 *
	 *
	 * User Class
	 */
	class User {
		private $user;

		public function __construct($user) {
			if($user instanceof DBHandler) {
				if(
					isset($user->id) && 
					isset($user->input_id) &&
					isset($user->password) &&
					isset($user->password_salt) &&
					isset($user->nick_name) &&
					isset($user->user_name) &&
					isset($user->email_address) &&
					isset($user->phone_number)
					) {
					$this->user = $user;
				}
			}
			else {
				Context::printWarning('User class is not initialize with User record data');
			}
		}

		public function __set($name, $value) {
			$this->user->set($name, $value);
			$this->user->save();
			$this->user->{$name} = $value;
		}

		public function __get($name) {			
			return $this->user->{$name};
		}

		public function __isset($name) {
			return isset($this->user{$name});
		}

		public function __unset($name) {
			unset($this->user->{$name});
		}
	}