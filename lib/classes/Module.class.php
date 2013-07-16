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
		public $actionData;
		public $moduleInfo;

		public $controller;
		public $view;
		public $model;
		
		final public function __construct() {
			$this->moduleID = substr(get_class($this), 0, strrpos(get_class($this), 'Module'));
			$this->moduleID = strtolower($this->moduleID);	
		}

		final public function __initBase() {
			$ufModuleID = ucfirst($this->moduleID);
			$actionData = $this->actionData ? $this->actionData : new StdClass();

			foreach(array('model', 'view', 'controller') as $key => $mvc) {
				if ($actionData->{$mvc})
					$this->{$mvc} = $this->loadMVCClass(ucfirst($actionData->{$mvc}), false);
				else if ($this->moduleInfo->{'default_'.$mvc})
					$this->{$mvc} = $this->loadMVCClass($this->moduleInfo->{'default_'.$mvc}, false);
				else
					$this->{$mvc} = $this->loadMVCClass(ucfirst($mvc), true);

				$this->{$mvc}->setMMVC(
					$this,
					$this->model,
					$this->view,
					$this->controller
				);
			}
		}
		
		final public function exec() {
			foreach(array('model', 'view', 'controller') as $key => $mvc) {
				if (method_exists($this->{$mvc}, $this->action))
					$this->{$mvc}->{$this->action}();
			}
		}
		
		private function loadMVCClass($className, $isGlobalFile) {
			if ($isGlobalFile) {
				$_loader = create_function('', 'return new '. $className .'();');
				return $_loader();
			}else {
				$classPath = ModuleHandler::getModuleDir($this->moduleID) . '/' . $className . '.class.php';
				
				if (!is_file($classPath)) {
					Context::printErrorPage(array(
						'en'=>'Cannot load lass "'. $className . '" - cannot load file',
						'kr'=>'클래스 "'. $className . '" 를 불러 올 수 없습니다. - 파일을 불러 올 수 없음'
					)); 
				}else {
					require $classPath;
					if (!class_exists($className)) {
						Context::printErrorPage(array(
							'en'=>'Cannot load lass "'. $className . '" - cannot find class',
							'kr'=>'클래스 "'. $className . '" 를 불러 올 수 없습니다. - 클래스를 찾을 수 없음'
						)); 
					}
					$_loader = create_function('', 'return new '. $className .'();');
					return $_loader();
				}
			}
		}
	}