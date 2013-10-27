<?php

	class BoardListController extends BoardController {
		
		const DEFAULT_AOP = 15;
		
		public function init() {

			$boardName = isset($_GET['board_name']) ? escape($_GET['board_name']) : NULL;
			$aop = isset($_GET['aop']) ? (int)escape($_GET['aop']) : self::DEFAULT_AOP;
			$nowPage = isset($_GET['page']) ? escape($_GET['page']) : 1;

			if ($boardName === NULL && Context::getInstance()->selectedMenu) {
				$boardInfo = $this->model->getBoardInfoByMenuId(Context::getInstance()->selectedMenu->id);
				$boardName = $boardInfo->name;

				if (!$boardInfo) {
					Context::printErrorPage(array(
						'en' => 'Cannot excute board - current menu is not connected with any board',
						'ko' => '게시판을 실행 할 수 없습니다 - 해당 메뉴와 연결된 게시판이 없습니다'
					));
					return;
				}
			}else if ($boardName === NULL) {
				Context::printErrorPage(array(
					'en' => 'Cannot excute board - board ID not defined',
					'ko' => '게시판을 실행 할 수 없습니다 - 게시판 ID가 지정되지 않음'
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

			
			// 메뉴가 연결되지 않았을 때 메뉴에 연결
			if (!isset(Context::getInstance()->selectedMenu)) {
				$row = DBHandler::for_table('menu')
					->where('id', $boardInfo->menu_id)
					->find_one();
				
				if ($row) {
					Context::getInstance()->selectedMenu = $row->getData();
					
					while ($row->parent_id != NULL) {
						$row = DBHandler::for_table('menu')
							->where('id', $row->parent_id)
							->find_one();
						
						array_unshift(Context::getInstance()->parentMenus, $row->getData());
					}
				}
			}
		}

		public function manufactureArticleDatas($articles) {
			$arr = array();

			for ($i=0; $i<count($articles); $i++) {
				$article = $articles[$i]->getData();

				if ($article->content === NULL)
					$article->is_delete = true;

				if ($article->is_secret) {
					if ($article->writer_id == User::getCurrent()->id || $this->view->isBoardAdmin)
						$article->secret_visible = true;
					else {
						$parentArticle = $this->model->getParentArticle($article->top_no, $article->order_key);
						if ($parentArticle && $parentArticle->writer_id == User::getCurrent()->id)
							$article->secret_visible = true;
					}
				}

				$article->writer = htmlspecialchars(USE_REAL_NAME ? $article->user_name : $article->nick_name);
				$article->is_reply = isset($article->parent_no);
				$article->upload_time2 = getRelativeTime(strtotime($article->upload_time));
				$article->title = htmlspecialchars($article->title);

				if (mb_strlen($article->title) > 150)
					$article->title = mb_substr($article->title, 0, 150) . '...';

				if ($article->category)
					$article->category = htmlspecialchars($article->category);
				
				if (isset($_REQUEST['search']) && $_REQUEST['search']) {
					$regexp = '/(' . str_replace(' ', '|', $_REQUEST['search']) . ')/i';

					$str = $_REQUEST['search_type'] == 'writer' ? $article->writer : $article->title;
					$str = str_replace('[', '\\[', $str);
					$str = str_replace(']', '\\]', $str);

					$searchKeys = explode(' ', $_REQUEST['search']);

					for ($j=0; $j<count($searchKeys); $j++)
						$str = str_replace($searchKeys[$j], '['.$searchKeys[$j].']', $str);

					$str = str_replace('[', '<strong class="searched">', $str);
					$str = str_replace(']', '</strong>', $str);
					$str = str_replace('\\[', '[', $str);
					$str = str_replace('\\]', ']', $str);

					if ($_REQUEST['search_type'] == 'writer')
						$article->writer = $str;
					else
						$article->title = $str;
				}

				array_push($arr, $article);
			}
			
			return $arr;
		}

	}

