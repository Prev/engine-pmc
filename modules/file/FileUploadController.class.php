<?php
	
	class FileUploadController extends Controller {

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
			if (!$_FILES['bifile']['size']) {
				$this->close();
				return;
			}
			
			return $this->_procUpload(true);
		}

		public function procImageUpload() {
			if (!$_FILES['bifile']['size']) {
				$this->close();
				return;
			}
			$imageKind = array('image/pjpeg', 'image/jpeg', 'image/JPG', 'image/X-PNG', 'image/PNG', 'image/png', 'image/x-png', 'image/gif', 'image/GIF');
			$imageExtensions = array('png', 'jpg', 'jpeg', 'gif', 'bmp');

			if (!in_array($_FILES['bifile']['type'], $imageKind) || !in_array(substr(strrchr($_FILES['bifile']['name'], '.'), 1), $imageExtensions)) {
				ErrorLogger::log('Attempt upload '.$_FILES['upload']['type'].' file as image');
				$this->close('Cannot upload this file as image');
				return;
			}
			return $this->_procUpload(false);
		}

		private function _procUpload($isBinary) {
			if (empty($_FILES['bifile'])) return;

			$fileName = $_FILES['bifile']['name'];
			$fileHash = sha1_file($_FILES['bifile']['tmp_name']);
			$fileSize = (int)$_FILES["bifile"]["size"];
			$fileExtension = substr(strrchr($_FILES['bifile']['name'], '.'), 1);
			
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
					'is_binary' => ($isBinary ? 1 : 0),
					'file_hash' => $fileHash,
					'file_size' => $fileSize
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
