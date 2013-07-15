<?php
	
	class IndexModule_View extends View {
		
		var $userData;
		var $message;
		var $loggedin;

		public function init() {
			
		}

		public function dispDefault() {
			$this->execTemplate('welcome');
		}

		function dispCredit () {
			echo 'Credit: prevdev@gmail.com';
			$this->dispDefault();
		}
		
	}