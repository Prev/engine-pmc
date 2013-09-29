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
			// 이미 해당 모듈과 해당 액션을 사용 했을 때, 새로 모듈을 생성하지 않고 그대로 반환
			// 단, queryParam 이 설정되어 있을시에는 queryParam 을 적용함
			if (isset(self::$modules->{$moduleID.'.'.$moduleAction})) {
				if ($queryParam) {
					if (is_string($queryParam))
						$queryParam = urlQueryToArray($queryParam);
					foreach ($queryParam as $key => $value) {
						self::$modules->{$moduleID.'.'.$moduleAction}->{$key} = $value;
					}
				}
				return self::$modules->{$moduleID.'.'.$moduleAction};
			}

			$moduleDir = self::getModuleDir($moduleID);

			if (!$moduleID) {
				Context::printErrorPage(array(
					'en' => 'Cannot load module - module not defined',
					'ko' => '모듈을 불러올 수 없습니다 - 모듈이 정의되지 않음'
				));
				return;
				
			}else if (!self::moduleExists($moduleID)) {
				Context::printErrorPage(array(
					'en' => 'Cannot load module "'.$moduleID.'" - module not found',
					'ko' => '모듈 "'.$moduleID.'"을 불러올 수 없습니다 - 모듈을 찾을 수 없음'
				));
				return;
			}

			if (!is_file($moduleDir . '/info.json')) {
				Context::printErrorPage(array(
					'en' => 'Cannot initialize module "'.$moduleID.'" - info.json file not exists',
					'ko' => '모듈 "'.$moduleID.'"을 초기화 할 수 없습니다 - info.json 파일이 존재하지 않음'
				));
				return;
			}

			$moduleInfo = json_decode(file_get_contents($moduleDir . '/info.json'));


			// info.json에 의해 모듈 체크 및 설정
			self::verifyModuleInfo($moduleID, $moduleInfo);
			
			// info.json을 읽고 현재 실행되는 액션을 체크 및 설정
			$actionData = self::verifyModuleAction($moduleID, $moduleAction, $moduleInfo);

			// 모듈 불러오기
			$_module = self::loadModule($moduleID, $actionData);

			
			if ($actionData !== NULL) {
				$_module->action = $actionData->name;
				$_module->actionData = $actionData;
			}

			$_module->moduleInfo = $moduleInfo;

			if (!$moduleAction && $actionData->name)
				$moduleAction = $actionData->name;


			self::$modules->{$moduleID.'.'.$moduleAction} = $_module;
			self::$moduleInfos->{$moduleID.'.'.$moduleAction} = $moduleInfo;
			
			// 모듈 초기화
			$_module->__initBase();
			$_module->init();
			
			if ($queryParam) {
				if (is_string($queryParam))
					$queryParam = urlQueryToArray($queryParam);
				foreach ($queryParam as $key => $value) {
					$_module->{$key} = $value;
				}
			}
			
			// MVC 클래스 초기화
			if (method_exists($_module->model, 'init'))			$_module->model->init();
			if (method_exists($_module->controller, 'init'))	$_module->controller->init();
			if (method_exists($_module->view, 'init'))			$_module->view->init();

			return $_module;
		}

		
		static private function verifyModuleInfo($moduleID, $moduleInfo) {
			$moduleDir = self::getModuleDir($moduleID);
			
			if ($moduleInfo === NULL) {
				Context::printWarning(array(
					'en' => 'Cannot initialize module "'.$moduleID.'" - Unexpected token ILLEGAL in info.json',
					'ko' => '모듈 "'.$moduleID.'"을 초기화 할 수 없습니다 - info.json 파일 파싱에 실패했습니다'
				));
			}
			
			// 웹 접근 가능여부 체크
			// allow_web_access가 false 일 경우 모듈 안에서 모듈을 불러오는 것만 가능하게 함
			if (isset($moduleInfo->allow_web_access) && $moduleInfo->allow_web_access == false && isset(Context::getInstance()->moduleID) && Context::getInstance()->moduleID == $moduleID){
				Context::printErrorPage(array(
					'en' => 'Cannot initialize module "'.$moduleID.'" - web access is not allowed',
					'ko' => '모듈 "'.$moduleID.'"을 초기화 할 수 없습니다 - 웹 접근이 허용되지않음'
				));
				return NULL;
			}

			// accessible_group가 설정되어 있을 시 해당 그룹만 접근 가능
			if (isset($moduleInfo->accessible_group) && (is_null(User::getCurrent()) || !User::getCurrent()->checkGroup($moduleInfo->accessible_group))) {
				Context::printErrorPage(array(
					'en' => 'Cannot initialize module "'.$moduleID.'" - Operation not permitted',
					'ko' => '모듈 "'.$moduleID.'"을 초기화 할 수 없습니다 - 권한이 없습니다'
				));
				return NULL;
			}
			// doctype, header 태그를 출력하지 않고 모듈에서 출력한 내용만 나타나게 함
			if (isset($moduleInfo->print_alone))
				Context::getInstance()->printAlone = true;

			// 해당 액션에서 사용할 레이아웃을 설정
			// 모듈 안에서 불러온 모듈에서는 사용할 수 없음
			if (isset($moduleInfo->layout))
				Context::getInstance()->setLayout($moduleInfo->layout);
		}

		static private function verifyModuleAction($moduleID, $moduleAction, $moduleInfo) {
			$moduleDir = self::getModuleDir($moduleID);
			
			if (!$moduleInfo->actions && $moduleInfo->action) {
				Context::printErrorPage(array(
					'en' => 'Action property in "info.json" is not "action" : "actions" (caution "s") in module "'.$moduleID.'"',
					'ko' => '모듈 "'.$moduleID.'" 에서 info.json 에서 정의하는 액션 속성은 action이 아닌 actions 속성입니다. ("s" 주의)'
				));
			}
			$actions = $moduleInfo->actions; //array
			
			for ($i=0; $i<count($actions); $i++) {
				// action이 지정되지 않고 default action이 info.json에서 정의됬을 시 해당 action 실행
				if (!$moduleAction && isset($actions[$i]->default) && $actions[$i]->default === true)
					$moduleAction = $actions[$i]->name;
				
				// 실행되는 모듈 액션을 찾았을 경우
				if ($moduleAction == $actions[$i]->name) {
					
					// 모듈 액션 상속 기능
					if (isset($actions[$i]->inherit)) {
						$isInherited = false; // if문이 종료될때까지 false 인 경우 오류


						$inherit = explode('.', $actions[$i]->inherit);
						if (count($inherit) != 2) {
							Context::printErrorPage(array(
								'en' => 'Cannot execute module action "'.($moduleID.'.'.$moduleAction).'" - inherit property is not invalid',
								'ko' => '모듈 액션 "'.($moduleID.'.'.$moduleAction).'"을 실행할 수 없습니다 - inherit 속성이 잘못됨'
							));
							return NULL;
						}

						$parentDir = self::getModuleDir($inherit[0]);
						if (!is_file($parentDir . '/info.json') || !($parentData = json_decode(file_get_contents($parentDir . '/info.json')))) {
							Context::printErrorPage(array(
								'en' => 'Cannot execute module action "'.($moduleID.'.'.$moduleAction).'" - fail to extends module "'.$inherit[0].'"',
								'ko' => '모듈 액션 "'.($moduleID.'.'.$moduleAction).'"을 실행할 수 없습니다 - 모듈 "'.$inherit[0].'"을 상속하는데 실패했습니다'
							));
							return NULL;
						}

						// action 외 데이터 상속
						foreach ($parentData as $key => $value) {
							if ($key == 'actions' || $key == 'name' || $key == 'version' || $key == 'author') continue;
							if (!empty($moduleInfo->{$key})) continue;

							$moduleInfo->{$key} = $value;
						}
						self::verifyModuleInfo($moduleID, $moduleInfo);
						for ($j=0; $j<count($parentData->actions); $j++) {
							if ($parentData->actions[$j]->name == $inherit[1]) {
								$isInherited = true;
								
								$actions[$i]->inheritData = $parentData->actions[$j];
								$actions[$i]->inheritData->module = $inherit[0];
								$actions[$i]->inheritData->action = $inherit[1];
								unset($actions[$i]->inheritData->name);

								foreach ($parentData->actions[$j] as $key => $value) {
									if ($key == 'inherit' || $key == 'name') continue;
									if (!empty($actions[$i]->{$key})) continue;

									$actions[$i]->{$key} = $value;
								}
							}
						}

						if ($isInherited == false) {
							Context::printErrorPage(array(
								'en' => 'Cannot execute module action "'.($moduleID.'.'.$moduleAction).'" - fail to extends module "'.$inherit[0].'"',
								'ko' => '모듈 액션 "'.($moduleID.'.'.$moduleAction).'"을 실행할 수 없습니다 - 모듈 "'.$inherit[0].'"을 상속하는데 실패했습니다'
							));
							return NULL;
						}
						
					}

					// 웹 접근 가능여부 체크
					// allow_web_access가 false 일 경우 모듈 안에서 모듈을 불러오는 것만 가능하게 함
					if (isset($actions[$i]->allow_web_access) && $actions[$i]->allow_web_access == false && Context::getInstance()->moduleID == $moduleID && Context::getInstance()->moduleAction == $moduleAction){
						Context::printErrorPage(array(
							'en' => 'Cannot execute module action "'.($moduleID.'.'.$moduleAction).'" - web access is not allowed',
							'ko' => '모듈 액션 "'.($moduleID.'.'.$moduleAction).'"을 실행할 수 없습니다 - 웹 접근이 허용되지않음'
						));
						return NULL;
					}

					// accessible_group가 설정되어 있을 시 해당 그룹만 접근 가능
					if (isset($actions[$i]->accessible_group) && (is_null(User::getCurrent()) || !User::getCurrent()->checkGroup($actions[$i]->accessible_group))) {
						Context::printErrorPage(array(
							'en' => 'Cannot execute module action "'.($moduleID.'.'.$moduleAction).'" - Operation not permitted',
							'ko' => '모듈 액션 "'.($moduleID.'.'.$moduleAction).'"을 실행할 수 없습니다 - 권한이 없습니다'
						));
						return NULL;
					}

					// doctype, header 태그를 출력하지 않고 모듈에서 출력한 내용만 나타나게 함
					if (isset($actions[$i]->print_alone))
						Context::getInstance()->printAlone = true;
					
					// 해당 액션에서 사용할 레이아웃을 설정
					// 모듈 안에서 불러온 모듈에서는 사용할 수 없음
					if (isset($actions[$i]->layout))
						Context::getInstance()->setLayout($actions[$i]->layout);

					return $actions[$i];
				}
			}
			if ($moduleAction === NULL) {
				Context::printErrorPage(array(
					'en' => 'Module action is not defined',
					'ko' => '모듈 액션이 정의되지 않았습니다.'
				));
			}

			Context::printErrorPage(array(
				'en' => 'Cannot execute module action "'.($moduleID.'.'.$moduleAction).'" - not defined in configuration file',
				'ko' => '모듈 액션 "'.($moduleID.'.'.$moduleAction).'" 을 실행할 수 없습니다 - Cofiguration 파일에서 정의되지 않음'
			));
			return NULL;
			
		}
		
		static private function loadModule($moduleID, $actionData) {
			$moduleDir = self::getModuleDir($moduleID);

			// <ModuleName>Module.class.php 파일이 존재하면 불러옴
			// 그렇지 않으면 기본 Module 클래스를 불러옴
			if (self::customModuleExists($moduleID)) {
				if (!class_exists(ucfirst($moduleID) . 'Module'))
					require $moduleDir . '/' . ucfirst($moduleID) . 'Module.class.php';
				$classID = ucfirst(strtolower($moduleID)) . 'Module';

			}else if (isset($actionData->inheritData) && self::customModuleExists($actionData->inheritData->module)) {
				$parentModuleID = $actionData->inheritData->module;
				if (!class_exists(ucfirst($parentModuleID) . 'Module'))
					require self::getModuleDir($parentModuleID) . '/' . ucfirst($parentModuleID) . 'Module.class.php';

				$classID = ucfirst(strtolower($parentModuleID)) . 'Module';
			}else
				$classID = 'Module';


			if (!class_exists($classID)) {
				Context::printErrorPage(array(
					'en' => 'Cannot initialize module "'.$moduleID.'" - Cannot find module class "'.$classID.'"',
					'ko' => '모듈 "'.$moduleID.'"을 초기화 할 수 없습니다 - 클래스 "'.$classID.'"를 찾을 수 없습니다.'
				));
			}

			// 모듈 클래스 생성
			$_loader = create_function('', 'return new ' . $classID . '(\''.$moduleID.'\');');
			$module = $_loader();

			if ($module === NULL) {
				Context::printErrorPage(array(
					'en' => 'Cannot load module "'.$moduleID.'" fail to create module instance',
					'ko' => '모듈" '.$moduleID.'"을 불러올 수 없습니다 - 모듈 인스턴스 생성에 실패했습니다'
				));
				return;
			}

			return $module;
		}
	}
	
