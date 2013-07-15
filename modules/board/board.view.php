<?php

	class BoardModule_View extends View {

		public $boardId;
		public $boardInfo;
		public $articleNo;
		
		public $boardName;
		public $aop;
		public $nowPage;

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
		
		function dispDefault() {
		}

		function dispList() {
			if (!$this->boardInfo)
				self::execTemplate('board_not_found');

			else {
				Context::set('nowPage', $this->nowPage);
				Context::set('pageNumbers', $this->model->getPageNumbers());
				Context::set('QS', substr(
					($_GET['board_name'] ? '&board_name=' . $_GET['board_name']  : '') .
					($_GET['aop'] ? '&aop=' . $_GET['aop']  : '') 
					,1)
				);
				Context::set('articleData', array_merge(
					$this->model->getNoticeArticles(),
					$this->model->getArticleDatas()
					)
				);
				//var_dump2(Context::$attr);
				self::execTemplate('board');
			}
		}

		function dispArticle() {
			if (!$this->articleNo)
				self::execTemplate('article_not_found');

			else {
				$data = $this->model->getArticleData();
				if (!$data) {
					self::execTemplate('article_not_found');
					return;
				}
				//if ($data->read_permission)

				Context::set('title', $data->title);
				Context::set('board', ($data->boardName_kr ? $data->boardName_kr : $data->boardName));
				Context::set('upload_time', $data->upload_time);
				Context::set('writer', $data->writerNick);
				Context::set('url', (USE_SHORT_URL ? 
					getUrl() . '/' . $this->articleNo :
					getUrl('board', 'dispArticle', array(article_no=>$this->articleNo))
					));
				Context::set('content', $data->content);

				self::execTemplate('article');
			}
		}

	}
