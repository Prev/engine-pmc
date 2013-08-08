<?php

	class BoardCommentController extends Controller {
			
		public function procWriteComment() {
			$row = $this->model->getCommentableGroup($_POST['article_no']);
			if (!$row) return;

			if ($row->commentable_group) {
				$me = User::getCurrent();
				if (!$me || !$me->checkGroup($row->commentable_group)) {
					goBack('덧글을 쓸 권한이 없습니다');
					return;
				}
			}

			$comment = join('&lt;', explode('<', $_POST['comment']));
			$comment = join('&gt;', explode('>', $comment));

			$record = DBHandler::for_table('article_comment')->create();
			$record->set(array(
				'article_no' => (int)$_POST['article_no'],
				'content' => $comment,
				'writer_id' => User::getCurrent()->id,
				'write_time' => date('Y-m-d H:i:s')
			));

			if (isset($_POST['parent_id'])) {
				$record->set(array(
					'top_id' => $_POST['top_id'],
					'parent_id' => $_POST['parent_id']
				));
			}

			$record->save();

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

			$comment = join('&lt;', explode('<', $_POST['comment']));
			$comment = join('&gt;', explode('>', $comment));

			$commentData->set('content', $comment);
			$commentData->save();

			goBack();
		}

		public function procDeleteComment() {
			if (!$_SERVER['HTTP_REFERER']) return;
			
			$commentData = $this->model->getCommentData((int)$_GET['comment_id']);
			
			$me = User::getCurrent();
			$adminGroup = isset($commentData->admin_group) ? 
				array_merge(json_decode($commentData->admin_group), User::getMasterAdmin()) :
				User::getMasterAdmin();

			if (!$me || ($me->id != $commentData->writer_id) && !$me->checkGroup($adminGroup)) {
				goBack('덧글을 삭제 할 수 없습니다');
				return;
			}
			
			$commentData
				->where('id', (int)$_GET['comment_id'])
				->delete_many();

			goBack('덧글을 삭제했습니다');
		}

	}

