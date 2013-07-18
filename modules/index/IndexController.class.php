<?php
	
	class IndexController extends Controller {
		
		public function init() {
			$user = User::getCurrentUser();

			$this->view->setProperties(array(
				'user' => $user,
				'loggedin' => (!is_null($user) ? true : false)
			));
		}
	}