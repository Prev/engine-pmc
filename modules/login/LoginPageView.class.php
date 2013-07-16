<?php
	
	class LoginPageView extends View {

		var $errorType;
		var $next;

		function dispLoginPage() {
			if (SSOHandler::getData()) {
				redirect(getUrl());
				return;
			}

			switch ($_GET['result']) {
				case 'fail':
				case 'fail_sec':
					$this->errorType = $_GET['result'];
					break;
			}
			
			$this->next = $_GET['next'] ? $_GET['next'] : $_SERVER['HTTP_REFERER'];
			self::execTemplate('login_form');
		}
		
	}