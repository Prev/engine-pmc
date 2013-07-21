<?php
	
	class PageView extends View {
		
		public $page;

		public function dispDefault() {
			if (isset($this->page))
				$this->loadPage($this->page);
		}

		private function loadPage($page) {
			CacheHandler::execTemplate(
				(substr($templateName, 0, 1) == '/') ?
					ROOT_DIR . $templateName :
					$this->model->getPagePath($page)
			, $this->module);
			
		}

	}