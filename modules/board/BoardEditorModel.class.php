<?php
	
	class BoardEditorModel extends Model {

		public function getBoardLists() {
			$arr = DBHandler::for_table('board')
				->find_many();

			for ($i=0; $i<count($arr); $i++) {
				$arr[$i] = $arr[$i]->getData();
				
				$me = User::getCurrent();
				if (!$me || (isset($arr[$i]->writable_group) && !$me->checkGroup(json_decode($arr[$i]->writable_group)))) {
					unset($arr[$i]);
					continue;
				}
				
				$arr[$i]->name_locale = fetchLocale($arr[$i]->name_locales);
			}
			return $arr;
		}


		public function getArticleTopId($parent_no) {
			$data = DBHandler::for_table('article')
				->select_many('no', 'top_no')
				->where('no', $parent_no)
				->find_one();

			if ($data->no == $data->top_no) return $data->no;
			else return $this->getArticleTopId($data->top_no);	
		}

		public function getArticleOrderKey($parent_no) {
			$data = DBHandler::for_table('article')
				->select_many('no', 'order_key')
				->where('no', $parent_no)
				->order_by_asc('order_key')
				->find_one();

			if ($data->order_key == NULL)
				return 'AA';
			else {
				$orderKey = $data->order_key;
				
				if (substr($orderKey, 1, 1) == 'Z')
					return $this->_getNextAlphabet(substr($orderKey, 0, 1)) . 'A';
				else
					return substr($orderKey, 0, 1) . $this->_getNextAlphabet(substr($orderKey, 1, 1));
			}
		}

		private function _getNextAlphabet($alphabet) {
			$alphabets = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			
			$pos = strpos($alphabets, $alphabet);
			return $alphabets[$pos + 1];
		}
	}