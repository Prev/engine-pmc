<?php
	
	class EditorView extends View {

		var $callbackUrl;
		var $formInnerData;
		var $formData;

		public function init() {
			$this->callbackUrl = $this->module->callbackUrl;
			$this->formInnerData = $this->module->formInnerData;
		}

		public function dispEditor() {
			self::execTemplate('editor.html');
		}

		public function dispFormInnerData() {
			self::execTemplate('editor_inner.html');
		}

		public function dispImagePopup() {
			self::execTemplate('pages/trex/image.html');
		}

		public function dispFilePopup() {
			self::execTemplate('pages/trex/file.html');
		}
		
	}