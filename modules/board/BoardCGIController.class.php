<?php
	
	class BoardCGIController extends BoardController {

		public function init() {
			if (User::getCurrent() == NULL) {
				goLogin();
				return;
			}
		}
		
		public function procSaveArticle() {
			$boardInfo = $this->model->getBoardInfo($_POST['board_id']);

			$me = User::getCurrent();
			if (!$me || (isset($boardInfo->writable_group) && !$me->checkGroup(json_decode($boardInfo->writable_group)))) {
				goBack('글을 쓸 권한이 없습니다', true);
				return;
			}

			$isNotice = evalCheckbox($_POST['is_notice']);
			$isBoardAdmin = $this->checkIsBoardAdmin($boardInfo->admin_group);

			if (isset($_POST['category']) && $_POST['category'] != 'none') {
				$categorys = json_decode($boardInfo->categorys);
				if (array_search($_POST['category'], $categorys) === false) {
					goBack('사용할 수 없는 카테고리입니다', true);
					return;
				}
			}

			if (!$isBoardAdmin && $isNotice) {
				goBack('공지사항을 작성 할 권한이 없습니다', true);
				return;
			}

			if (!empty($_POST['attach_files'])) {
				$attachFiles = $_POST['attach_files'];
				$attachFiles = join('"', explode("\\\"", $attachFiles));
				$attachFiles = json_decode($attachFiles);
			}
			$record = DBHandler::for_table('article')->create();
			$record->set(array(
				'board_id' => $boardInfo->id,
				'title' => $_POST['title'],
				'content' => removeXSS($_POST['content']),
				'writer_id' => User::getCurrent()->id,
				'is_secret' => evalCheckbox($_POST['is_secret']) ? 1 : 0 ,
				'is_notice' => $isNotice ? 1 : 0 ,
				'allow_comment' => evalCheckbox($_POST['allow_comment']) ? 1 : 0 ,
				'upload_time' => date('Y-m-d H:i:s')
			));
			if (isset($_POST['parent_no']) && !empty($_POST['parent_no'])) {
				$topNo = $this->model->getArticleTopId((int)$_POST['parent_no']);
				$orderKey = $this->model->getArticleOrderKey((int)$_POST['parent_no'], $topNo);
				
				$record->set(array(
					'top_no' => $topNo,
					'order_key' => $orderKey
				));
			}
			if (isset($_POST['category']) && $_POST['category'] != 'none') {
				$record->set('category', $_POST['category']);
			}
			$record->save();

			$articleNo = $record->no;
			
			if ($attachFiles) {
				for ($i=0; $i<count($attachFiles); $i++) { 
					$record = DBHandler::for_table('article_files')->create();
					$record->set(array(
						'article_no' => $articleNo,
						'file_id' => $attachFiles[$i]->id,
						'file_name' =>  $attachFiles[$i]->name,
					));
					$record->save();
				}
			}

			redirect(RELATIVE_URL .  (USE_SHORT_URL ? '/' : '/?module=board&action=dispArticle&article_no=') . $articleNo);
		}


		public function procUpdateArticle() {
			if (!$_POST['article_no']) {
				goBack('오류가 발생했습니다', true);
				return;
			}

			$articleBoardData = $this->model->getArticleAndGroupData($_POST['article_no']);
			$isNotice = evalCheckbox($_POST['is_notice']);
			$isBoardAdmin = $this->checkIsBoardAdmin($articleBoardData->admin_group);

			$articleData = $this->model->getArticleData($_POST['article_no']);

			if (!$articleData || User::getCurrent()->id != $articleData->writer_id) {
				goBack('글을 수정 할 권한이 없습니다', true);
				return;
			}

			if (!$isBoardAdmin && $isNotice) {
				goBack('공지사항을 작성 할 권한이 없습니다', true);
				return;
			}

			if (isset($_POST['category']) && $_POST['category'] != 'none') {
				$categorys = json_decode($articleBoardData->categorys);
				if (array_search($_POST['category'], $categorys) === false) {
					goBack('사용할 수 없는 카테고리입니다', true);
					return;
				}
			}

			if (!empty($_POST['attach_files'])) {
				$attachFiles = $_POST['attach_files'];
				$attachFiles = join('"', explode("\\\"", $attachFiles));
				$attachFiles = json_decode($attachFiles);
			}

			if ($articleData->board_id != $_POST['board_id']) {
				DBHandler::for_table('article')->raw_query('
					UPDATE '.DBHandler::$prefix.'article
					SET board_id = "'.$_POST['board_id'].'"
					WHERE top_no = "'.$_POST['article_no'].'"
				');
			}

			$articleData->set(array(
				'board_id' => $_POST['board_id'],
				'title' => $_POST['title'],
				'content' => removeXSS($_POST['content']),
				'is_secret' => evalCheckbox($_POST['is_secret']) ? 1 : 0 ,
				'is_notice' => evalCheckbox($_POST['is_notice']) ? 1 : 0 ,
				'allow_comment' => evalCheckbox($_POST['allow_comment']) ? 1 : 0 ,
				'upload_time' => date('Y-m-d H:i:s')
			));

			if (isset($_POST['category']) && $_POST['category'] != 'none') {
				$articleData->set('category', $_POST['category']);
			}

			$articleData->save();

			$originFiles = $this->model->getOriginFiles($_POST['article_no']);

			for ($i=0; $i<count($originFiles); $i++) {
				if ($pos = array_search($originFiles[$i]->file_id, $attachFiles)) {
					array_slice($originFiles, $i);
					array_slice($attachFiles, $pos);
					continue;
				}
			}
			for ($i=0; $i<count($attachFiles); $i++) { 
				$record = DBHandler::for_table('article_files')->create();
				$record->set(array(
					'article_no' => $_POST['article_no'],
					'file_id' => $attachFiles[$i]->id,
					'file_name' => $attachFiles[$i]->name
				));
				$record->save();
			}
			for ($i=0; $i<count($originFiles); $i++) { 
				DBHandler::for_table('article_files')
					->where('id', $originFiles[$i]->id)
					->delete_many();
			}
			$this->alert('게시글을 성공적으로 수정했습니다');
			redirect(RELATIVE_URL .  (USE_SHORT_URL ? '/' : '/?module=board&action=dispArticle&article_no=') . $_POST['article_no']);
		}



		public function procDeleteArticle() {
			if (!$_SERVER['HTTP_REFERER']) return;

			$articleData = $this->model->getArticleAndGroupData((int)$_GET['article_no']);
			if ($articleData === false) {
				goBack('게시글이 존재하지 않습니다');
				return;
			}

			if (!$articleData->top_no) $articleData->top_no = $articleData->no;

			$me = User::getCurrent();
			
			$isBoardAdmin = $this->checkIsBoardAdmin($articleData->admin_group);

			if (!$me || ($me->id != $articleData->writer_id) && !$isBoardAdmin) {
				goBack('권한이 없습니다');
				return;
			}
			
			$this->deleteArticleAndCheckParent($_GET['article_no'], $articleData->top_no, $articleData->order_key);
			// 임시 삭제된 부모글을 체크하고 게시글을 삭제
			
			$this->alert('게시글을 성공적으로 삭제했습니다');
			$url = getUrl('board', 'dispList', 'board_name='.$articleData->name);
			//$url = getUrlA('next='.getUrl(), getBackUrl());
			redirect($url, false);
		}

		private function deleteArticleAndCheckParent($no, $topNo, $orderKey) {
			$sibingArticles = $this->model->getSibingArticle($topNo, $orderKey);
			
			if (count($sibingArticles) === 0) {
				// 답글이 없으면 그냥 삭제함
				$this->deleteArticle($no);
			
			}else if ($orderKey !== NULL && count($sibingArticles) == 1) {
				// 삭제하는 글이 답글이고 부모글에 답글이 하나만 달렸으면

				$row = $this->model->getParentArticle($topNo, $orderKey); // 원본글이 임시삭제 됬는지 (content가 NULL인지)를 체크
				
				$this->deleteArticle($no); // 일단 현재 글 삭제

				if ($row != false && $row->content === NULL) {
					// 임시 삭제된 부모글이 있으면 삭제
					// 부모의 부모까지 처리하기 위해 재귀함수 처리
					$this->deleteArticleAndCheckParent($row->no, $row->top_no, $row->order_key);
				}
			}else {
				// 부모글에 답글이 두개 이상 달려있거나 삭제하는 글이 원글이면
				$childArticles = $this->model->getChildArticleNo($topNo, $orderKey); // 본인 글과 자녀 게시글 (해당 글의 답글) 가져옴
				
				if (count($childArticles) > 0) {
					// 자녀 게시글이 있으면 임시 삭제
					
					$record = DBHandler::for_table('article')
						->where('no', $no)
						->find_one();
					$record->set('content', NULL); // content를 NULL로 설정하면서 임시 삭제 처리
					$record->save();
				}else {
					// 자녀 게시글이 없으면 완전 삭제
					$this->deleteArticle($no);
				}
			}
		}

		private function deleteArticle($articleNo) {
			DBHandler::for_table('article')
				->where('no', $articleNo)
				->delete_many();
		}

		public function procToggleNotice() {
			if (!$_SERVER['HTTP_REFERER']) return;
			if (!$_GET['article_no']) {
				goBack('오류가 발생했습니다', true);
				return;
			}

			$articleBoardData = $this->model->getArticleAndGroupData($_POST['article_no']);
			$isBoardAdmin = $this->checkIsBoardAdmin($articleBoardData->admin_group);
			
			$articleData = $this->model->getArticleData($_GET['article_no']);

			if (!$isBoardAdmin) {
				goBack('권한이 없습니다', true);
				return;
			}

			$articleData->set('is_notice', !$articleData->is_notice);
			$articleData->save();

			goBack( $articleData->is_notice ?
				'게시글을 공지사항으로 등록했습니다' : '게시글을 공지사항에서 등록해제했습니다'
			);
		}


		private function alert($message) {
			echo '<script type="text/javascript">alert("'.$message.'")</script>';
			return;
		}
	}