<?php
	
	class BoardModule extends Module {
		
		protected $boardId;
		protected $boardInfo;
		protected $articleNo;
		
		protected $boardName;
		protected $boardName_kr;
		
		protected $aop;
		protected $nowPage;
		
		public function init() {
			$action = $this->action;

			if ($action == 'dispList') {
				$this->boardName = $_GET['board_name'] ? $_GET['board_name'] : ( $_GET['menu'] ? $_GET['menu'] : NULL );
				$this->aop = $_GET['aop'] ? $_GET['aop'] : 20;
				$this->nowPage = $_GET['page'] ? $_GET['page'] : 1;

				if (!$this->boardName) {
					Context::printErrorPage(array(
						'en' => 'Cannot excute board - board ID not defined',
						'kr' => '게시판을 실행 할 수 없습니다 - 게시판 ID가 지정되지 않음'
					));
					return;
				}
				
				$this->boardInfo = DBHandler::execQueryOne("SELECT * FROM (#)board WHERE name='{$this->boardName}' LIMIT 1");
				$this->boardId = $this->boardInfo->id;
				$this->boardName_kr = $this->boardInfo->boardName_kr;

				Context::getInstance()->selectedMenu = $this->boardName;
				
			}else if ($action == 'dispArticle') {
				$this->articleNo = $this->model->articleNo = $_GET['article_no'] ? $_GET['article_no'] : ($_GET['no'] ? $_GET['no'] : NULL);
				$this->boardName = $this->model->getArticleData()->boardName;
				
				Context::getInstance()->selectedMenu = $this->boardName;
				$this->view->articleNo = $this->articleNo;
			}
			
			$this->setProperties($this->model);
			$this->setProperties($this->view);
		}
		
		private function setProperties($target) {
			$target->boardId = $this->boardId;
			$target->boardInfo = $this->boardInfo;
			$tatget->articleNo = $this->articleNo;
			
			$target->boardName = $this->boardName;
			$target->aop = $this->aop;
			$target->nowPage = $this->nowPage;
		}
		
	}