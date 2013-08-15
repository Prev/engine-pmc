<?php
	
	class BoardModel extends Model {

		public function getParentArticle($topNo, $orderKey) {
			$orderKey = substr($orderKey, 0, strlen($orderKey)-2);

			if ($orderKey == '') {
				return DBHandler::for_table('article')
					->where('no', $topNo)
					->find_one();
			}else {
				return DBHandler::for_table('article')
					->where('top_no', $topNo)
					->where('order_key', $orderKey)
					->find_one();
			}
		}
		
	}