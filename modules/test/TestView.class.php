<?php
	
	class TestView extends View {

		public function dispDefault() {
			echo '테스트 모듈이 실행되었습니다.<br><br>';
			echo 'getContent("index", "dispCredit"):<br><br>';
			getContent('index', 'dispCredit');
		}

		public function dispLessc() {
			$this->execTemplate('test_less');
		}
		
	}