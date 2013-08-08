<?php
	
	class FileDownController extends Controller {

		public function procDownloadFile() {
			$data = $this->model->getFileUrl($_GET['file'], $_GET['articleNo']);
			$fileDir = ROOT_DIR . $data->uploaded_url;

			header("Content-Type: application/octet-stream"); 
			header("Content-Disposition: attachment; filename=". $data->file_name); 
			header("Content-Length: ".$data->file_hash); 
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