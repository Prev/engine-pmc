<?php
	
	class IndexModule extends ModuleBase {
		
		protected $appendText = '';
		private $ssoData;
		
		public function init() {
			$this->ssoData = SSOHandler::getData();
		}
		
		public function printContent() {
			$message = '"INDEX" 모듈이 성공적으로 로드되었습니다';
			if ($this->appendText)
				$message .= '<br>' . $this->appendText;
			
			Context::set('message', $message);
			Context::set('loggedin', $this->ssoData ? true : false);
			
			if ($this->ssoData) Context::set('lists', $this->ssoData->user_data);
			else Context::set('lists', (object) array('info'=>'NULL'));
			
			$this->execTemplate('welcome');
		}
		
	}