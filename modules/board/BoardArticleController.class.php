<?php

	class BoardArticleController extends BoardController {
			
		public function init() {
			$articleNo = $_GET['article_no'] ? escape($_GET['article_no']) : ($_GET['no'] ? escape($_GET['no']) : NULL);
			$articleData = $this->model->getArticleData($articleNo);
			$boardName = $articleData->boardName;
			
			if (User::getCurrent())
				$isBoardAdmin = $this->checkIsBoardAdmin($articleData->admin_group);

			if ($articleData->readable_group) {
				$me = User::getCurrent();
				if (!$me || !$me->checkGroup($articleData->readable_group)) {
					goBack('글을 볼 권한이 없습니다');
				}
			}

			if (!$articleData->is_notice && $articleData->is_secret && !$isBoardAdmin && $articleData->writer_id != User::getCurrent()->id) {
				$parentArticle = $this->model->getParentArticle($articleData->top_no, $articleData->order_key);
				
				if (!$parentArticle || $parentArticle->writer_id != User::getCurrent()->id)
					goBack('글을 읽을 권한이 없습니다');
			}

			$this->view->commentable = true;
			if ($articleData->commentable_group) {
				$me = User::getCurrent();
				if (!$me || !$me->checkGroup($articleData->commentable_group))
					$this->view->commentable = false;
			}

			$commentDatas = $this->model->getArticleComments($articleNo);
			$fileDatas = $this->model->getArticleFiles($articleNo);
			
			$isBoardAdmin = $this->checkIsBoardAdmin($articleData->admin_group);

			for ($i=0; $i<count($commentDatas); $i++) {
				$commentDatas[$i]->content = join('<br>', explode("\n", $commentDatas[$i]->content));
				$commentDatas[$i]->writer = USE_REAL_NAME ? $commentDatas[$i]->user_name : $commentDatas[$i]->nick_name;
				
				if ($commentDatas[$i]->top_id) {
					$commentDatas[$i]->is_reply = true;

					for ($j=0; $j<count($commentDatas); $j++) { 
						if ($commentDatas[$i]->parent_id == $commentDatas[$j]->id) {
							$commentDatas[$i]->parent_writer_id = $commentDatas[$j]->writer_id;
							$commentDatas[$i]->parent_writer = USE_REAL_NAME ? $commentDatas[$j]->user_name : $commentDatas[$j]->nick_name;
						}
					}
				}
				
				if ($commentDatas[$i]->is_secret) {
					if ($commentDatas[$i]->writer_id == User::getCurrent()->id || $articleData->writer_id == User::getCurrent()->id || $isBoardAdmin)
						$commentDatas[$i]->secret_visible = true;
					
					else if ($commentDatas[$i]->parent_writer_id && $commentDatas[$i]->parent_writer_id == User::getCurrent()->id)
						$commentDatas[$i]->secret_visible = true;
				}
			}


			// 조회수
			if ($articleData) {
				if (!$_SESSION['article_hits']) $_SESSION['article_hits'] = array();
				if (!$_SESSION['article_hits'][$articleData->no]) {
					$this->model->increaseArticleHits($articleData->no);
					$articleData->hits++;
					$_SESSION['article_hits'][$articleData->no] = 1;
				}
			}
			
			$this->view->setProperties(array(
				'articleNo' => $articleNo,
				'articleData' => $articleData,
				'boardName' => $boardName,
				'commentDatas' => $commentDatas,
				'fileDatas' => $fileDatas,
				'isBoardAdmin' => $isBoardAdmin
			));
			
			Context::getInstance()->selectedMenu = $boardName;
		}

	}

