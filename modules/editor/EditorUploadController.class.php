<?php
	
	class EditorUploadController extends FileUploadController {

		protected $FILE_MAX_SIZE; // override

		public function init() {
			$this->FILE_MAX_SIZE = $this->module->FILE_MAX_SIZE;
			parent::init();
		}
		
		public function procFileUpload() {
			$data = parent::procFileUpload();

			echo '<script type="text/javascript">
				window.opener.appendFile("'.getUrl() . '/' . substr($data->uploadedUrl, 1).'", "'.$data->fileName.'", '.$data->fileSize.', "'.$data->fileMimeType.'", '.$data->fileId.');
				window.close();
			</script>';
		}

		public function procImageUpload() {
			$data = parent::procImageUpload();
			
			echo '<script type="text/javascript">
				window.opener.appendImage("'.getUrl() . '/' . substr($data->uploadedUrl, 1).'", "'.$data->fileName.'", '.$data->fileSize.');
				window.close();
			</script>';
		}

	}