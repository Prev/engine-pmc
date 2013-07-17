<?php
	
	class BoardListModel extends Model {
		
		public $boardId;
		public $aop;
		public $nowPage;

		function getBoardInfo($boardName) {
			return DBHandler::execQueryOne("
				SELECT * FROM (#)board
				WHERE name='${boardName}'
				LIMIT 1
			");
		}

		function getArticleDatas() {
			$limitNum = ($this->nowPage - 1) * $this->aop;
			$data = DBHandler::execQuery("
				SELECT (#)article.*, (#)user.user_name FROM `(#)article`,`(#)user`
				WHERE (#)article.board_id = '{$this->boardId}'
					AND (#)user.id = (#)article.writer_id
				ORDER BY (#)article.top_no DESC, (#)article.order_key ASC
				LIMIT $limitNum,{$this->aop}
			");
			
			for ($i=0; $i<count($data); $i++) {
				$data[$i]->is_reply = isset($data[$i]->parent_no);
				$data[$i]->upload_time2 = getRelativeTime(strtotime($data[$i]->upload_time));
			}
			return $data;
		}
		
		function getNoticeArticles() {
			$data = DBHandler::execQuery("
				SELECT (#)article.*, (#)user.user_name FROM `(#)article`,`(#)user`
				WHERE (#)article.board_id = '{$this->boardId}'
					AND is_notice = '1'
					AND (#)user.id = (#)article.writer_id
				ORDER BY (#)article.no DESC
			");
			
			for ($i=0; $i<count($data); $i++) {
				$data[$i]->top_notice = true;
				$data[$i]->upload_time2 = getRelativeTime(strtotime($data[$i]->upload_time));
			}
			return $data;
		}
		
		function getPageNumbers() {
			$obj = (object) array();
			
			$result = DBHandler::execQuery("
				SELECT no FROM (#)article
				WHERE board_id='{$this->boardId}'
			");
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