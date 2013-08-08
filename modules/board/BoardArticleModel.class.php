<?php
	
	class BoardArticleModel extends Model {
		
		public function getArticleData($articleNo) {
			$data = DBHandler::for_table('article')
				->select_many('article.*', 'board.*', 'user.nick_name')
				->where('article.no', $articleNo)
				->join('board', array(
					'board.id', '=', 'article.board_id'
				))
				->join('user', array(
					'user.id', '=', 'article.writer_id'
				))
				->find_one();

			if ($data) {
				$data->boardName = $data->name;
				$data->boardName_locale = fetchLocale($data->name_locales);
				$data->writer = USE_REAL_NAME ? $data->user_name : $data->nick_name;
			}
			return $data;
		}

		public function getArticleComments($articleNo) {
			return DBHandler::for_table('article_comment')
				->select_many('article_comment.*', 'user.user_name', 'user.nick_name')
				->where('article_comment.article_no', $articleNo)
				->join('user', array(
					'user.id', '=', 'article_comment.writer_id'
				))
				->order_by_expr('IF ('.DBHandler::$prefix.'article_comment.top_id, '.DBHandler::$prefix.'article_comment.top_id, '.DBHandler::$prefix.'article_comment.id)')
				->order_by_asc('id')
				->find_many();
		}

		public function getArticleFiles($articleNo) {
			return DBHandler::for_table('article_files')
				->where('article_files.article_no', $articleNo)
				->join('files', array(
					'files.id', '=', 'article_files.file_id'
				))
				->find_many();
		}
		
	}