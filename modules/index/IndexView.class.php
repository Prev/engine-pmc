<?php
	
	class IndexView extends View {
		
		var $user;
		var $loggedin;
		
		public function dispDefault() {
			$this->execTemplate('welcome');
		}
		
	}