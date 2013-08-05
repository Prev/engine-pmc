<?php
	
	class FileDownController extends Controller {

		public function procDownloadFile() {
			$fileHash = basename($_GET['file']);

			$data = $this->model->getFileUrl($fileHash);
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