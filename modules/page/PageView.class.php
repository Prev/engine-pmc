<?php
	
	class PageView extends View {
		
		public $page;

		public function dispDefault() {
			if (isset($this->page))
				$this->loadPage($this->page);
		}

		private function loadPage($page) {
			CacheHandler::execTemplate($this->model->getPagePath($page), $this->module);
		}

	}