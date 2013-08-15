<?php

	class BoardCommentModel extends BoardModel {
		
		public function getArticleInfo($articleNo) {
			return DBHandler::for_table('article')
				->select_many('article.board_id', 'article.allow_comment', 'board.commentable_group')
				->where('article.no', $articleNo)
				->join('board', array(
					'board.id','=','article.board_id'
				))
				->find_one();
		}

		public function getCommentData($commentId) {
			return DBHandler::for_table('article_comment')
				->where('id', $commentId)
				->find_one();
		}

	}

