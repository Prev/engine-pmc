<?php
	
	require_once( 'BoardController.class.php' );
	require_once( 'BoardModel.class.php' );

	class BoardModule extends Module {

		public function init() {
			if (!USE_DATABASE) {
				Context::printErrorPage(array(
					'en' => 'If you want to use database, Use should fix const "USE_DATABASE" to "true"',
					'ko' => '데이터베이스를 사용하려면 상수 "USE_DATABASE"를 "true"로 바꿔야 합니다.'
				));
			}
		}

	}