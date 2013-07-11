<?php
	
	class BoardModule extends ModuleBase {
		
		protected $boardId;
		protected $boardInfo;
		protected $articleNo;
		
		protected $boardName;
		protected $boardName_kr;
		
		protected $aop;
		protected $nowPage;
		
		public function init() {
			$action = $GLOBALS['__ModuleAction__'] ? $GLOBALS['__ModuleAction__'] : 'dispList';
			
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
				$this->articleNo = self::getModel()->articleNo = $_GET['article_no'] ? $_GET['article_no'] : ($_GET['no'] ? $_GET['no'] : NULL);
				$this->boardName = self::getModel()->getArticleData()->boardName;
				
				Context::getInstance()->selectedMenu = $this->boardName;
			}
			
			self::getModel()->inherit();
			self::getView()->inherit();
		}
		
		protected function inherit() {
			$m = $GLOBALS['__Module__'];
			
			$this->boardId = $m->boardId;
			$this->boardInfo = $m->boardInfo;
			$this->articleNo = $m->articleNo;
			
			$this->boardName = $m->boardName;
			$this->aop = $m->aop;
			$this->nowPage = $m->nowPage;
		}
		
		public function printContent() {
			if ($GLOBALS['__ModuleAction__'] === NULL)
				self::getView()->dispList();
		}
		
	}