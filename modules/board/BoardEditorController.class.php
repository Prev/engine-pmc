<?php
	
	class BoardEditorController extends Controller {

		public function init() {
			if (User::getCurrent() == NULL) {
				goLogin();
				return;
			}
		}
		
		public function procSaveArticle() {
			$boardInfo = DBHandler::for_table('board')
				->where('id', $_POST['board_id'])
				->find_one();

			$me = User::getCurrent();
			
			if (!$me || (isset($boardInfo->writable_group) && !$me->checkGroup(json_decode($boardInfo->writable_group)))) {
				goBack('글을 쓸 권한이 없습니다', true);
				return;
			}	

			/**
				TODO : 카테고리
			*/
			$attachFiles = explode(',', $_POST['attach_files']);
			for ($i=0; $i<count($attachFiles); $i++)
				$attachFiles[$i] = (int)$attachFiles[$i];


			$record = DBHandler::for_table('article')->create();
			$record->set(array(
				'board_id' => $boardInfo->id,
				'title' => $_POST['title'],
				'content' => removeXSS($_POST['content']),
				'writer_id' => User::getCurrent()->id,
				'is_secret' => evalCheckbox($_POST['is_secret']) ? 1 : 0 ,
				'is_notice' => evalCheckbox($_POST['is_notice']) ? 1 : 0 ,
				'allow_comment' => evalCheckbox($_POST['allow_comment']) ? 1 : 0 ,
				'upload_time' => date('Y-m-d H:i:s'),
				'attach_files' => '[' . $attachFiles . ']'
			));
			if (isset($_POST['parent_no']) && $_POST['parent_no'] != '') {
				$record->set(array(
					'top_no' => $this->model->getArticleTopId((int)$_POST['parent_no']),
					'order_key' => $this->model->getArticleOrderKey((int)$_POST['parent_no'])
				));
			}
			$record->save();

			$articleNo = $record->no;
			$record->set('top_no', $articleNo);
			$record->save();

			for ($i=0; $i<count($attachFiles); $i++) { 
				$record = DBHandler::for_table('article_files')->create();
				$record->set(array(
					'article_no' => $articleNo,
					'file_id' => $attachFiles[$i]
				));
				$record->save();
			}
			redirect(RELATIVE_URL .  (USE_SHORT_URL ? '/' : '/?module=board&action=dispArticle&article_no=') . $articleNo);
		}

	}