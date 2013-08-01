<?php
	
	/**
	 * @author prevdev@gmail.com, luaviskang@gmail.com
	 * @2013.05
	 *
	 *
	 * self Class
	 * Load and handling modules
	 */
	
	class ModuleHandler extends Handler {
		
		static $modules;
		static $moduleInfos;
		
		static public function init() {
			self::$modules = new stdClass();
			self::$moduleInfos = new stdClass();
		}

		static public function moduleExists($moduleID) {
			return is_dir(self::getModuleDir($moduleID));
		}
		static public function customModuleExists($moduleID) {
			$path = self::getModuleDir($moduleID) . '/' . ucfirst($moduleID) . 'Module.class.php';
			return (is_file($path) && is_readable($path)) ? true : false;
		}

		static public function getModule($moduleID, $moduleAction) {
			return self::$modules->{$moduleID.'.'.$moduleAction};
		}

		static public function getModuleDir($moduleID) {
			return ROOT_DIR . '/modules/' . $moduleID;
		}


		static public function initModule($moduleID, $moduleAction=NULL, $queryParam=NULL) {
			if (isset(self::$modules->{$moduleID.'.'.$moduleAction})) return self::$modules->{$moduleID.'.'.$moduleAction};

			$moduleDir = self::getModuleDir($moduleID);
			if (!$moduleID) {
				Context::printErrorPage(array(
					'en' => 'Cannot load module - module not defined',
					'kr' => '모듈을 불러올 수 없습니다 - 모듈이 정의되지 않음'
				));
				return;
				
			}else if (!self::moduleExists($moduleID)) {
				Context::printErrorPage(array(
					'en' => 'Cannot load module - module not found',
					'kr' => '모듈을 불러올 수 없습니다 - 모듈을 찾을 수 없음'
				));
				return;
			}
			
			$_module = self::loadModule($moduleID, $moduleAction);
			if ($_module === NULL) {
				Context::printErrorPage(array(
					'en' => 'Cannot load module in self::procModule',
					'kr' => '모듈을 불러올 수 없습니다 - self::procModule'
				));
				return;
			}
			if ($moduleActionData = self::loadModuleAction($moduleAction ? $moduleAction : NULL, $_module)) {
				$_module->action = $moduleActionData->name;
				$_module->actionData = $moduleActionData;
			}
			
			$_module->__initBase();
			$_module->init();

			if ($queryParam) {
				if (is_string($queryParam))
					$queryParam = urlQueryToArray($queryParam);
				foreach ($queryParam as $key => $value) {
					$_module->{$key} = $value;
				}
			}
			
			if (method_exists($_module->model, 'init'))			$_module->model->init();
			if (method_exists($_module->controller, 'init'))	$_module->controller->init();
			if (method_exists($_module->view, 'init'))			$_module->view->init();


			return $_module;
		}

		
		static private function loadModule($moduleID, $moduleAction) {
			if (!self::moduleExists($moduleID)) return NULL;

			$moduleDir = self::getModuleDir($moduleID);
			
			// if (ModuleName)Module.class.php exists, load it
			// else, default Module class
			if (self::customModuleExists($moduleID)) {
				if (!class_exists(ucfirst($moduleID) . 'Module'))
					require $moduleDir . '/' . ucfirst($moduleID) . 'Module.class.php';

				$classID = ucfirst(strtolower($moduleID)) . 'Module';

			}else
				$classID = 'Module';


			if (!class_exists($classID)) {
				Context::printErrorPage(array(
					'en' => 'Cannot initialize module - Cannot find module class',
					'kr' => '모듈을 초기화 할 수 없습니다 - 클래스를 찾을 수 없습니다.'
				));
			}
			if (!is_file($moduleDir . '/info.json')) {
				Context::printErrorPage(array(
					'en' => 'Cannot initialize module - info.json file not exists',
					'kr' => '모듈을 초기화 할 수 없습니다 - info.json 파일이 존재하지 않습니다'
				));
				return;
			}


			$moduleInfo = json_decode(readFileContent($moduleDir . '/info.json'));
			
			if ($moduleInfo === NULL) {
				Context::printWarning(array(
					'en' => 'Cannot initialize module - Unexpected token ILLEGAL in info.json',
					'kr' => '모듈을 초기화 할 수 없습니다 - info.json 파일 파싱에 실패했습니다'
				));
			}
			
			if (isset($moduleInfo->allow_web_access) && $moduleInfo->allow_web_access == false && isset(Context::getInstance()->moduleID) && Context::getInstance()->moduleID == $moduleID){
				Context::printErrorPage(array(
					'en' => 'Cannot initialize module - web access is not allowed',
					'kr' => '모듈을 초기화 할 수 없습니다 - 웹 접근이 허용되지않음'
				));
				return NULL;
			}
			if (isset($moduleInfo->accessible_group) && (is_null(User::getCurrent()) || !User::getCurrent()->checkGroup($moduleInfo->accessible_group))) {
				Context::printErrorPage(array(
					'en' => 'Cannot initialize module - Operation not permitted',
					'kr' => '모듈을 초기화 할 수 없습니다 - 권한이 없습니다'
				));
				return NULL;
			}

			if (isset($moduleInfo->layout))
				Context::getInstance()->setLayout($moduleInfo->layout);



			$_loader = create_function('', 'return new ' . $classID . '(\''.$moduleID.'\');');
			$module = $_loader();
			$module->moduleInfo = $moduleInfo;

			self::$modules->{$moduleID.'.'.$moduleAction} = $module;
			self::$moduleInfos->{$moduleID.'.'.$moduleAction} = $moduleInfo;

			return $module;
		}
		
		static private function loadModuleAction($action, $module) {
			$moduleID = $module->moduleID;
			$moduleDir = self::getModuleDir($moduleID);
			
			// info.json 파일이 없으면 취소
			if (!is_file($moduleDir . '/info.json')) return;

			if ($moduleInfo = self::$moduleInfos->{$moduleID.'.'.$action}) {
				if (!$moduleInfo->actions && $moduleInfo->action) {
					Context::printErrorPage(array(
						'en' => 'Action property in "info.json" is not "action" : "actions" (caution "s")',
						'kr' => 'info.json 에서 정의하는 액션 속성은 action이 아닌 actions 속성입니다. ("s" 주의)'
					));
				}
				$actions = $moduleInfo->actions; //array
				
				for ($i=0; $i<count($actions); $i++) {
					// action이 지정되지 않고 default action이 info.json에서 정의됬을 시 해당 action 실행
					if (!isset($action) && isset($actions[$i]->default) && $actions[$i]->default === true) {
						$action = $actions[$i]->name;

						self::$modules->{$moduleID.'.'.$action} = self::$modules->{$moduleID.'.'};
						self::$moduleInfos->{$moduleID.'.'.$action} = self::$moduleInfos->{$moduleID.'.'};
						
						$module = self::$moduleInfos->{$moduleID.'.'.$action};
						$moduleInfo = self::$moduleInfos->{$moduleID.'.'.$action};
						
						unset(self::$modules->{$moduleID.'.'});
						unset(self::$moduleInfos->{$moduleID.'.'});
					}
					
					if ($action == $actions[$i]->name) {
						if (isset($actions[$i]->allow_web_access) && $actions[$i]->allow_web_access == false && Context::getInstance()->moduleID == $moduleID && Context::getInstance()->moduleAction == $action){
							Context::printErrorPage(array(
								'en' => 'Cannot execute module action - web access is not allowed',
								'kr' => '모듈 액션을 실행할 수 없습니다 - 웹 접근이 허용되지않음'
							));
							return NULL;
						}
						if (isset($actions[$i]->accessible_group) && (is_null(User::getCurrent()) || !User::getCurrent()->checkGroup($actions[$i]->accessible_group))) {
							Context::printErrorPage(array(
								'en' => 'Cannot execute module action - Operation not permitted',
								'kr' => '모듈 액션을 실행할 수 없습니다 - 권한이 없습니다'
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

				Context::printErrorPage(array(
					'en' => 'Cannot execute module action - permission denined by configuration file',
					'kr' => '모듈 액션을 실행할 수 없습니다 - Cofiguration 파일에 의해 권한이 거부됨'
				));
				return NULL;
			}
			return NULL;
		}
		
		
	}
	