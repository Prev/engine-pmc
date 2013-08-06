<?php

	class BoardCommentController extends Controller {
			
		public function procWriteComment() {
			$row = $this->model->getCommentableGroup($_POST['article_no']);
			if (!$row) return;

			if ($row->commentable_group) {
				$me = User::getCurrent();
				if (!$me || !$me->checkGroup($row->commentable_group)) {
					$this->alert('덧글을 쓸 권한이 없습니다');
					goBack();
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
			$record->save();

			goBack();
		}
		
		public function procDeleteComment() {
			if (!$_SERVER['HTTP_REFERER']) return;
			
			$commentData = $this->model->getCommentData((int)$_GET['comment_id']);
			
			$me = User::getCurrent();
			if (!$me || $me->id != $commentData->writer_id) {
				$this->alert('덧글을 수정 할 수 없습니다');
				goBack();
				return;
			}
			
			$commentData
				->where('id', (int)$_GET['comment_id'])
				->delete_many();

			$this->alert('덧글을 삭제했습니다');
			goBack();
		}

		private function alert($message) {
			echo '<script type="text/javascript">alert("'.$message.'")</script>';
			return;
		}

	}

