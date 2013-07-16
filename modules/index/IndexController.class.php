<?php
	
	class IndexController extends Controller {
		
		public function init() {
			$ssoData = SSOHandler::getData();
			$message = '"INDEX" 모듈이 성공적으로 로드되었습니다';
			
			if ($ssoData) $this->view->userData = $ssoData->user_data;
			$this->view->message = $message;
			$this->view->loggedin = ($ssoData ? true : false);
		}
		
	}