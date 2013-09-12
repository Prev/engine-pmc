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

		public function insertNewComment($articleNo, $comment, $isSecret, $topId=NULL, $parentId=NULL) {
			$record = DBHandler::for_table('article_comment')->create();
			$record->set(array(
				'article_no' => $articleNo,
				'content' => $comment,
				'writer_id' => User::getCurrent()->id,
				'write_time' => date('Y-m-d H:i:s'),
				'is_secret' => $isSecret
			));

			if (isset($topId) && $topId) {
				$record->set(array(
					'top_id' => $topId,
					'parent_id' => $parentId
				));
			}

			$record->save();
		}

		public function updateComment($commentData, $comment, $isSecret) {
			$commentData->set(array(
				'content' => $comment,
				'is_secret' => $isSecret
			));
			$commentData->save();
		}

		public function deleteComment($commentData) {
			$commentData->delete();
		}
	}

