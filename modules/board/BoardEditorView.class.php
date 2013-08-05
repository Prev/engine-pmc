<?php
	
	class BoardEditorView extends View {
		
		var $boardLists;
		var $boardName;

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

			$this->execTemplate('editor_inner_data');
		}
	}