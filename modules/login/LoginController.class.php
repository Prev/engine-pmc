<?php
	
	class LoginController extends Controller {

		public function procLogin() {
			if (!isset($_POST['auto_login'])) $_POST['auto_login'] = false;
			$this->login($_POST['id'], $_POST['pw'], evalCheckbox($_POST['auto_login']));
		}
		
		public function procSecureLogin() {
			require ROOT_DIR . '/lib/others/lib.rsa.php';
			
			$enc_id = $_POST['enc_id'];
			$enc_pw = $_POST['enc_pw'];
			$check_sum = $_POST['check_sum'];
			$next = $_REQUEST['next'] ? $_REQUEST['next'] : getUrl();

			$_rsa = new RSA(
				RSA_PUBLIC_KEY,
				RSA_PRIVATE_KEY,
				RSA_MODULUS
			);

			if (!isset($enc_id) || !isset($enc_pw) || !isset($check_sum)) {
				Context::printErrorPage('Variable Error');
				return;
			}else {
				$real_id = $_rsa->decrypt($enc_id);
				$real_pw = $_rsa->decrypt($enc_pw);
				
				if (md5($real_id . $real_pw) != $check_sum) {
					$this->goBackToLoginPage('result=fail_sec', $next);
					return;
				}
				if (!isset($_POST['auto_login'])) $_POST['auto_login'] = false;
				$this->login($real_id, $real_pw, evalCheckbox($_POST['auto_login']));
			}
		}
		public function procLogout() {
			if (!isset($_COOKIE['pmc_sess_key'])) return;
			
			$sessionKey = $_COOKIE['pmc_sess_key'];
			
			DBHandler::for_table('session')
				->where('session_key', $sessionKey)
				->delete_many();

			setcookie('pmc_sess_key', '', time()-60, getServerInfo()->uri, SESSION_DOMAIN);
			unset($_SESSION['pmc_sso_data']);
			
			$next = (isset($_REQUEST['next']) ? $_REQUEST['next'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : getUrl()));
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
			
			$logRecord = DBHandler::for_table('login_log')->create();
			$logRecord->set(array(
				'ip_address' => $ipAdress,
				'input_id' => $inputId,
				'succeed' => $succeed,
				'auto_login' => $autoLogin
			));
			$logRecord->save();

		}
		
		private function login($id, $pw, $autoLogin) {
			$next = !empty($_REQUEST['next']) ? $_REQUEST['next'] : getUrl();

			$r = DBHandler::for_table('user')
				->select_many('id', 'input_id', 'password', 'password_salt')
				->where('input_id', $id)
				->find_one();

			// ID does not exist OR password do not match
			if (!$r || ($r->password != hash('sha256', $pw . $r->password_salt))) {
				$this->insertLoginlog($id, false, $autoLogin);
				$this->goBackToLoginPage('result=fail' ,$next);
			}else {
				do {
					$sessionKey = $this->generateSessionKey();
					$r2 = DBHandler::for_table('session')
						->select('session_key')
						->where('session_key', $sessionKey)
						->find_many();
				}while(count($r2) !== 0);

				$expireTime = time() + ($autoLogin ? 604800 : 10800); // auto login: 7day /else: 3hour
				$ipAddr = $_SERVER['REMOTE_ADDR'];

				$newSessionRecord = DBHandler::for_table('session')
					->create();

				$newSessionRecord->set(array(
					'session_key' => $sessionKey,
					'expire_time' => date('Y-m-d H:i:s', $expireTime),
					'ip_address' => $ipAddr,
					'user_id' => $r->id
				));

				$newSessionRecord->save();

				$user = DBHandler::for_table('user')
					->find_one($r->id);

				$user->set('last_logined_ip', $ipAddr);
				$user->save();

				$this->insertLoginlog($id, true, $autoLogin);
				setcookie('pmc_sess_key', $sessionKey, ($autoLogin ? $expireTime : 0), getServerInfo()->uri, SESSION_DOMAIN);
				
				redirect($next);

			}
		}
		
		private function goBackToLoginPage($extraVars, $next) {
			redirect(getUrlA($extraVars . '&next=' . $next, LOGIN_URL));
		}
	}
	
?>