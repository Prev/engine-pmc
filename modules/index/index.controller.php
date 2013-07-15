<?php
	
	class IndexModule_Controller extends Controller {
		
		public function init() {
			$this->ssoData = SSOHandler::getData();
			
			$message = '"INDEX" 모듈이 성공적으로 로드되었습니다';
			
			if ($this->ssoData)
				$this->view->userData = $this->ssoData->user_data;
			
			$this->view->message = $message;
			$this->view->loggedin = ($this->ssoData ? true : false);
		}
		
	}