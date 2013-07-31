<?php
	
	class FileController extends Controller {

		public function init() {
			if (!is_dir(ROOT_DIR . '/files/attach/')) {
				mkdir(ROOT_DIR . '/files/attach/');
				chmod(ROOT_DIR . '/files/attach/', 0755);
			}
			if (!is_dir(ROOT_DIR . '/files/attach/binaries/')) {
				mkdir(ROOT_DIR . '/files/attach/binaries/');
				chmod(ROOT_DIR . '/files/attach/binaries/', 0755);
			}
			if (!is_dir(ROOT_DIR . '/files/attach/images/')) {
				mkdir(ROOT_DIR . '/files/attach/images/');
				chmod(ROOT_DIR . '/files/attach/images/', 0755);
			}
		}

		public function procFileUpload() {
			$data = $this->_procUpload(true);

			echo '<script type="text/javascript">
				window.opener.appendFile("'.getUrl() . '/' . substr($data->uploadedUrl, 1).'", "'.$data->fileName.'", '.$data->fileSize.', "'.$data->fileMimeType.'", '.$data->fileId.');
				window.close();
			</script>';
		}

		public function procImageUpload() {
			$imageKind = array('image/pjpeg', 'image/jpeg', 'image/JPG', 'image/X-PNG', 'image/PNG', 'image/png', 'image/x-png');
			$imageExtensions = array('png', 'jpg', 'jpeg', 'gif', 'bmp');

			if (!in_array($_FILES['bifile']['type'], $imageKind) || !in_array(substr(strrchr($_FILES['bifile']['name'], '.'), 1), $imageExtensions)) {
				ErrorLogger::log('Attempt upload '.$_FILES['upload']['type'].' file as image');
				$this->close('Cannot upload this file as image');
				return;
			}
			$data = $this->_procUpload(false);
			echo '<script type="text/javascript">
				window.opener.appendImage("'.getUrl() . '/' . substr($data->uploadedUrl, 1).'", "'.$data->fileName.'", '.$data->fileSize.');
				window.close();
			</script>';
		}

		private function _procUpload($isBinary) {
			if (empty($_FILES['bifile'])) return;

			$fileName = basename($_FILES['bifile']['name']);
			$fileHash = sha1_file($_FILES['bifile']['tmp_name']);
			$fileSize = (int)$_FILES["bifile"]["size"];
			$fileExtension = substr(strrchr($_FILES['bifile']['name'], '.'), 1);
			
			if(
				$fileExtension === 'php' ||
				$fileExtension === 'css' ||
				$fileExtension === 'html' ||
				$fileExtension === 'js' ||
				$fileExtension === '.xhtml' ||
				$fileExtension === 'html'
			) {
				$this->close('Fail uploading file');
				ErrorLogger::log('Fatal Error: not allowed file extension');
				exit;
			}
			
			$uploadFileUrl = '/files/attach/' . ($isBinary ? 'binaries' : 'images') . '/' . $fileHash . ($isBinary ? '' : '.' . $fileExtension);
			$uploadFileDir = ROOT_DIR . $uploadFileUrl;
			
			if ($fileSize > 1024 * 1024 * 20) {
				$this->close('File size is upper than 20MB');
				return;
			}

			$record = DBHandler::for_table('files')
				->where('file_hash', $fileHash)
				->find_one();
			
			if ($record !== false) {
				// hash exists
				return (object) array(
					'fileId' => $record->id,
					'fileName' => $fileName,
					'fileHash' => $fileHash,
					'fileSize' => $fileSize,
					'fileMimeType' => $_FILES['bifile']['type'],
					'uploadedUrl' => $uploadFileUrl
				);
			}

			if (move_uploaded_file($_FILES['bifile']['tmp_name'], $uploadFileDir)) {
				$fileRecord = DBHandler::for_table('files')->create();
				$fileRecord->set(array(
					'uploaded_url' => $uploadFileUrl,
					'is_binary' => ($isBinary ? 1 : 0),
					'file_name' => $fileName,
					'file_size' => $fileSize,
					'file_hash' => $fileHash
				));
				$fileRecord->save();

				return (object) array(
					'fileId' => $fileRecord->id,
					'fileName' => $fileName,
					'fileHash' => $fileHash,
					'fileSize' => $fileSize,
					'fileMimeType' => $_FILES['bifile']['type'],
					'uploadedUrl' => $uploadFileUrl,
				);

			}else {
				$this->close('Fail uploading file');
				ErrorLogger::log('Fatal Error: fail to move_uploaded_file in FileController');
				exit;
			}
		}

		private function close($message=NULL) {
			if ($message)
				echo '<script type="text/javascript">alert("'.$message.'");window.close();</script>';
			else
				echo '<script type="text/javascript">window.close();</script>';
		}
	}
