<?php

	class BoardListView extends View {

		public $boardName;
		public $nowPage;
		public $pageNumbers;
		public $articleDatas;
		public $commonArticleDatas;
		public $boardInfo;
		public $categorys;
		
		function dispList() {
			if (!$this->boardInfo)
				self::execTemplate('board_not_found');

			else {
				$this->nowPage = $this->nowPage;
				$this->pageNumbers = $this->model->getPageNumbers();
				$this->commonArticleDatas = $this->model->getArticleDatas($this->boardInfo);

				$this->articleDatas = $this->controller->manufactureArticleDatas(
					array_merge(
						$this->model->getNoticeArticles(),
						$this->commonArticleDatas
					)
				);
				self::execTemplate('board_list');
			}
		}


		function printArticlePrefix($orderKey, $category=NULL) {
			$html = '';

			if (!$orderKey || (isset($_REQUEST['search']) || $_REQUEST['search']))
				$html .= '<span class="dot-blank">&#183</span>';
			else {
				$depth = (int)(strlen($orderKey) / 2);
				for ($i=0; $i<$depth; $i++)
					$html .= '<span class="reply-blank">&nbsp;</span>';
				$html .= '<div class="reply-icon"></div>&nbsp;';
			}
			if ($category)
				$html .= '<span class="category">['.$category.']&nbsp;</span>';

			echo $html;
		}
	}
