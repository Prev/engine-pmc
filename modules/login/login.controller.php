<?php
	
	class LoginModule_Controller extends Controller {
		
		public function procLogin() {
			self::login($_POST['id'], $_POST['pw'], $_POST['auto_login']);
		}
		
		public function procSecureLogin() {
			require ROOT_DIR . '/lib/encryption/RSA.class.php';
			
			$_rsa = new RSA(
				'10001',
				'3c6c9ac18899b33cdfb03503eb81fc9',
				'801d5852519f4382e8faa29ae15222d'
			);
			
			$enc_id = $_POST['enc_id'];
			$enc_pw = $_POST['enc_pw'];
			$check_sum = $_POST['check_sum'];
			
			if (!isset($enc_id) || !isset($enc_pw) || !isset($check_sum)) {
				Context::printErrorPage('Variable Error');
				return;
			}else {
				$real_id = $_rsa->decrypt($enc_id);
				$real_pw = $_rsa->decrypt($enc_pw);
				
				if (md5($real_id . $real_pw) != $check_sum) {
					redirect(getUrlA('result=fail_sec', LOGIN_URL));
					return;
				}
				
				self::login($real_id, $real_pw, $_POST['auto_login']);
			}
		}
		public function procLogout() {
			if (!$_COOKIE['pmc_sess_key']) return;
			
			$sessionKey = $_COOKIE['pmc_sess_key'];
			
			DBHandler::execQuery("DELETE FROM (#)session WHERE session_key='${sessionKey}' LIMIT 1");
			setcookie('pmc_sess_key', '', time()-60, '/', SESSION_DOMAIN);
			unset($_SESSION['pmc_sso_data']);
			
			$next = ($_REQUEST['next'] ? $_REQUEST['next'] : ($_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : getUrl()));
			redirect($next);
		}
		
		private function generateSessionKey() {
			mt_srand(microtime(true) * 100000 + memory_get_usage(true));
			return sha1(uniqid(mt_rand(), true));
		}
		
		private function insertLoginlog($inputId, $succeed, $autoLogin) {
			$ipAdress = $_SERVER['REMOTE_ADDR'];
			$autoLogin = $autoLogin ? 1 : 0;
			$succeed = $succeed ? 1 : 0;
			
			DBHandler::execQuery("INSERT INTO (#)login_log (ip_address, input_id, succeed, auto_login) VALUES ('${ipAdress}', '${inputId}', ${succeed}, ${autoLogin})");
		}
		
		private function login($id, $pw, $autoLogin) {
			$id = escape($id);
			$pw = escape($pw);
			
			$r = DBHandler::execQueryOne("SELECT id,input_id,password,password_salt FROM (#)user WHERE input_id='${id}' LIMIT 1");
			
			// ID does not exist OR password do not match
			if (!$r || ($r->password != hash('sha256', $pw . $r->password_salt))) {
				$this->insertLoginlog($id, false, $autoLogin);
				$this->goBackToLoginPage('result=fail');
			}else {
				do {
					$sessionKey = $this->generateSessionKey();
					$r2 = DBHandler::execQueryOne("SELECT session_key FROM (#)session WHERE session_key='${sessionKey}'");
				}while(count($r2) !== 0);
				
				$expireTime = time() + ($autoLogin ? 604800 : 10800); // auto login: 7day /else: 3hour
				$ipAddr = $_SERVER['REMOTE_ADDR'];
				
				DBHandler::execQuery("INSERT INTO (#)session (session_key, expire_time, ip_address, user_id) VALUES ('${sessionKey}', '".date('Y-m-d H:i:s', $expireTime)."', '${ipAddr}', '{$r->id}')");
				DBHandler::execQuery("UPDATE (#)user SET last_logined_ip='${ipAddr}' WHERE id='{$r->id}'");
					
				$this->insertLoginlog($id, true, $autoLogin);
				setcookie('pmc_sess_key', $sessionKey, ($autoLogin ? $expireTime : 0), '/', SESSION_DOMAIN);
					
				$next = $_REQUEST['next'] ? $_REQUEST['next'] : getUrl();
				redirect($next);
			}
		}
		
		private function goBackToLoginPage($extraVars) {
			redirect(getUrlA($extraVars . '&' . $next, LOGIN_URL));
		}
	}
	
?>