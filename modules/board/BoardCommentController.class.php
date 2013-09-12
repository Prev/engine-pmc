<?php

	class BoardCommentController extends BoardController {
			
		public function procWriteComment() {
			$row = $this->model->getArticleInfo($_POST['article_no']);
			if (!$row) return;

			if ($row->commentable_group) {
				$me = User::getCurrent();
				if (!$me || !$me->checkGroup($row->commentable_group)) {
					goBack('덧글을 쓸 권한이 없습니다');
					return;
				}
			}

			if (!$row->allow_comment) {
				goBack('덧글을 허용하지 않은 게시글입니다');
				return;
			}

			$comment = htmlspecialchars($_POST['comment']);
			$comment = stripslashes($comment);

			$this->model->insertNewComment((int)$_POST['article_no'], $comment, evalCheckbox($_POST['is_secret']), $_POST['top_id'], $_POST['parent_id']);

			goBack();
		}
		
		public function procUpdateComment() {
			if (!$_POST['comment_id']) {
				goBack('오류가 발생했습니다', true);
				return;
			}

			$commentData = $this->model->getCommentData((int)$_GET['comment_id']);

			$me = User::getCurrent();
			if (!$me || $me->id != $commentData->writer_id) {
				goBack('덧글을 수정 할 수 없습니다');
				return;
			}

			$comment = htmlspecialchars($_POST['comment']);
			$comment = stripslashes($comment);

			$this->model->updateComment($commentData, $comment, evalCheckbox($_POST['is_secret']));

			goBack();
		}

		public function procDeleteComment() {
			if (!$_SERVER['HTTP_REFERER']) return;
			
			$commentData = $this->model->getCommentData((int)$_GET['comment_id']);
			
			$me = User::getCurrent();
			$isBoardAdmin = $this->checkIsBoardAdmin($articleData->admin_group);

			if (!$me || ($me->id != $commentData->writer_id) && !$isBoardAdmin) {
				goBack('덧글을 삭제 할 수 없습니다');
				return;
			}
			
			$this->model->deleteComment($commentData);
			
			goBack();
		}

	}

