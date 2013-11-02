<?php
	
	class BoardCGIModel extends BoardModel {


		/**
		 * 저장 프로세스
		 */

		public function getBoardInfo($boardId) {
			return DBHandler::for_table('board')
				->where('id', $boardId)
				->find_one();
		}

		public function getParentArticleBoardId($parentNo) {
			$data = DBHandler::for_table('article')
				->select_many('board_id', 'content')
				->where('no', $parentNo)
				->find_one();

			if (!$data || !$data->content)
				return NULL;
			else
				return $data->board_id;
		}

		public function getArticleTopId($parentNo) {
			$data = DBHandler::for_table('article')
				->select_many('no', 'top_no')
				->where('no', $parentNo)
				->find_one();

			if (!$data->top_no) return $data->no;
			else return $this->getArticleTopId($data->top_no);	
		}

		public function getArticleOrderKey($parentNo, $topNo) {
			$parentArticleData = DBHandler::for_table('article')
				->select_many('no', 'order_key')
				->where('no', $parentNo)
				->find_one();
			
			if ($parentArticleData->order_key == NULL) {
				$data = DBHandler::for_table('article')
					->select_many('no', 'order_key')
					->where('top_no', $parentNo)
					->where_raw('LENGTH(order_key) = 2')
					->order_by_desc('order_key')
					->find_one();
			}else {
				$data = DBHandler::for_table('article')
					->select_many('no', 'order_key')
					->where('top_no', $topNo)
					->where_like('order_key', $parentArticleData->order_key.'%')
					->order_by_desc('order_key')
					->find_one();

				if ($data->order_key == NULL || $data->order_key == $parentArticleData->order_key)
					return $parentArticleData->order_key . 'AA';
			}

			if ($data->order_key == NULL)
				return 'AA';
			else {
				$orderKey = $data->order_key;
				
				if ($orderKey == 'ZZ')
					return NULL;
				else if (substr($orderKey, strlen($orderKey)-1, 1) == 'Z')
					return $this->_getNextAlphabet(substr($orderKey, 0, 1)) . 'A';
				else
					return substr($orderKey, 0, strlen($orderKey)-1) . $this->_getNextAlphabet(substr($orderKey, strlen($orderKey)-1, 1));
			}
		}

		private function _getNextAlphabet($alphabet) {
			$alphabets = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			
			$pos = array_search($alphabet, $alphabets);
			return $alphabets[$pos + 1];
		}



		public function insertNewArtile($boardId, $title, $content, $isSecret, $isNotice, $allowComment, $parentNo=NULL, $category=NULL) {
			$record = DBHandler::for_table('article')->create();
			$record->set(array(
				'board_id' => $boardId,
				'title' => $title,
				'content' => $content,
				'writer_id' => User::getCurrent()->id,
				'is_secret' => $isSecret,
				'is_notice' => $isNotice,
				'allow_comment' => $allowComment,
				'upload_time' => date('Y-m-d H:i:s')
			));

			if (isset($parentNo) && !empty($parentNo)) {
				$topNo = $this->getArticleTopId($parentNo);
				$orderKey = $this->getArticleOrderKey($parentNo, $topNo);

				if ($orderKey === NULL)
					return -1; // 답글 최대 갯수 23*23개를 초월함

				$record->set(array(
					'top_no' => $topNo,
					'order_key' => $orderKey
				));
			}
			if (isset($category) && $category != 'none') {
				$record->set('category', $category);
			}
			$record->save();

			return $record;
		}


		public function insertArticleFiles($articleNo, $fileId, $fileName) {
			$record = DBHandler::for_table('article_files')->create();
			$record->set(array(
				'article_no' => $articleNo,
				'file_id' => $fileId,
				'file_name' => $fileName
			));
			$record->save();
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


		public function moveChildArticles($boardId, $topArticleNo) {
			DBHandler::for_table('article')->raw_query('
				UPDATE '.DBHandler::$prefix.'article
				SET board_id = "'.escape($boardId).'"
				WHERE top_no = "'.escape($topArticleNo).'"
			');
		}

		public function updateArticle($articleData, $boardId, $title, $content, $isSecret, $isNotice, $allowComment, $category=NULL) {
			$articleData->set(array(
				'board_id' => $boardId,
				'title' => $title,
				'content' => $content,
				'is_secret' => $isSecret,
				'is_notice' => $isNotice,
				'allow_comment' => $allowComment,
				'upload_time' => date('Y-m-d H:i:s')
			));

			if (isset($category) && !empty($category) && $category != 'none') {
				$articleData->set('category', $category);
			}
			
			$articleData->save();
		}

		public function deleteArticleFiles($id) {
			DBHandler::for_table('article_files')
				->where('id', $id)
				->delete_many();
		}

		/**
		 * 삭제 프로세스
		 */


		public function getArticleAndGroupData($articleNo) {
			return DBHandler::for_table('article')
				->select_many('article.*', 'board.name', 'board.admin_group', 'board.categorys')
				->join('board', array(
					'board.id','=','article.board_id'
				))
				->where('article.no', $articleNo)
				->find_one();
		}

		public function getSibingArticle($topNo, $orderKey) {
			// 형제 게시글 불러옴

			// 답글이 없을 경우 경우 빈 배열 반환
			// 답글이 있을 경우 답글들 반환
			
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
						->where_not_equal('order_key', $orderKey)
						->where_like('order_key', $orderKey . '%')
						->find_many();
				}
			}
		}

		// 상속받음
		/* public function getParentArticle($topNo, $orderKey) */

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
					->where_not_equal('order_key', $orderKey)
					->find_many();
			}
		}

		public function deleteArticle($articleNo) {
			DBHandler::for_table('article')
				->where('no', $articleNo)
				->delete_many();
		}


		/**
		 * 공지사항 등록 프롯스
		 */

		public function updateNoticeInfo($articleData, $isNotice) {
			$articleData->set('is_notice', $isNotice);
			$articleData->save();
		}
	}