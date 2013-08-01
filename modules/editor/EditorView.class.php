<?php
	
	class EditorView extends View {

		var $callbackUrl;
		var $formInsertData;
		var $formData;

		public function init() {
			$this->callbackUrl = $this->module->callbackUrl;
			$this->formInsertData = $this->module->formInsertData;

			$this->formData = isset($this->module->formData) ? $this->module->formData : array();
		}

		public function dispEditor() {
			self::execTemplate('editor.html');
		}

		public function dispImagePopup() {
			self::execTemplate('pages/trex/image.html');
		}

		public function dispFilePopup() {
			self::execTemplate('pages/trex/file.html');
		}
		
	}