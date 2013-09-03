<?php

	class BoardListController extends BoardController {
		
		const DEFAULT_AOP = 15;
		
		public function init() {

			$boardName = isset($_GET['board_name']) ? escape($_GET['board_name']) : NULL;
			$aop = isset($_GET['aop']) ? escape($_GET['aop']) : self::DEFAULT_AOP;
			$nowPage = isset($_GET['page']) ? escape($_GET['page']) : 1;

			if ($boardName === NULL && $_GET['menu']) {
				$boardName = $_GET['menu'];
				$boardInfo = $this->model->getBoardInfo($_GET['menu']);

				if (!$boardInfo) {
					Context::printErrorPage(array(
						'en' => 'Cannot excute board - current menu is not connected with any board',
						'kr' => '게시판을 실행 할 수 없습니다 - 해당 메뉴와 연결된 게시판이 없습니다'
					));
					return;
				}
			}else if ($boardName === NULL) {
				Context::printErrorPage(array(
					'en' => 'Cannot excute board - board ID not defined',
					'kr' => '게시판을 실행 할 수 없습니다 - 게시판 ID가 지정되지 않음'
				));
				return;
			}else
				$boardInfo = $this->model->getBoardInfo($boardName);

			
			$boardId = $boardInfo->id;
			$this->model->setProperties(array(
				'aop' => $aop,
				'boardId' => $boardId,
				'nowPage' => $nowPage
			));
			$this->view->nowPage = $nowPage;
			$this->view->boardName = $boardName;
			$this->view->boardInfo = $boardInfo;
			$this->view->isBoardAdmin = $this->checkIsBoardAdmin($boardInfo->admin_group);
			$this->view->categorys = $boardInfo->categorys ? json_decode($boardInfo->categorys) : NULL;

			Context::getInstance()->selectedMenu = $boardName;
		}

		public function manufactureArticleDatas($articles) {
			for ($i=0; $i<count($articles); $i++) {
				if ($articles[$i]->content === NULL)
					$articles[$i]->is_delete = true;

				if ($articles[$i]->is_secret) {
					if ($articles[$i]->writer_id == User::getCurrent()->id || $this->view->isBoardAdmin)
						$articles[$i]->secret_visible = true;
					else {
						$parentArticle = $this->getParentArticle($articles[$i]->top_no, $articles[$i]->order_key);
						if ($parentArticle && $parentArticle->writer_id == User::getCurrent()->id)
							$articles[$i]->secret_visible = true;
					}
				}

				$articles[$i]->writer = htmlspecialchars(USE_REAL_NAME ? $articles[$i]->user_name : $articles[$i]->nick_name);
				$articles[$i]->is_reply = isset($articles[$i]->parent_no);
				$articles[$i]->upload_time2 = getRelativeTime(strtotime($articles[$i]->upload_time));
				$articles[$i]->title = htmlspecialchars($articles[$i]->title);

				if ($articles[$i]->category)
					$articles[$i]->category = htmlspecialchars($articles[$i]->category);
				
				if (isset($_REQUEST['search']) && $_REQUEST['search']) {
					$regexp = '/(' . str_replace(' ', '|', $_REQUEST['search']) . ')/i';

					if ($_REQUEST['search_type'] == 'writer')
						$articles[$i]->writer = preg_replace($regexp, '<strong class="search">$1</strong>', $articles[$i]->writer);
					else
						$articles[$i]->title = preg_replace($regexp, '<strong class="search">$1</strong>', $articles[$i]->title);
				}
			}
			return $articles;
		}

	}

