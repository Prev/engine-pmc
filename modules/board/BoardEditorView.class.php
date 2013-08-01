<?php
	
	class BoardEditorView extends View {
		
		public function init() {
			if (User::getCurrent() == NULL) {
				goLogin();
				return;
			}
		}
		
		public function dispEditor() {
			getContent('editor', 'dispEditor', array(
				'callbackUrl' => getUrl('board', 'procSaveArticle'),
				'formInsertData' => array(
					'module' => 'board',
					'action' => 'dispEditorInsertData'
				),
				'formData' => array(
					
				)
			));
		}
		
		public function dispEditorInsertData() {
			$this->execTemplate('editor_insert_data');
		}
	}