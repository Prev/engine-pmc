<?php
	
	class IndexController extends Controller {
		
		public function init() {
			$ssoData = SSOHandler::getData();
			if (!isset($ssoData)) {
				$ssoData = new StdClass();
				$ssoData->user_data = NULL;
			}

			$this->view->setProperties(array(
				'userData' => $ssoData->user_data,
				'loggedin' => (isset($ssoData->user_data) ? true : false)
			));
		}
		
	}