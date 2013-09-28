<?php
	
	class FileUploadController extends Controller {

		protected $FILE_MAX_SIZE = 10485760; // 10 MB


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
			
			return $this->_procUpload('binaries', false);
		}

		public function procImageUpload() {
			if (!$_FILES['bifile']['size']) {
				$this->close();
				return;
			}
			$imageKind = array('image/pjpeg', 'image/jpeg', 'image/JPG', 'image/X-PNG', 'image/PNG', 'image/png', 'image/x-png', 'image/gif', 'image/GIF');
			$imageExtensions = array('png', 'jpg', 'jpeg', 'gif', 'bmp');

			$fileExtension = strtolower(substr(strrchr($_FILES['bifile']['name'], '.'), 1));

			if (!in_array($_FILES['bifile']['type'], $imageKind) || !in_array($fileExtension, $imageExtensions)) {
				ErrorLogger::log('Attempt upload '.$_FILES['upload']['type'].' file as image');
				$this->close(array(
					'en' => 'Cannot upload this file as image',
					'ko' => '이 파일을 이미지로 업로드 할 수 없습니다'
				));
				return;
			}
			return $this->_procUpload('images', true);
		}

		protected function _procUpload($fileType, $remainExtension) {
			if (empty($_FILES['bifile'])) return;

			$fileName = $_FILES['bifile']['name'];
			$fileHash = sha1_file($_FILES['bifile']['tmp_name']);
			$fileSize = (int)$_FILES['bifile']["size"];
			$fileExtension = strtolower(substr(strrchr($_FILES['bifile']['name'], '.'), 1));
			
			$uploadFileUrl = '/files/attach/' . $fileType . '/' . $fileHash . ($remainExtension ? '.' . $fileExtension : '');
			$uploadFileDir = ROOT_DIR . $uploadFileUrl;

			if ($fileSize > $this->FILE_MAX_SIZE) {
				$clearedMaxFileSize = getClearFileSize($this->FILE_MAX_SIZE);

				$this->close(array(
					'en' => 'Cannot upload file whose size is upper than ' . $clearedMaxFileSize,
					'ko' => $clearedMaxFileSize . '를 초과하는 파일은 업로드 할 수 없습니다'
				));
				return;
			}

			$fileData = $this->model->getFileData($fileType, $fileHash);
			
			if ($fileData !== false) {
				// hash exists
				return (object) array(
					'fileId' => $fileData->id,
					'fileName' => $fileName,
					'fileHash' => $fileHash,
					'fileSize' => $fileSize,
					'fileMimeType' => $_FILES['bifile']['type'],
					'uploadedUrl' => $uploadFileUrl
				);
			}

			if (move_uploaded_file($_FILES['bifile']['tmp_name'], $uploadFileDir)) {
				$fileRecord = $this->model->insertFileData($fileType, $fileHash, $fileSize);

				return (object) array(
					'fileId' => $fileRecord->id,
					'fileName' => $fileName,
					'fileHash' => $fileHash,
					'fileSize' => $fileSize,
					'fileMimeType' => $_FILES['bifile']['type'],
					'uploadedUrl' => $uploadFileUrl,
				);
				
			}else {
				$this->close(array(
					'en' => 'Fail uploading file',
					'ko' => '파일을 업로드 하는데 실패했습니다'
				));
				ErrorLogger::log('Fatal Error: fail to move_uploaded_file in FileController');
				exit;
			}
		}

		protected function close($message=NULL) {
			if (is_array($message) || is_object($message)) $message = fetchLocale($message);

			echo $message !== NULL ?
				('<script type="text/javascript">alert("'.$message.'");window.close();</script>'):
				('<script type="text/javascript">window.close();</script>');
		}
	}
