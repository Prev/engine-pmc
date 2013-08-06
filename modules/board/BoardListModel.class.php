<?php
	
	class BoardListModel extends Model {
		
		public $boardId;
		public $aop;
		public $nowPage;

		function getBoardInfo($boardName) {
			return DBHandler::for_table('board')
				->where('name', $boardName)
				->find_one();
		}

		function getArticleDatas() {
			$limitNum = ($this->nowPage - 1) * $this->aop;

			$pfx = DBHandler::$prefix;

			$data = DBHandler::for_table('article')
				->select_many('article.*', 'user.user_name')
				->select_expr('(SELECT COUNT(*) FROM '.$pfx.'article_comment WHERE article_no = '.$pfx.'article.no)', 'comment_counts')
				->select_expr('(SELECT COUNT(*) FROM '.$pfx.'article_files WHERE article_no = '.$pfx.'article.no)', 'file_counts')
				->where('article.board_id', $this->boardId)
				->join('user', array(
					'user.id','=','article.writer_id'
				))
				->order_by_desc('article.top_no')
				->order_by_asc('article.order_key')
				->limit($limitNum, $this->aop)
				->find_many();
			
			for ($i=0; $i<count($data); $i++) {
				if ($data[$i]->content === NULL)
					$data[$i]->is_delete = true;
				
				$data[$i]->is_reply = isset($data[$i]->parent_no);
				$data[$i]->upload_time2 = getRelativeTime(strtotime($data[$i]->upload_time));
			}
			return $data;
		}
		
		function getNoticeArticles() {
			$data = DBHandler::for_table('article')
				->select_many('article.*', 'user.user_name')
				->select_expr('(SELECT COUNT( * ) FROM '.DBHandler::$prefix.'article_comment WHERE article_no = '.DBHandler::$prefix.'article.no)', 'comment_counts')
				->where('article.board_id', $this->boardId)
				->where('article.is_notice', 1)
				->join('user', array(
					'user.id', '=', 'article.writer_id'
				))
				->order_by_desc('article.no')
				->find_many();
			
			for ($i=0; $i<count($data); $i++) {
				$data[$i]->top_notice = true;
				$data[$i]->upload_time2 = getRelativeTime(strtotime($data[$i]->upload_time));
			}
			return $data;
		}
		
		function getPageNumbers() {
			$obj = (object) array();
			
			$result = DBHandler::for_table('article')
				->select('no')
				->where('board_id', $this->boardId)
				->find_many();

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