<?php

	class BoardArticleView extends View {

		public $articleNo;
		public $articleData;
		public $boardName;

		var $title;
		var $board;
		var $upload_time;
		var $writer;
		var $url;
		var $content;
		var $fileDatas;

		function dispArticle() {
			$articleData = $this->articleData;

			if (!$this->articleNo)
				self::execTemplate('article_not_found');
			else {
				if (!$articleData) {
					self::execTemplate('article_not_found');
					return;
				}
				
				$this->title = $articleData->title;
				$this->board = ($articleData->boardName_locale ? $articleData->boardName_locale : $articleData->boardName);
				$this->upload_time = $articleData->upload_time;
				$this->writer = $articleData->writerNick;
				$this->url = (USE_SHORT_URL ? 
					getUrl() . '/' . $this->articleNo :
					getUrl('board', 'dispArticle', array('article_no' => $this->articleNo))
				);
				$this->content = $articleData->content;
				$this->attachFiles = $articleData->attach_files;

				$this->prevArticle = NULL;
				$this->nextArticle = NULL;

				self::execTemplate('article');
			}
		}

	}
