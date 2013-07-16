<?php
	
	/**
	 * @ author prevdev@gmail.com
	 * @ 2013.06
	 *
	 *
	 * SSOHandler Class
	 * get sso data and set session
	 */
	
	class SSOHandler extends Handler {
		
		static public function getData($redirect=false) {
			if (!$_COOKIE['pmc_sess_key']) {
				if ($redirect) redirect(LOGIN_URL . '&next=' . REAL_URI);
				unset($_SESSION['pmc_sso_data']);
				return NULL;
				
			}else if ($_SESSION['pmc_sso_data'] && (time() < strtotime($_SESSION['pmc_sso_data']->expire_time))) {
				return $_SESSION['pmc_sso_data'];
			}else {
				$urlData = getURLData(SSO_URL . '?sses_key=' . $_COOKIE['pmc_sess_key'], 'PMC-SSO Connection');
				if (!$urlData) {
					Context::printErrorPage(array(
						'en' => 'cannot load sso data',
						'kr' => 'SSO 데이터를 불러올 수 없습니다'
					));
					unset($_SESSION['pmc_sso_data']);
					return NULL;
				}
				
				$urlData = json_decode($urlData);
				if ($urlData->error) {
					unset($_SESSION['pmc_sso_data']);
					return NULL;
				}
				$_SESSION['pmc_sso_data'] = $urlData;
				return $_SESSION['pmc_sso_data'];
			}
		}
		
		
	}
	