<?php
	
	class LoginModel extends Model {

		public function getUserData($inputId) {
			return DBHandler::for_table('user')
				->select_many('id', 'input_id', 'password', 'password_salt')
				->where('input_id', $inputId)
				->find_one();
		}

		public function getSessionData($sessionKey) {
			return DBHandler::for_table('session')
				->select('session_key')
				->where('session_key', $sessionKey)
				->find_many();
		}

		public function createSession($sessionKey, $expireTime, $keepLogin, $userId) {
			$record = DBHandler::for_table('session')
				->create();
			$record->set(array(
				'session_key' => $sessionKey,
				'expire_time' => date('Y-m-d H:i:s', $expireTime),
				'keep_login' => $keepLogin,
				'ip_address' => $_SERVER['REMOTE_ADDR'],
				'user_id' => $userId
			));

			$record->save();
		}

		public function removeSession($sessionKey) {
			DBHandler::for_table('session')
				->where('session_key', $sessionKey)
				->delete_many();
		}

		public function updateLastLoginedIp($userId) {
			$record = DBHandler::for_table('user')
				->find_one($userId);
			
			$record->set('last_logined_ip', $_SERVER['REMOTE_ADDR']);
			$record->save();
		}

		public function insertIntoLoginlog($inputId, $succeed, $keepLogin) {
			$ipAdress = $_SERVER['REMOTE_ADDR'];
			$keepLogin = $keepLogin ? 1 : 0;
			$succeed = $succeed ? 1 : 0;
			
			$logRecord = DBHandler::for_table('login_log')->create();
			$logRecord->set(array(
				'ip_address' => $ipAdress,
				'input_id' => $inputId,
				'succeed' => $succeed,
				'keep_login' => $keepLogin
			));
			$logRecord->save();

		}
	}