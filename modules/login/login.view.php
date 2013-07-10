<?php
	
	class LoginModule_View extends LoginModule {
		
		function dispLoginPage() {
			switch ($_GET['result']) {
				case 'fail':
					Context::set('errorMessage', '<b>올바르지 않은 아이디 또는 비밀번호입니다.</b><br>등록되지 않은 아이디이거나, 아이디 또는 비밀번호를 잘못 입력하셨습니다.');
					break;
				
				case 'fail_sec':
					Context::set('errorMessage', '<b>보안로그인에 오류가 발생했습니다.</b><br>자주 이 메시지가 발생하는 경우에는 "보안로그인"을 해제 한 후 로그인 해 주십시오.');
					break;
			}
			
			Context::set('next', $_GET['next'] ? $_GET['next'] : $_SERVER['HTTP_REFERER']);
			self::execTemplate('login_form');
		}
		
	}