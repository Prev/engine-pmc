<?php
	
	class IndexController extends Controller {
		
		public function init() {
			$ssoData = SSOHandler::getData();

			$this->view->setProperties(array(
				'userData' => $ssoData->user_data,
				'message' => $message,
				'loggedin' => ($ssoData ? true : false)
			));
		}
		
	}