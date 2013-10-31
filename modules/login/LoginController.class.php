<?php
	
	class LoginController extends Controller {

		public function procLogin() {
			if (!isset($_POST['keep_login'])) $_POST['keep_login'] = false;
			$this->login($_POST['id'], $_POST['pw'], evalCheckbox($_POST['keep_login']));
		}
		
		public function procSecureLogin() {
			require_once( ROOT_DIR . '/lib/others/lib.rsa.php' );
			
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
				if (!isset($_POST['keep_login'])) $_POST['keep_login'] = false;
				$this->login($real_id, $real_pw, evalCheckbox($_POST['keep_login']));
			}
		}
		public function procLogout() {
			setCookie2('pmc_logout', 1);
			redirect(getUrl('login', 'procLogout2', 'next='.getBackUrl()));
		}
		
		public function procLogout2() {
			if (!$_COOKIE['pmc_logout']) return;
			
			setCookie2('pmc_logout', 0, time()-60);
			
			if (isset($_COOKIE[SSO_COOKIE_NAME])) {
				$this->model->removeSession($_COOKIE[SSO_COOKIE_NAME]);
				setCookie2(SSO_COOKIE_NAME, '', time()-60);
				setCookie2(SSO_COOKIE_NAME.'_synchash', '', time()-60);
			}
			unset($_SESSION[SSO_SESSION_NAME]);

			redirect($_GET['next']);
		}


		private function generateSessionKey() {
			mt_srand(microtime(true) * 100000 + memory_get_usage(true));
			return sha1(uniqid(mt_rand(), true));
		}
		
		private function login($id, $pw, $keepLogin) {
			if (!$id || !$pw) return;

			$next = !empty($_REQUEST['next']) ? urldecode($_REQUEST['next']) : getUrl();

			$userData = $this->model->getUserData($id);

			// ID does not exist OR password do not match
			if (!$userData || ($userData->password != hash('sha256', $pw . $userData->password_salt))) {
				$this->model->insertIntoLoginlog($id, false, $keepLogin);
				$this->goBackToLoginPage('result=fail', urldecode($next));
				return;
				
			}else {
				do {
					$sessionKey = $this->generateSessionKey();
					$sessionData = $this->model->getSessionData($sessionKey);

				}while(count($sessionData) !== 0);

				$expireTime = time() + ($keepLogin ? 604800 : 10800); // auto login: 7day /else: 3hour
				
				$this->model->createSession(
					$sessionKey,
					$expireTime,
					$keepLogin,
					$userData->id
				);

				$this->model->updateLastLoginedIp($userData->id);
				$this->model->insertIntoLoginlog($id, true, $keepLogin);

				setCookie2(SSO_COOKIE_NAME, $sessionKey, ($keepLogin ? $expireTime : 0));
				
				redirect($next);
			}
		}
		
		private function goBackToLoginPage($extraVars, $next) {
			redirect(getUrlA($extraVars . '&next=' . $next, LOGIN_URL));
		}
	}
	
?>