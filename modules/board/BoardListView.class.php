<?php

	class BoardListView extends View {

		public $boardName;
		public $nowPage;
		public $pageNumbers;
		public $articleDatas;

		/**
			비밀글
			원본이 없어진 답글
		*/
		function dispList() {

			if (!$this->nowPage)
				self::execTemplate('board_not_found');

			else {
				$this->nowPage = $this->nowPage;
				$this->pageNumbers = $this->model->getPageNumbers();
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
