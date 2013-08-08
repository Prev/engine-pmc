<?php
	
	class BoardCGIModel extends Model {


		/**
		 * 저장 프로세스
		 */

		public function getBoardInfo($boardId) {
			return DBHandler::for_table('board')
				->where('id', $boardId)
				->find_one();
		}

		public function getArticleTopId($parent_no) {
			$data = DBHandler::for_table('article')
				->select_many('no', 'top_no')
				->where('no', $parent_no)
				->find_one();

			if ($data->no == $data->top_no) return $data->no;
			else return $this->getArticleTopId($data->top_no);	
		}

		public function getArticleOrderKey($parent_no, $top_no) {
			$currentArticleData = DBHandler::for_table('article')
				->select_many('no', 'order_key')
				->where('no', $parent_no)
				->find_one();

			if ($currentArticleData->order_key == NULL) {
				$data = DBHandler::for_table('article')
					->select_many('no', 'order_key')
					->where('top_no', $parent_no)
					->order_by_desc('order_key')
					->find_one();
			}else {
				$data = DBHandler::for_table('article')
					->select_many('no', 'order_key')
					->where('top_no', $top_no)
					->where_like('order_key', $currentArticleData->order_key.'%')
					->order_by_desc('order_key')
					->find_one();

				if ($data->order_key == NULL || $data->order_key == $currentArticleData->order_key)
					return $currentArticleData->order_key . 'AA';
					//$data->order_key = $currentArticleData->order_key . 'AA';
			}

			if ($data->order_key == NULL)
				return 'AA';
			else {
				$orderKey = $data->order_key;
				
				if (substr($orderKey, strlen($orderKey)-1, 1) == 'Z')
					return substr($orderKey . strlen($orderKey)-2) . $this->_getNextAlphabet(substr($orderKey, strlen($orderKey)-1, 1)) . 'A';
				else
					return substr($orderKey, 0, strlen($orderKey)-1) . $this->_getNextAlphabet(substr($orderKey, strlen($orderKey)-1, 1));
			}
		}

		private function _getNextAlphabet($alphabet) {
			$alphabets = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			
			$pos = array_search($alphabet, $alphabets);
			return $alphabets[$pos + 1];
		}



		/**
		 * 수정 프로세스
		 */

		public function getArticleData($articleNo) {
			return DBHandler::for_table('article')
				->where('no', $articleNo)
				->find_one();
		}

		public function getOriginFiles($articleNo) {
			return DBHandler::for_table('article_files')
				->where('article_no', $articleNo)
				->find_many();
		}

		/**
		 * 삭제 프로세스
		 */


		public function getArticleDataAndAdminGroup($articleNo) {
			return DBHandler::for_table('article')
				->select_many('article.*', 'board.name', 'board.admin_group')
				->join('board', array(
					'board.id','=','article.board_id'
				))
				->where('article.no', $articleNo)
				->find_one();
		}

		public function getSibingArticle($topNo, $orderKey) {
			// 형제와 부모 게시글 불러옴

			// 답글이 없을 경우 경우 본인 자신만 출력됨
			// 답글이 있을 경우 원글과 답글들이 출력됨
			
			if (!isset($orderKey)) {
				// order_key 가 NULL인 경우 : 최상단 글
				return DBHandler::for_table('article')
					->select_many('no', 'top_no')
					->where('top_no', $topNo)
					->find_many();

			}else {
				// 답글
				$orderKey = substr($orderKey, 0, strlen($orderKey)-2);

				if ($orderKey == '') {
					return DBHandler::for_table('article')
					->where('top_no', $topNo)
					->find_many();
				}else {
					return DBHandler::for_table('article')
						->where('top_no', $topNo)
						->where_like('order_key', $orderKey . '%')
						->find_many();
				}
			}
		}

		public function getParentArticle($topNo, $orderKey) {
			$orderKey = substr($orderKey, 0, strlen($orderKey)-2);

			if ($orderKey == '') {
				return DBHandler::for_table('article')
					->where('top_no', $topNo)
					->find_one();
			}else {
				return DBHandler::for_table('article')
					->where('top_no', $topNo)
					->where('order_key', $orderKey)
					->find_one();
			}
		}

		public function getChildArticleNo($topNo, $orderKey) {
			if ($orderKey == NULL) {
				return DBHandler::for_table('article')
					->select('no')
					->where('top_no', $topNo)
					->find_many();
			}else {
				return DBHandler::for_table('article')
					->select('no')
					->where('top_no', $topNo)
					->where_like('order_key', $orderKey . '%')
					->find_many();
			}
		}
	}