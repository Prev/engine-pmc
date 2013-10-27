<?php
	
	class BoardArticleModel extends BoardModel {
		
		public function getArticleData($articleNo) {
			$data = DBHandler::for_table('article')
				->select_many('article.*', 'board.*', 'user.nick_name', 'user.user_name')
				->where('article.no', $articleNo)
				->join('board', array(
					'board.id', '=', 'article.board_id'
				))
				->join('user', array(
					'user.id', '=', 'article.writer_id'
				))
				->find_one();

			if ($data) {
				$data->title = htmlspecialchars($data->title);
				$data->boardName = $data->name;
				$data->boardName_locale = fetchLocale($data->name_locales);
				$data->writer = htmlspecialchars(USE_REAL_NAME ? $data->user_name : $data->nick_name);
			}
			return $data;
		}

		public function getArticleComments($articleNo) {
			$articleNo = escape($articleNo);
			$pfx = DBHandler::$prefix;

			$query = "SELECT c1.*, c2.is_secret AS top_is_secret, {$pfx}user.user_name, {$pfx}user.nick_name
						FROM {$pfx}article_comment c1, {$pfx}article_comment c2, {$pfx}user
						WHERE
							c1.article_no = {$articleNo}
							AND ((c1.top_id IS NULL AND c2.id = c1.id) OR c2.id = c1.top_id)
							AND {$pfx}user.id = c1.writer_id
						ORDER BY IF (c1.top_id, c1.top_id, c1.id) ASC, c1.id ASC";

			$arr = DBHandler::for_table('article_comment')
					->raw_query($query)
					->find_many();
			
			if ($arr) {
				for ($i=0; $i<count($arr); $i++) {
					$arr[$i]->is_secret = $arr[$i]->is_secret ? true : false;
					$arr[$i]->top_is_secret = $arr[$i]->top_is_secret ? true : false;

					if (!$arr[$i]->top_id)
						$arr[$i]->top_is_secret = NULL;
				}
			}
			return $arr;
		}

		public function getArticleFiles($articleNo) {
			return DBHandler::for_table('article_files')
				->where('article_files.article_no', $articleNo)
				->join('files', array(
					'files.id', '=', 'article_files.file_id'
				))
				->find_many();
		}

		public function increaseArticleHits($articleNo) {
			$row = DBHandler::for_table('article')
				->where('no', $articleNo)
				->find_one();

			if ($row) {
				$row->set_expr('hits', 'hits + 1');
				$row->save();
				
				return true;
			}
			return false;
		}
	}