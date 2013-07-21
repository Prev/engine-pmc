<?php
	
	class PageView extends View {
		
		public function dispDefault() {
			$page = $this->model->getPageName();

			if (!isset($page) || !$page)
				Context::printErrorPage(array(
					'en' => 'page is not defined',
					'kr' => '페이지가 정의되지 않았습니다'
				));
			else {
				if (!is_file($this->getPagePath($page))) {
					Context::printErrorPage(array(
						'en' => 'cannot load page "'.$page.'"',
						'kr' => '페이지 "'.$page.'" 를 불러올 수 없습니다.'
					));
				}
				$this->loadPage($page);
			}

		}

		private function getPagePath($page) {
			return ModuleHandler::getModuleDir($this->module->moduleID) .
				'/pages/' . $page . '/' . $page . '.html';
		}

		private function loadPage($page) {
			CacheHandler::execTemplate(
				(substr($templateName, 0, 1) == '/') ?
					ROOT_DIR . $templateName :
					$this->getPagePath($page)
			, $this->module);
			
		}

	}