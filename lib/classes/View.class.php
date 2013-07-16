<?php
	
	class View extends MVC {

		protected $module;
		protected $model;
		protected $controller;
		
		final public function setMMVC($module, $model, $view, $controller) {
			$this->module = $module;
			$this->model = $model;
			$this->controller = $controller;
		}
		
		public function execTemplate($templateName) {
			if (substr($templateName, strlen($templateName)-5, 5) != '.html')
				$templateName .= '.html';
			
			CacheHandler::execLayout(
				(substr($templateName, 0, 1) == '/') ?
					ROOT_DIR . $templateName :
					ModuleHandler::getModuleDir($this->module->moduleID) . '/template/' . $templateName
			, $this->module);
			
		}
	}