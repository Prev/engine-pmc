<?php
	
	/**
	 * @ author prevdev@gmail.com
	 * @ 2013.05 ~ 07
	 *
	 *
	 * ModuleBase class
	 * Base class of modules
	 */
	
	abstract class ModuleBase {
		
		abstract public function init();
		abstract public function printContent();
		
		private static $controller;
		private static $view;
		private static $model;
		
		private static $instance;
		private static $initalized = false;
		
		protected $module;
		
		final public function __construct() {
			if (self::$initalized) return;
			
			self::$initalized = true;
			
			$moduleName = substr(get_class($this), 0, strrpos(get_class($this), 'Module'));
			$moduleName = strtolower($moduleName);	
			
			$this->loadMVCClass($moduleName, 'model');
			$this->loadMVCClass($moduleName, 'view');
			$this->loadMVCClass($moduleName, 'controller');
		}
		
		final public function content() {
			if ($GLOBALS['__ModuleActionFunc__']) {
				$GLOBALS['__ModuleActionFunc__']();
				unset($GLOBALS['__ModuleActionFunc__']);
			}
			$this->printContent();
		}
		
		private function loadMVCClass($moduleName, $type) {
			$type = strtolower($type);
			if (is_file(MODULE_DIR . "/${moduleName}.${type}.php")) {
				require MODULE_DIR . "/${moduleName}.${type}.php";
				
				if (class_exists(get_class($this) . '_' . ucfirst($type))) {
					$_loader = create_function('', 'return new '. get_class($this) . '_' . ucfirst($type) .'();');
					switch ($type) {
						case 'model' :
							self::$model = $_loader();
							break;
						case 'view' :
							self::$view = $_loader();
							break;
						case 'controller' :
							self::$controller = $_loader();
							break;
					}
				}
			}
		}
		
		public function execTemplate($templateName) {
			if (substr($templateName, strlen($templateName)-5, 5) != '.html')
				$templateName .= '.html';
			
			CacheHandler::execLayout(
				(substr($templateName, 0, 1) == '/') ?
					ROOT_DIR . $templateName :
					MODULE_DIR . '/template/' . $templateName
			);
			
		}
		
		public function getModel() {
			return self::$model;
		}
		
		public function getView() {
			return self::$view;
		}
		
		public function getController() {
			return self::$controller;
		}
	}