<?php
	
	class BoardFileDownController extends FileDownController {
		
		public function procDownloadFile() {
			$data = $this->model->getFileUrl($_GET['file'], $_GET['article_no']);
			if (!$data) {
				echo 'Invalid file';
				return;
			}
			parent::procDownloadFile('binaries', $data->file_name, $data->file_hash, $data->file_size);
		}

	}