<?php
	
	class BoardArticleModel extends Model {
		
		function getArticleData($articleNo) {
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
				$data->writerNick = $data->nick_name;
			}
			return $data;
		}
		
	}