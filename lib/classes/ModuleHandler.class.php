<?php
	
	/**
	 * @ author prevdev@gmail.com
	 * @ 2013.05
	 *
	 *
	 * ModuleHandler Class
	 * Load and handling modules
	 */
	
	class ModuleHandler extends Handler {
		
		static $modules;
		static $moduleInfos;
		
		static public function init() {
			self::$modules = new stdClass();
			self::$moduleInfos = new stdClass();
		}

		static public function isModule($moduleID) {
			$path = self::getModuleDir($moduleID) . '/__module.php';
			return (is_file($path) && is_readable($path)) ? true : false;
		}

		static public function getModule($moduleID) {
			return self::$modules->{$moduleID};
		}

		static public function getModuleDir($moduleID) {
			return ROOT_DIR . '/modules/' . $moduleID;
		}


		static public function initModule($moduleID, $moduleAction=NULL) {
			if (self::$modules->{$moduleID}) return self::$modules->{$moduleID};

			$moduleDir = self::getModuleDir($moduleID);
			if (!$moduleID) {
				Context::printErrorPage(array(
					'en' => 'Cannot load module - module not defined',
					'kr' => '모듈을 불러올 수 없습니다 - 모듈이 정의되지 않음'
				));
				return;
				
			}else if (!ModuleHandler::isModule($moduleID)) {
				Context::printErrorPage(array(
					'en' => 'Cannot load module - module not found',
					'kr' => '모듈을 불러올 수 없습니다 - 모듈을 찾을 수 없음'
				));
				return;
			}
			
			$_module = self::loadModule($moduleID);
			if ($_module === NULL) {
				Context::printErrorPage(array(
					'en' => 'Cannot load module in ModuleHandler::procModule',
					'kr' => '모듈을 불러올 수 없습니다 - ModuleHandler::procModule'
				));
				return;
			}
			if ($moduleActionData = self::loadModuleAction($moduleAction ? $moduleAction : NULL, $_module)) {
				$_module->action = $moduleActionData[0];
				$_module->actionTarget = $moduleActionData[1];
			}
			$_module->init();
			return $_module;
		}

		
		static private function loadModule($moduleID) {
			if (!ModuleHandler::isModule($moduleID)) return NULL;
			
			$moduleDir = self::getModuleDir($moduleID);
			$filePath = $moduleDir . '/__module.php';
			$classID = ucfirst(strtolower($moduleID)) . 'Module';
			
			require $filePath;
			
			if (class_exists($classID)) {
				if (is_file($moduleDir . '/conf/info.json')) {
					self::$moduleInfos->{$moduleID} = json_decode(readFileContent($moduleDir . '/conf/info.json'));
					if (self::$moduleInfos->{$moduleID} === NULL)
						Context::printWarning(array(
							'en' => 'Unexpected token ILLEGAL in conf/info.json',
							'kr' => 'conf/info.json 파일 파싱에 실패했습니다'
						));
					if (self::$moduleInfos->{$moduleID}->layout)
						Context::getInstance()->setLayout(self::$moduleInfos->{$moduleID}->layout);
				}
				
				$_loader = create_function('', "return new ${classID}();");
				self::$modules->{$moduleID} = $_loader();
				return self::$modules->{$moduleID};
			}
			return NULL;
		}
		
		static private function loadModuleAction($action, $module) {
			$moduleID = $module->moduleID;
			$moduleDir = self::getModuleDir($moduleID);

			// conf/info.json 파일이 없으면 취소
			if (!is_file($moduleDir . '/conf/info.json')) return;

			if (self::$moduleInfos->{$moduleID}) {
				$actions = self::$moduleInfos->{$moduleID}->actions; //array
				
				for ($i=0; $i<count($actions); $i++) {
					// action이 지정되지 않고 default action이 conf/info.json에서 정의됬을 시 해당 action 실행
					if (!$action && $actions[$i]->default === true) {
						$action = $actions[$i]->name;
					}
					if ($action == $actions[$i]->name) {
						$o = $module->{$actions[$i]->type};

						if (method_exists($o, $action)) {
							if ($actions[$i]->layout) 
								Context::getInstance()->setLayout($actions[$i]->layout);
							return array($action, $actions[$i]->type);
						}else {
							Context::printErrorPage(array(
								'en' => 'Cannot execute module action - method do not exists',
								'kr' => '모듈 액션을 실행할 수 없습니다 - 메소드가 존재 하지 않음'
							));
							return NULL;
						}
					}
				}
				if ($action === NULL) return;
				Context::printErrorPage(array(
					'en' => 'Cannot execute module action - permission denined by configuration file',
					'kr' => '모듈 액션을 실행할 수 없습니다 - Cofiguration 파일에 의해 권한이 거부됨'
				));
				return NULL;
			}
			return NULL;
		}
		
		
	}
	