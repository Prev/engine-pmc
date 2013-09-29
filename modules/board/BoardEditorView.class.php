<?php
	
	class BoardEditorView extends View {
		
		var $boardLists;
		var $boardName;
		var $title;

		public function init() {
			if (User::getCurrent() == NULL) {
				goLogin();
				return;
			}
		}
		
		public function dispEditor() {
			if (isset($_GET['article_no'])) {
				getContent('editor', 'dispEditor', array(
					'callbackUrl' => getUrl('board', 'procUpdateArticle'),
					'formInnerData' => array('board', 'dispUpdateEditorInnerData')
				));
			}else {
				getContent('editor', 'dispEditor', array(
					'callbackUrl' => getUrl('board', 'procSaveArticle'),
					'formInnerData' => array('board', 'dispEditorInnerData')
				));
			}
		}
		
		public function dispEditorInnerData() {
			$this->boardLists = $this->model->getBoardLists();
			$boardUseAble = false;

			if (isset($_GET['board_name'])) {
				for ($i=0; $i<count($this->boardLists); $i++) {
					if ($_GET['board_name'] == $this->boardLists[$i]->name)
						$boardUseAble = true;
				}
				if (!$boardUseAble) {
					goBack(array(
						'en' => 'Permission Denined',
						'ko' => '권한이 없습니다'
					));
					return;
				}
				$this->boardName = $_GET['board_name'];
			}
			if (isset($_GET['parent_no'])) {
				$data = $this->model->getArticleTitle((int)$_GET['parent_no']);
				$this->title = 'Re: ' . $data->title;
			}

			$this->execTemplate('editor_inner_data');
		}

		public function dispUpdateEditorInnerData() {
			if (!isset($_GET['article_no'])) {
				goBack(array(
					'en' => 'Cannot modify non-existing post',
					'ko' => '존재하지 않는 게시글을 수정할 수 없습니다'
				));
				return;
			}
			
			$this->boardLists = $this->model->getBoardLists();
			$this->articleData = $this->model->getArticleData($_GET['article_no']);
			$this->fileDatas = $this->model->getArticleFiles($_GET['article_no']);

			if (User::getCurrent()->id != $this->articleData->writer_id) {
				goBack(array(
					'en' => 'Permission Denined',
					'ko' => '권한이 없습니다'
				));
				return;
			}

			$this->execTemplate('update_editor_inner_data');
		}

		public function dispEditorInnerBottomData() {
			$this->articleData = $this->module->articleData;
			$this->execTemplate('editor_inner_bottom_data');
		}
	}