<?php
	
	class BoardEditorView extends View {
		
		var $boardLists;
		var $boardName;
		var $title;

		/**
			게시판 카테고리
		*/

		public function init() {
			if (User::getCurrent() == NULL) {
				goLogin();
				return;
			}
		}
		
		public function dispEditor() {
			getContent('editor', 'dispEditor', array(
				'callbackUrl' => getUrl('board', 'procSaveArticle'),
				'formInnerData' => array('board', 'dispEditorInnerData')
			));
		}
		
		public function dispEditorInnerData() {
			$this->boardLists = $this->model->getBoardLists();
			
			if (isset($_GET['board_name']))
				$this->boardName = $_GET['board_name'];

			if (isset($_GET['parent_no'])) {
				$data = $this->model->getArticleTitle((int)$_GET['parent_no']);
				$this->title = 'Re: ' . $data->title;
			}

			$this->execTemplate('editor_inner_data');
		}
	}