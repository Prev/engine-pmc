<?php
	
	class BoardListModel extends BoardModel {
		
		public $boardId;
		public $aop;
		public $nowPage;

		public function getBoardInfo($boardName) {
			return DBHandler::for_table('board')
				->where('name', $boardName)
				->find_one();
		}

		public function getArticleDatas($boardInfo) {
			$limitNum = (int)(($this->nowPage - 1) * $this->aop);

			$userId = (int)User::getCurrent()->id;
			$pfx = DBHandler::$prefix;
			$isBoardAdmin = $this->controller->checkIsBoardAdmin($boardInfo->admin_group);

			if ($boardInfo->hide_secret_article && !$isBoardAdmin) {		
				$query = "SELECT a2.*, {$pfx}user.user_name, {$pfx}user.nick_name,
						(SELECT COUNT(*) FROM {$pfx}article_comment WHERE article_no = a2.no) AS comment_counts,
						(SELECT COUNT(*) FROM {$pfx}article_files WHERE article_no = a2.no) AS file_counts
					FROM {$pfx}article a1, {$pfx}article a2
					JOIN {$pfx}user ON {$pfx}user.id = a2.writer_id
					WHERE
					(
						(
							(a2.is_secret != 0)
							AND
							(
								(
									IF (a1.top_no, a1.top_no, a1.no) = a2.top_no
									AND a1.order_key <=> IF( LENGTH(a2.order_key)=2, NULL , SUBSTR(a2.order_key, 1, LENGTH(a2.order_key)-2)) 
									AND a1.writer_id = '{$userId}'
								)OR (
									a2.writer_id = '{$userId}'
									AND a2.no = a1.no
								)
							)
						)
						OR (
							a2.is_secret = '0'
							AND a2.no = a1.no
						)
					)";
				if (isset($_REQUEST['category']) && $_REQUEST['category'])
					$query .= 'AND a2.category = "'.escape($_REQUEST['category']).'"';
				
				if (isset($_REQUEST['search']) && $_REQUEST['search']) {
					switch ($_REQUEST['search_type']) {
						case 'all':
							$query .= 'AND ((a2.title LIKE "%' . escape($_REQUEST['search']) . '%") OR (a2.content LIKE "%' . escape($_REQUEST['search']) . '%"))';
							break;
						
						case 'title':
							$query .= 'AND a2.title LIKE "%' . escape($_REQUEST['search']) . '%"';
							break;

						case 'writer':
							$query .= 'AND ' . $pfx . (USE_REAL_NAME ? 'user.user_name' : 'user.nick_name') . ' LIKE "%' . escape($_REQUEST['search']) . '%"';
							break;
					}
				}
				
				$query .= "ORDER BY IF(a2.top_no, a2.top_no, a2.no) DESC, order_key ASC LIMIT {$limitNum}, {$this->aop}";

				$row = DBHandler::for_table('article')
					->raw_query($query);

			}else {
				$row = DBHandler::for_table('article')
					->select_many('article.*', 'user.user_name', 'user.nick_name')
					->select_expr('(SELECT COUNT(*) FROM '.$pfx.'article_comment WHERE article_no = '.$pfx.'article.no)', 'comment_counts')
					->select_expr('(SELECT COUNT(*) FROM '.$pfx.'article_files WHERE article_no = '.$pfx.'article.no)', 'file_counts')
					->where('article.board_id', $this->boardId)
					->join('user', array(
						'user.id','=','article.writer_id'
					))
					->order_by_expr('IF ('.$pfx.'article.top_no, '.$pfx.'article.top_no, '.$pfx.'article.no) DESC')
					->order_by_asc('article.order_key')
					->limit($limitNum, $this->aop);


				if (isset($_REQUEST['category']) && $_REQUEST['category'])
					$row->where('article.category', $_REQUEST['category']);

				if (isset($_REQUEST['search']) && $_REQUEST['search']) {
					switch ($_REQUEST['search_type']) {
						case 'all':
							$row->where_raw('(('.$pfx.'article.title LIKE "%' . escape($_REQUEST['search']) . '%") OR ('.$pfx.'article.content LIKE "%' . escape($_REQUEST['search']) . '%"))');
							break;
						
						case 'title':
							$row->where_like('article.title', '%'.escape($_REQUEST['search']).'%');
							break;

						case 'writer':
							$row->where_like(USE_REAL_NAME ? 'user.user_name' : 'user.nick_name', '%'.escape($_REQUEST['search']).'%');
							break;
					}
				}
			}

			$data = $row->find_many();
			
			for ($i=0; $i<count($data); $i++) {
				if ($data[$i]->content === NULL)
					$data[$i]->is_delete = true;

				if ($data[$i]->is_secret) {
					if ($data[$i]->writer_id == User::getCurrent()->id || $this->view->isBoardAdmin)
						$data[$i]->secret_visible = true;
					else {
						$parentArticle = $this->getParentArticle($data[$i]->top_no, $data[$i]->order_key);
						if ($parentArticle && $parentArticle->writer_id == User::getCurrent()->id)
							$data[$i]->secret_visible = true;
					}
				}

				if ($data[$i]->category)
					$data[$i]->category = htmlspecialchars($data[$i]->category);

				$data[$i]->writer = USE_REAL_NAME ? $data[$i]->user_name : $data[$i]->nick_name;
				$data[$i]->is_reply = isset($data[$i]->parent_no);
				$data[$i]->upload_time2 = getRelativeTime(strtotime($data[$i]->upload_time));
				$data[$i]->title = htmlspecialchars($data[$i]->title);
			}
			return $data;
		}
		
		public function getNoticeArticles() {
			$data = DBHandler::for_table('article')
				->select_many('article.*', 'user.user_name', 'user.nick_name')
				->select_expr('(SELECT COUNT( * ) FROM '.DBHandler::$prefix.'article_comment WHERE article_no = '.DBHandler::$prefix.'article.no)', 'comment_counts')
				->where('article.board_id', $this->boardId)
				->where('article.is_notice', 1)
				->join('user', array(
					'user.id', '=', 'article.writer_id'
				))
				->order_by_desc('article.no')
				->find_many();
			
			for ($i=0; $i<count($data); $i++) {
				$data[$i]->writer = USE_REAL_NAME ? $data[$i]->user_name : $data[$i]->nick_name;
				$data[$i]->top_notice = true;
				$data[$i]->upload_time2 = getRelativeTime(strtotime($data[$i]->upload_time));
			}
			return $data;
		}
		
		public function getPageNumbers() {
			$obj = (object) array();

			$userId = (int)User::getCurrent()->id;
			$pfx = DBHandler::$prefix;
			$isBoardAdmin = $this->controller->checkIsBoardAdmin($boardInfo->admin_group);

			if ($this->view->boardInfo->hide_secret_article && !$isBoardAdmin) {
					$query = 
						"SELECT a2.no
						FROM {$pfx}article a1, {$pfx}article a2
						JOIN {$pfx}user ON {$pfx}user.id = a2.writer_id
						WHERE
						(
							(
								(a2.is_secret != 0)
								AND
								(
									(
										IF (a1.top_no, a1.top_no, a1.no) = a2.top_no
										AND a1.order_key <=> IF( LENGTH(a2.order_key)=2, NULL , SUBSTR(a2.order_key, 1, LENGTH(a2.order_key)-2)) 
										AND a1.writer_id = '{$userId}'
									)OR (
										a2.writer_id = '{$userId}'
										AND a2.no = a1.no
									)
								)
							)
							OR (
								a2.is_secret = '0'
								AND a2.no = a1.no
							)
						)";
					if (isset($_REQUEST['category']) && $_REQUEST['category'])
						$query .= 'AND a2.category = "'.escape($_REQUEST['category']).'"';
					
					if (isset($_REQUEST['search']) && $_REQUEST['search']) {
						switch ($_REQUEST['search_type']) {
							case 'all':
								$query .= 'AND ((a2.title LIKE "%' . escape($_REQUEST['search']) . '%") OR (a2.content LIKE "%' . escape($_REQUEST['search']) . '%"))';
								break;
							
							case 'title':
								$query .= 'AND a2.title LIKE "%' . escape($_REQUEST['search']) . '%"';
								break;

							case 'writer':
								$query .= 'AND ' . $pfx . (USE_REAL_NAME ? 'user.user_name' : 'user.nick_name') . ' LIKE "%' . escape($_REQUEST['search']) . '%"';
								break;
						}
					}
					$row = DBHandler::for_table('article')->raw_query($query);

			}else {
				$row = DBHandler::for_table('article')
					->select('no')
					->where('board_id', $this->boardId)
					->join('user', array(
						'user.id','=','article.writer_id'
					));

				if (isset($_REQUEST['category']) && $_REQUEST['category'])
					$row->where('article.category', $_REQUEST['category']);

				if (isset($_REQUEST['search']) && $_REQUEST['search']) {
					switch ($_REQUEST['search_type']) {
						case 'all':
							$row->where_raw('(('.$pfx.'article.title LIKE "%' . escape($_REQUEST['search']) . '%") OR ('.$pfx.'article.content LIKE "%' . escape($_REQUEST['search']) . '%"))');
							break;
						
						case 'title':
							$row->where_like('article.title', '%'.escape($_REQUEST['search']).'%');
							break;

						case 'writer':
							$row->where_like(USE_REAL_NAME ? 'user.user_name' : 'user.nick_name', '%'.escape($_REQUEST['search']).'%');
							break;
					}
				}

			}
			
			$result = $row->find_many();

			$totalPageNum = (int)((count($result)-1) / $this->aop) + 1;
			$tenDigit = (int)(($this->nowPage-1) / 10);
			
			$obj->prevBtn = ($tenDigit > 0) ? ($tenDigit * 10) : NULL;
			$obj->pages = array();
			$obj->nextBtn = ($tenDigit < (int)(($totalPageNum-1) / 10)) ? (($tenDigit + 1) * 10 + 1) : NULL;
			
			for ($i=($tenDigit)*10+1; $i<(($tenDigit)+1)*10+1; $i++) {
				if ($i > $totalPageNum) break;
				array_push($obj->pages, $i);
			}
			
			return $obj;
		}
		
	}