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
			
			if (Context::getInstance()->isMobileMode && substr($templateName, 0, 1) != '/' && is_file(ModuleHandler::getModuleDir($this->module->moduleID) . '/template/m.' . $templateName))
				$templateName = 'm.' . $templateName;

			if ($this->module->parentModuleID) {
				$backtrace = debug_backtrace();
				$filePath = $backtrace[0]['file'];

				$moduledir = substr($filePath, 0, strrpos($filePath, DIRECTORY_SEPARATOR));
				$moduledir = substr($moduledir, strlen(ROOT_DIR));
				
				$moduledir = str_replace(DIRECTORY_SEPARATOR, '/', $moduledir);
				$moduledir = ROOT_DIR . $moduledir;
				
			}else
				$moduledir = ModuleHandler::getModuleDir($this->module->moduleID);

			CacheHandler::execTemplate(
				(substr($templateName, 0, 1) == '/') ?
					ROOT_DIR . $templateName :
					$moduledir . '/template/' . $templateName
			, $this->module);
			
		}
	}