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
				'formData' => array(
					
				)
			));
		}
		
	}