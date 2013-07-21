<?php
	
	/**
	 * @ author prevdev@gmail.com
	 * @ author luaviskang@gmail.com
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
			$path = self::getModuleDir($moduleID) . '/__' . ucfirst($moduleID) . 'Module.class.php';
			return (is_file($path) && is_readable($path)) ? true : false;
		}

		static public function getModule($moduleID) {
			return self::$modules->{$moduleID};
		}

		static public function getModuleDir($moduleID) {
			return ROOT_DIR . '/modules/' . $moduleID;
		}


		static public function initModule($moduleID, $moduleAction=NULL) {
			if (isset(self::$modules->{$moduleID})) return self::$modules->{$moduleID};

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
				$_module->action = $moduleActionData->name;
				$_module->actionData = $moduleActionData;
			}
			$_module->__initBase();
			$_module->init();
			
			if (method_exists($_module->model, 'init'))			$_module->model->init();
			if (method_exists($_module->controller, 'init'))	$_module->controller->init();
			if (method_exists($_module->view, 'init'))			$_module->view->init();

			return $_module;
		}

		
		static private function loadModule($moduleID) {
			if (!ModuleHandler::isModule($moduleID)) return NULL;
			
			$moduleDir = self::getModuleDir($moduleID);
			$filePath = $moduleDir . '/__' . ucfirst($moduleID) . 'Module.class.php';
			$classID = ucfirst(strtolower($moduleID)) . 'Module';

			if (is_file($moduleDir . '/info.json')) {
				self::$moduleInfos->{$moduleID} = json_decode(readFileContent($moduleDir . '/info.json'));
				if (self::$moduleInfos->{$moduleID} === NULL) {
					Context::printWarning(array(
						'en' => 'Unexpected token ILLEGAL in info.json',
						'kr' => 'info.json 파일 파싱에 실패했습니다'
					));
				}
				else if(isset(self::$moduleInfos->{$moduleID}->group)) {
					if(
						is_null(User::getCurrent()) &&
						User::getCurrent()->group_name != self::$moduleInfos->{$moduleID}->group) {
						Context::printErrorPage(array(
							'en' => 'Operation not permitted',
							'kr' => '권한이 없습니다.'
						));
					}
				}

				if (isset(self::$moduleInfos->{$moduleID}->layout))
					Context::getInstance()->setLayout(self::$moduleInfos->{$moduleID}->layout);
			}

			if(is_file($filePath)) {
				require $filePath;

				if(class_exists($classID)) {
					$_loader = create_function('', 'return new ' . $classID . '();');
					self::$modules->{$moduleID} = $_loader();
					self::$modules->{$moduleID}->moduleInfo = self::$moduleInfos->{$moduleID};
					return self::$modules->{$moduleID};
				}
			}
			$_loader = create_function('', 'return new Module();');
			self::$modules->{$moduleID} = $_loader();
			self::$modules->{$moduleID}->moduleInfo = self::$moduleInfos->{$moduleID};
			return self::$modules->{$moduleID};
		}
		
		static private function loadModuleAction($action, $module) {
			$moduleID = $module->moduleID;
			$moduleDir = self::getModuleDir($moduleID);
			
			// info.json 파일이 없으면 취소
			if (!is_file($moduleDir . '/info.json')) return;

			if (self::$moduleInfos->{$moduleID}) {
				$actions = self::$moduleInfos->{$moduleID}->actions; //array
				
				for ($i=0; $i<count($actions); $i++) {
					// action이 지정되지 않고 default action이 info.json에서 정의됬을 시 해당 action 실행
					if (!isset($action) && isset($actions[$i]->default) && $actions[$i]->default === true)
						$action = $actions[$i]->name;
					
					if ($action == $actions[$i]->name) {
						if (isset($actions[$i]->allow_web_access) && $actions[$i]->allow_web_access == false && isset(Context::getInstance()->moduleAction) &&Context::getInstance()->moduleAction == $action){
							Context::printErrorPage(array(
								'en' => 'Cannot execute module action - web access is not allowed',
								'kr' => '모듈 액션을 실행할 수 없습니다 - 웹 접근이 허용되지않음'
							));
							return NULL;
						}
						if (isset($actions[$i]->layout))
							Context::getInstance()->setLayout($actions[$i]->layout);
						return $actions[$i];
					}
				}
				if ($action === NULL) {
					Context::printErrorPage(array(
						'en' => 'Module action is not defined',
						'kr' => '모듈 액션이 정의되지 않았습니다.'
					));
				}
				else if(isset($action->private) && $action->private === true) {
					Context::printErrorPage(array(
						'en' => 'Module action is not opened',
						'kr' => '모듈 액션이 공개되어 있지 않습니다'
					));
				}
				else if(isset($action->group)) {
					if(!is_null(User::getCurrent())) {
						if(User::getCurrent()->group_name === $action->group) {
							return NULL;
						}
					}
					Context::printErrorPage(array(
						'en' => 'Operation not permitted',
						'kr' => '권한이 없습니다.'
					));
				}

				Context::printErrorPage(array(
					'en' => 'Cannot execute module action - permission denined by configuration file',
					'kr' => '모듈 액션을 실행할 수 없습니다 - Cofiguration 파일에 의해 권한이 거부됨'
				));
				return NULL;
			}
			return NULL;
		}
		
		
	}
	