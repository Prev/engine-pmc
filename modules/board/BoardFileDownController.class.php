<?php
	
	class BoardFileDownController extends FileDownController {
		
		public function procDownloadFile($fileType=NULL, $fileName=NULL, $fileHash=NULL, $fileSize=NULL) {
			$data = $this->model->getFileData($_GET['file'], $_GET['article_no']);
			if (!$data) {
				echo 'Invalid file';
				return;
			}
			
			if ($data->readable_group) {
				$me = User::getCurrent();
				if (!$me || !$me->checkGroup($data->readable_group)) {
					echo 'Permission denined';
					return;
				}
			}

			parent::procDownloadFile('binaries', $data->file_name, $data->file_hash, $data->file_size);
		}

	}