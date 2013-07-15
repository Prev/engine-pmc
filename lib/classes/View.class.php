<?php
	
	abstract class View {

		protected $module;
		protected $model;
		protected $controller;

		//abstract public function init();
		abstract public function dispDefault();

		final public function setProperties($module, $model, $view, $controller) {
			$this->module = $module;
			$this->model = $model;
			//$this->view = $view;
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