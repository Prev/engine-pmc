<?php
	
	class TestModule_View extends View {

		public function init() {
			
		}

		public function dispDefault() {
			echo '테스트 모듈이 실행되었습니다.';
			getContent('index', 'dispCredit');
		}

		public function dispBlank() {
			echo 'blank layout is loaded;';
		}
		
	}