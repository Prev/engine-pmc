<?php
	
	class BoardEditorController extends Controller {

		public function init() {
			if (User::getCurrent() == NULL) {
				goLogin();
				return;
			}
		}
		
		public function procSaveArticle() {
			$record = DBHandler::for_table('article')->create();
			$record->set(
				'board_id'
			);
			$_POST['content'];
			$_POST['attach_file'];
		}

	}