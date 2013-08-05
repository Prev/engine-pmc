<?php

	class BoardArticleController extends Controller {
			
		public function init() {
			$articleNo = $_GET['article_no'] ? escape($_GET['article_no']) : ($_GET['no'] ? escape($_GET['no']) : NULL);
			$articleData = $this->model->getArticleData($articleNo);
			$boardName = $articleData->boardName;

			
			if ($articleData->readable_group) {
				$me = User::getCurrent();
				if (!$me || !$me->checkGroup($articleData->readable_group)) {
					goBack('글을 볼 권한이 없습니다');
				}
			}
			
			$fileDatas = $this->model->getArticleFiles($articleNo);
			
			$this->view->setProperties(array(
				'articleNo' => $articleNo,
				'articleData' => $articleData,
				'boardName' => $boardName,
				'fileDatas' => $fileDatas
			));
					
			Context::getInstance()->selectedMenu = $boardName;
		}

	}

