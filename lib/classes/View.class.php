<?php
	
	/**
	 * @author prevdev@gmail.com
	 * @2013.07
	 *
	 *
	 * (abstract) View Class
	 */

	class View extends MVC {

		public $module;
		public $model;
		public $controller;
		
		final public function setMMVC($module, $model, $view, $controller) {
			$this->module = $module;
			$this->model = $model;
			$this->controller = $controller;
		}
		
		public function execTemplate($templateName) {
			if (substr($templateName, strlen($templateName)-5, 5) != '.html')
				$templateName .= '.html';
			
			if (Context::getInstance()->mobileMode && substr($templateName, 0, 1) != '/' && is_file(ModuleHandler::getModuleDir($this->module->moduleID) . '/template/m.' . $templateName))
				$templateName = 'm.' . $templateName;


			CacheHandler::execTemplate(
				(substr($templateName, 0, 1) == '/') ?
					ROOT_DIR . $templateName :
					ModuleHandler::getModuleDir($this->module->moduleID) . '/template/' . $templateName
			, $this->module);
			
		}
	}