<?php
	
	class LoginPageView extends View {

		var $errorType;
		var $next;

		function dispLoginPage() {
			if (!is_null(User::getCurrent())) {
				redirect(getUrl());
				return;
			}

			if (isset($_GET['result']))
				switch ($_GET['result']) {
					case 'fail':
					case 'fail_sec':
						$this->errorType = $_GET['result'];
						break;
				}
			
			$this->rsaKeys = (object) array(
				'publicKey' => RSA_PUBLIC_KEY,
				'modulus' => RSA_MODULUS
			);
			$this->next = isset($_GET['next']) ? $_GET['next'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL);
			self::execTemplate('login_form');
		}
		
	}