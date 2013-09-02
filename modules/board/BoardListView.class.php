<?php

	class BoardListView extends View {

		public $boardName;
		public $nowPage;
		public $pageNumbers;
		public $articleDatas;
		public $boardInfo;
		public $categorys;
		
		function dispList() {
			if (!$this->nowPage)
				self::execTemplate('board_not_found');

			else {
				$this->nowPage = $this->nowPage;
				$this->pageNumbers = $this->model->getPageNumbers();
				$this->articleData = array_merge(
					$this->model->getNoticeArticles(),
					$this->model->getArticleDatas($this->boardInfo)
				);
				self::execTemplate('board_list');
			}
		}


		function printArticlePrefix($orderKey, $category=NULL) {
			$html = '';

			if (!$orderKey)
				$html .= '<span class="dot-blank">&#183</span>';
			else {
				$depth = (int)(strlen($orderKey) / 2);
				for ($i=0; $i<$depth; $i++)
					$html .= '<span class="reply-blank">&nbsp;</span>';
				$html .= '<div class="reply-icon"></div>&nbsp;';
			}
			if ($category)
				$html .= '<span class="category">['.htmlspecialchars($category).']&nbsp;</span>';

			echo $html;
		}
	}
