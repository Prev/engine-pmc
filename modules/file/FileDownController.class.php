<?php
	
	class FileDownController extends Controller {

		public function procDownloadFile() {
			$data = $this->model->getFileUrl($_GET['file'], $_GET['article_no']);
			
			$fileExtension = substr(strrchr($data->file_name, '.'), 1);
			$fileDir = ROOT_DIR . '/files/attach/' . ($data->is_binary ? 'binaries' : 'images') . '/' . $data->file_hash . (!$data->is_binary ? '.'.$fileExtension : '');
			
			if (!is_file($fileDir)) {
				echo '파일이 존재하지 않습니다.';
				exit;
			}

			header("Content-Type: application/octet-stream"); 
			header("Content-Disposition: attachment; filename=". $data->file_name); 
			header("Content-Length: ".$data->file_size); 
			header("Content-Transfer-Encoding: binary "); 
			header("Pragma: no-cache");
			header("Expires: 0");

			if (!file_exists($fileDir)) exit;

			flush();

			$fp = fopen($fileDir, 'r');
			while(!feof($fp))
				echo fgets($fp, 1024);

			fclose($fp);
		}

	}