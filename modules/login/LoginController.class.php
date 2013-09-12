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
			$next = $_REQUEST['next'] ? urldecode($_REQUEST['next']) : getUrl();

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
					$this->goBackToLoginPage('result=fail_sec', urldecode($next));
					return;
				}
				if (!isset($_POST['auto_login'])) $_POST['auto_login'] = false;
				$this->login($real_id, $real_pw, evalCheckbox($_POST['auto_login']));
			}
		}
		public function procLogout() {
			if (!isset($_SERVER['HTTP_REFERER'])) return;
			
			if (isset($_COOKIE['pmc_sess_key'])) {
				$sessionKey = $_COOKIE['pmc_sess_key'];
				$this->model->removeSession($sessionKey);

				setcookie('pmc_sess_key', '', time()-60, getServerInfo()->uri, SESSION_DOMAIN);
			}
			unset($_SESSION['pmc_sso_data']);
			setcookie('pmc_logout_key', '0', time()-60);

			redirect(RELATIVE_URL);
		}
		
		private function generateSessionKey() {
			mt_srand(microtime(true) * 100000 + memory_get_usage(true));
			return sha1(uniqid(mt_rand(), true));
		}
		
		private function login($id, $pw, $autoLogin) {
			$next = !empty($_REQUEST['next']) ? urldecode($_REQUEST['next']) : getUrl();

			$userData = $this->model->getUserData($id);

			// ID does not exist OR password do not match
			if (!$userData || ($userData->password != hash('sha256', $pw . $userData->password_salt))) {
				$this->model->insertIntoLoginlog($id, false, $autoLogin);
				$this->goBackToLoginPage('result=fail', urldecode($next));
			}else {
				do {
					$sessionKey = $this->generateSessionKey();
					$sessionData = $this->model->getSessionData($sessionKey);

				}while(count($sessionData) !== 0);

				$expireTime = time() + ($autoLogin ? 604800 : 10800); // auto login: 7day /else: 3hour
				
				$this->model->createSession(
					$sessionKey,
					$expireTime,
					$userData->id
				);

				$this->model->updateLastLoginedIp($userData->id);
				$this->model->insertIntoLoginlog($id, true, $autoLogin);

				setcookie('pmc_sess_key', $sessionKey, ($autoLogin ? $expireTime : 0), getServerInfo()->uri, SESSION_DOMAIN);
				
				redirect($next);
			}
		}
		
		private function goBackToLoginPage($extraVars, $next) {
			redirect(getUrlA($extraVars . '&next=' . $next, LOGIN_URL));
		}
	}
	
?>