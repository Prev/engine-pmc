<?php
	
	class BoardArticleModel extends Model {
		
		function getArticleData($articleNo) {
			$data = DBHandler::execQueryOne("
				SELECT (#)article.*, (#)board.*, (#)user.nick_name
				FROM (#)article, (#)board, (#)user
				WHERE (#)article.no='${articleNo}'
					AND (#)board.id=(#)article.board_id
					AND (#)user.id = (#)article.writer_id
				LIMIT 1"
			);

			if ($data) {
				$data->boardName = $data->name;
				$data->boardName_kr = $data->name_kr;
				$data->writerNick = $data->nick_name;
			}
			return $data;
		}
		
	}