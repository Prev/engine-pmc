<?php

	class BoardListView extends View {

		public $nowPage;

		var $pageNumbers;
		var $QS;
		var $articleDatas;

		function dispList() {

			if (!$this->nowPage)
				self::execTemplate('board_not_found');

			else {
				$this->nowPage = $this->nowPage;
				$this->pageNumbers = $this->model->getPageNumbers();
				$this->QS = substr(
					(isset($_GET['board_name']) ? '&board_name=' . $_GET['board_name']  : '') .
					(isset($_GET['aop']) ? '&aop=' . $_GET['aop']  : '') 
				, 1);
				$this->articleData = array_merge(
					$this->model->getNoticeArticles(),
					$this->model->getArticleDatas()
				);
				self::execTemplate('board');
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
				$html .= '<span class="tag-type">['.$category.']&nbsp;</span>';

			echo $html;
		}
	}
