<?php
	
	class IndexView extends View {
		
		var $userData;
		var $message;
		var $loggedin;
		
		public function dispDefault() {
			$this->execTemplate('welcome');
		}
		
	}