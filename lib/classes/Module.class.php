<?php
	
	/**
	 * @ author prevdev@gmail.com
	 * @ 2013.05 ~ 07
	 *
	 *
	 * ModuleBase class
	 * Base class of modules
	 */
	
	abstract class Module {
		
		abstract public function init();
		
		public $moduleID;
		public $action;
		public $actionTarget;

		public $controller;
		public $view;
		public $model;
		
		final public function __construct() {
			$this->moduleID = substr(get_class($this), 0, strrpos(get_class($this), 'Module'));
			$this->moduleID = strtolower($this->moduleID);	
			
			$this->loadMVCClass($this->moduleID, 'model');
			$this->loadMVCClass($this->moduleID, 'view');
			$this->loadMVCClass($this->moduleID, 'controller');

			foreach(array('model', 'view', 'controller') as $key => $mvc) {
				if (!$this->{$mvc}) continue;

				$this->{$mvc}->setProperties(
					$this,
					$this->model,
					$this->view,
					$this->controller
				);
			}

			if (method_exists($this->model, 'init'))		$this->model->init();
			if (method_exists($this->controller, 'init'))	$this->controller->init();
			if (method_exists($this->view, 'init'))			$this->view->init();
		}
		
		final public function exec() {
			if ($this->action && $this->actionTarget)
				call_user_method($this->action, $this->{$this->actionTarget});

			else
				$this->view->dispDefault();
		}
		
		private function loadMVCClass($moduleID, $type) {
			$type = strtolower($type);
			if (is_file(ModuleHandler::getModuleDir($moduleID) . "/${moduleID}.${type}.php")) {
				require ModuleHandler::getModuleDir($moduleID) . "/${moduleID}.${type}.php";
				
				if (class_exists(get_class($this) . '_' . ucfirst($type))) {
					$_loader = create_function('', 'return new '. get_class($this) . '_' . ucfirst($type) .'();');
					$this->{$type} = $_loader();
				}
			}
		}
	}