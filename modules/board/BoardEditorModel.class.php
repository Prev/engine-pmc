<?php
	
	class BoardEditorModel extends BoardModel {

		public function getBoardLists() {
			$arr = DBHandler::for_table('board')
				->find_many();
			$lists = array();

			for ($i=0; $i<count($arr); $i++) {
				$arr[$i] = $arr[$i]->getData();

				$me = User::getCurrent();
				if (!$me || (isset($arr[$i]->writable_group) && !$me->checkGroup($arr[$i]->writable_group)))
					continue;
				
				$arr[$i]->name_locale = fetchLocale($arr[$i]->name_locales);
				array_push($lists, $arr[$i]);
			}
			return $lists;
		}

		public function getArticleTitle($articleNo) {
			return DBHandler::for_table('article')
				->select('title')
				->where('no', $articleNo)
				->find_one();
		}

		public function getArticleData($articleNo) {
			return DBHandler::for_table('article')
				->where('article.no', $articleNo)
				->find_one();
		}

		public function getArticleTitleAndBoardInfo($articleNo) {
			return DBHandler::for_table('article')
				->select_many('article.title', 'board.*')
				->where('article.no', $articleNo)
				->join('board', array(
					'board.id', '=', 'article.board_id'
				))
				->find_one();
		}

		public function getArticleFiles($articleNo) {
			return DBHandler::for_table('article_files')
				->select_many('files.*', 'article_files.file_name')
				->where('article_files.article_no', $articleNo)
				->join('files', array(
					'files.id', '=', 'article_files.file_id'
				))
				->find_many();
		}
	}