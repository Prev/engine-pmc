<?php
	
	class FileDownController extends Controller {

		public function procDownloadFile($fileType, $fileName, $fileHash, $fileSize) {
			$fileDir = ROOT_DIR . '/files/attach/' . $fileType . '/' . $fileHash;
			
			if (!is_file($fileDir)) {
				echo fetchLocale(array(
					'en' => 'file not exists',
					'ko' => '파일이 존재하지 않습니다.'
				));
				exit;
			}

			header('Content-Type: application/octet-stream'); 
			header('Content-Disposition: attachment; filename='. $fileName); 
			header('Content-Length: '.$fileSize); 
			header('Content-Transfer-Encoding: binary'); 
			header('Pragma: no-cache');
			header('Expires: 0');

			if (!file_exists($fileDir)) exit;
			
			flush();

			$fp = fopen($fileDir, 'r');
			while(!feof($fp))
				echo fgets($fp, 1024);

			fclose($fp);
		}

	}