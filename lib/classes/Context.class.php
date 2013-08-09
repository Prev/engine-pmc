<?php

	/**
	 * @author prevdev@gmail.com
	 * @2013.05 ~ 07
	 *
	 *
	 * Context Class
	 * Control all about content and context
	 */

	class Context {
		
		/**
		 * Attributes Container
		 * Set attr by Context::set method
		 * Get attr by Context::get method
		 */
		static $attr;
		
		/**
		 * module ID
		 */
		public $moduleID;
		
		/**
		 * module Action
		 */
		public $moduleAction;
		

		/**
		 * selected menu
		 * first set by $_GET['menu']
		 * in board or page module, can modify it
		 */
		public $selectedMenu;


		/**
		 * only print module content 
		 */
		public $printAlone;


		/**
		 * mobile mode
		 */
		public $mobileMode;
		public $isMobile;


		/**
		 * headerTagHandler obj
		 * HeaderTagHandler Class
		 */
		private $headerTagHandler;
		
		
		/**
		 * layout name
		 * default value is defined in config/config.php
		 */
		private $layout;
		
		/**
		 * set content printable
		 * if this var is false, can not excute printContent() method
		 */
		private $contentPrintable = true;
		

		/**
		 * menu datas
		 */
		static private $menuDatas;


		/**
		 * Get Context instance
		 */
		public static function getInstance() {
			if(!isset($GLOBALS['__Context__'])) {
				$GLOBALS['__Context__'] = new Context();
			}
			
			return $GLOBALS['__Context__'];
		}
		
		/**
		 * Initalize Context instance
		 */
		public function init($db_info) {
			self::$attr = new StdClass();
			self::$menuDatas = new StdClass();
			
			$this->headerTagHandler = new HeaderTagHandler();
			$this->setLayout(LAYOUT_NAME);
			$this->printAlone = false;

			$mobileAgents  = array('iphone','lgtelecom','skt','mobile','samsung','nokia','blackberry','android','android','sony','phone');
				
			for ($i=0; $i<count($mobileAgents); $i++){ 
				if (preg_match("/{$mobileAgents[$i]}/", strtolower($_SERVER['HTTP_USER_AGENT']))) {
					$this->mobileMode = true;
					$this->isMobile = true;
					break;
				} 
			}
			
			if ($_COOKIE['mobile']) $this->mobileMode = true;
			if (!$_COOKIE['mobile']) $this->mobileMode = false;

			if (isset($_GET['mobile'])) {
				if ($_GET['mobile']) {
					$this->mobileMode = true;
					setcookie('mobile', 1);
				}else {
					$this->mobileMode = false;
					setcookie('mobile', 0);
				}
			}

			if (isset($_GET['locale'])) setcookie('locale', $_GET['locale']);
			if (isset($_GET['page']) && !isset($_GET['module'])) $_GET['module'] = 'page';
			if (!isset($GLOBALS['serverInfo'])) {
				Context::printErrorPage(array(
					'en' => 'Cannot find connected server with the server defined in config/server_info.json',
					'kr' => 'config/server_info.json 파일에서 현재 서버와 연결된 서버를 찾을 수 없습니다'
				));
				return;
			}

			if (!class_exists('PDO')) {
				Context::printErrorPage(array(
					'en' => 'php extension "PDO" does not exists',
					'kr' => 'php 확장 모듈 "PDO"가 존재하지 않습니다'
				));
				return;
			}

			CacheHandler::init();
			ModuleHandler::init();
			DBHandler::init($db_info);
			
			$this->initMenu($_REQUEST);
			

			$this->addMetaTag( array('charset'=>TEXT_ENCODING) );

			$this->addHeaderFile('/static/css/global.css');
			$this->addHeaderFile('/static/js/global.js');
			
			$this->addHeaderTag(
				'<script type="text/javascript">' .
					'var RELATIVE_URL = "'.RELATIVE_URL . '";' .
					'var USE_SHORT_URL = "'.USE_SHORT_URL . '";' .
					'var REAL_URL = "'.REAL_URL . '";' .
				'</script>'
			);

			if (DEBUG_MODE)
				$this->addHeaderFile('/static/js/vdump.js');
			
			if (X_UA_Compatible) {
				$this->addMetaTag(
					array('http-equiv'=>'X-UA-Compatible', 'content'=>X_UA_Compatible)
				);
			}
			
			$this->setTitle('engine pmc');
		}
		
		/**
		 * initalize menu and set moduleID, moduleAction
		 * If menu has own module or action, set it's module, action
		 * If module is not defined by getVar or menu, set default value 'index'
		 */
		
		private function initMenu($getVars) {
			$moduleID = isset($getVars['module']) ? basename($getVars['module']) : NULL;
			$moduleAction = isset($getVars['action']) ? basename($getVars['action']) : NULL;

			// get default(index) menu when module and menu not defined
			if (!isset($getVars['menu']) && !$moduleID) {
				$data = DBHandler::for_table('menu')
					->where('is_index', 1)
					->find_one();

				if (isset($data)) $getVars['menu'] = $data->title;
			}

			if (!isset($getVars['menu'])) $getVars['menu'] = NULL;
			
			$data = DBHandler::for_table('menu')
				->where('title', $getVars['menu'])
				->find_one();

			// module and menu are not defined, print error
			if (!isset($data) && !isset($moduleID)) {
				self::printErrorPage(array(
					'en' => 'Cannot find requested menu',
					'kr' => '해당 메뉴를 찾을 수 없습니다'
				));
			}else {
				$this->selectedMenu = $getVars['menu'];				
				if (isset($data) && isset($data->module)) {
					if ($moduleID) {
						Context::printErrorPage(array(
							'en' => 'Cannot excute module "'.$moduleID.'" in menu "'.$getVars['menu'].'"',
							'kr' => '해당 메뉴 "'.$getVars['menu'].'" 에서 연결된 모듈 '.$moduleID.'"" 을 실행 할 수 없습니다'
						));
					}else
						$moduleID = $data->module;
				}
				if ($data && $data->extra_vars) {
					$extraVars = json_decode($data->extra_vars);
					if ($extraVars && $extraVars->linkToSubMenu == true) {
						$subMenu = self::getMenu(2);
						if ($subMenu && count($subMenu) > 0)
							redirect(getUrl() . (USE_SHORT_URL ? '/' : '/?menu=') . $subMenu[0]->title);
					}
				}
				if ($data && $data->module && $data->action && !$moduleAction)
					$moduleAction = $data->action;
			}
			
			$this->moduleID = $moduleID;
			$this->moduleAction = $moduleAction;
		}
		
		
		/**
		 * Get menu data
		 * Add cached CSS
		 */
		static public function getMenu($level) {
			$menuHash = $level . ':' . self::getInstance()->selectedMenu;
			if (isset(self::$menuDatas->{$menuHash})) return self::$menuDatas->{$menuHash};

			// current selected menu
			$selectedMenuData = DBHandler::for_table('menu')
				->where('title', self::getInstance()->selectedMenu)
				->find_one();
				
			if ($level == 1) {
				$arr = DBHandler::for_table('menu')
					->where('level', 1)
					->where('visible', 1)
					->find_many();
			}else {
				if (empty($selectedMenuData)) {
					self::printErrorPage(array('en' => 'fail parsing menu', 'kr' => '메뉴 파싱에 실패했습니다.'));
					return;
				}
				// current selected menu's level is equal with requested menu's level
				if ($level == $selectedMenuData->level)
					$parent_id = $selectedMenuData->parent_id;
				
				// request low level menu data than selected menu's level
				else if ($level > $selectedMenuData->level)
					$parent_id = $selectedMenuData->id;

				// request high level menu data than selected menu's level
				else {
					$parentMenuData = DBHandler::for_table('menu')
						->where('level', $level)
						->where('id', $selectedMenuData->parent_id)
						->find_one();

					$parent_id = $parentMenuData->parent_id;
				}
				$arr = DBHandler::for_table('menu')
						->where('level', $level)
						->where('visible', 1)
						->where('parent_id', $parent_id)
						->find_many();
			}

			static $topMenus;
			if ($selectedMenuData !== false) {
				if ($selectedMenuData->level == 1) $topMenus = array($selectedMenuData->id);
				else if (!$topMenus) $topMenus = explode(',', self::getParentMenus($selectedMenuData->id));
			}

			for ($i=0; $i<count($arr); $i++) {
				$arr[$i] = $arr[$i]->getData();
				$arr[$i]->className = 'menu-' . $arr[$i]->title;
				
				if (isset($topMenus) && array_search($arr[$i]->id, $topMenus) !== false)
					$arr[$i]->selected = true;
				
				if ($arr[$i]->is_index && USE_SHORT_URL)
					$arr[$i]->title = '';
				
				$arr[$i]->title_locale = fetchLocale($arr[$i]->title_locales);
			}
			
			self::$menuDatas->{$menuHash} = $arr;
			return $arr;
		}

		static private function getParentMenus($menuId) {
			$row = DBHandler::for_table('menu')
				->select_many('id', 'parent_id')
				->where('id', $menuId)
				->find_one();
			
			if (empty($row))
				return NULL;
			else if ($row->parent_id === NULL)
				return $row->id;
			else
				return $row->id . ',' . self::getParentMenus($row->parent_id);
		}

		/**
		 * set context var
		 * layouts/template file can access vars defined with this funcs
		 */
		static public function set($key, $value) {
			if ($value === NULL)
				unset(self::$attr->{$key});
			else
				self::$attr->{$key} = $value;
		}
		
		/**
		 * get context var
		 */
		static public function get($key) {
			if (!isset(self::$attr->{$key}))
				return NULL;
			else
				return self::$attr->{$key};
		}
		
		/*
		 * check existence of layout file and set layout
		 */
		public function setLayout($name) {
			if (!is_file(ROOT_DIR . '/layouts/' . $name . '/layout.html')) {
				Context::printErrorPage(array(
					'en' => 'layout "'.$name.'" does not exist',
					'kr' => '레이아웃 파일 "'.$name.'" 이 존재하지 않습니다'
				));
				return;
			}
			
			$this->layout = $name;
		}
		
		
		/**
		 * set browser title, <title> tag
		 */
		public function setTitle($title) {
			$this->headerTagHandler->setBrowserTitle($title);
		}
		
		
		/**
		 * add header files like css/js/favicon
		 * @param $path : path of file
		 * @param $index : if $index is -1, push file in last of array, else push file in current index
		 * @param $position : position of header files / generally added in head, body-top or body-bottom
		 * @param $requiredAgent : if $requiredAgent !== NULL, check user agent and matching of them.
		 *							if $requiredAgent doesn't matche with userAgent, header file is not added
		 * @param $targetie : target of added ie, if @param is not NULL, header tag is reaplaced to <!--[if @param]>HEADER_TAG<![endif]-->
		 */
		public function addHeaderFile($path, $index=-1, $position='head', $requiredAgent=NULL, $targetie=NULL) {
			if (substr($path, 0, 1) != '/')
				$path = '/' . $path;

			if (!is_file(ROOT_DIR . '/' . $path)) {
				self::printWarning(array(
					'en' => 'fail to load file "<b>'.$path.'"</b>',
					'kr' => '파일을 불러오는데 실패했습니다 - "<b>'.$path.'"</b>'
				));
				return;
			}
			
			switch ($extension = substr(strrchr($path, '.'), 1)) {
				case 'css' :
					$this->headerTagHandler->addCSSFile($path, $index, $position, $requiredAgent, $targetie);
					break;
					
				case 'js' :
					$this->headerTagHandler->addJsFile($path, $index, $position, $requiredAgent, $targetie);
					break;
					
				case 'lessc' :	
				case 'less' :
					$this->headerTagHandler->addLesscFile($path, $index, $position, $requiredAgent, $targetie);
					break;
					
				case 'ico' :
					$this->headerTagHandler->setFavicon($path);
					break;

					
				default :
					self::printWarning(array(
						'en' => 'Unknown type of file - <b>"'.$path.'"</b>',
						'kr' => '알수없는 파일을 불러오려했습니다 - <b>"'.$path.'"</b>'
					));
					break;
			}
		}
		
		/**
		 * add meta tag
		 */
		public function addMetaTag($stringOrObj, $index=-1) {
			$this->headerTagHandler->addMetaTag($stringOrObj, $index);
		}
		
		/**
		 * add other header tags
		 */
		public function addHeaderTag($string, $index=-1) {
			$this->headerTagHandler->addHeaderTag($string, $index);
		}
		
		/**
		 * get doctype by defined const 'DOCTYPE' in config/config.php
		 */
		public function getDoctype() {
			switch (DOCTYPE) {
				case 'html5' :
					return '<!doctype html>';
					break;

				case 'xhtml-t' :
					return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
					break;
				
				case 'xhtml-s' :
					return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
					break;
				
				case 'xhtml' :
					return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
					break;
				
				case 'html4-t' :
					return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
					break;
				
				case 'html4' :
					return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
					break;
				
				case NULL :
				case 'none' :
					return '';
					break;
					
				default :
					return DOCTYPE;
					break;
				
			}
		}
		
		/**
		 * Get header files tags in head
		 */
		public function getHead() {
			return $this->headerTagHandler->getTags('head');
		}
		
		/**
		 * Get js and css file tags in body-top (no meta,script,etc tags)
		 */
		public function getBodyTop() {
			return $this->headerTagHandler->getTags('body-top');
		}
		
		/**
		 * Get js and css file tags in body-bottom (no meta,script,etc tags)
		 */
		public function getBodyBottom() {
			return $this->headerTagHandler->getTags('body-bottom');
		}
		
		/**
		 * Print error page
		 */
		static public function printErrorPage($content) {
			ob_clean();
			
			$content = fetchLocale($content);
			Context::set('errorMessage', $content);
			
			self::getInstance()->setLayout('error');
			self::getInstance()->procLayout();

			exit;
		}
		
		/**
		 * Print warning line
		 */
		static public function printWarning($message) {
			$message = fetchLocale($message);
			
			$backtrace = debug_backtrace();
			$backtrace_path = getFilePathClear($backtrace[0]['file']);
			$backtrace_message = '&nbsp;&nbsp;- in "' . $backtrace_path . '" on line ' . $backtrace[0]['line'];
			
			
			if (DEBUG_MODE) {
				echo '<div class="warning">' .
						'<span class="warning-c">' .
							'<b>Warning:</b> ' . $message . $backtrace_message .
						'</span>' .
					 '</div>';
			}
			ErrorLogger::log('Warning : ' . $message, $backtrace);
		}
		
		/**
		 * check sso and initialize
		 */
		public function checkSSO() {
			if (isset($_COOKIE['pmc_sess_key']) && !isset($_SESSION['pmc_sso_data'])) {
				$urlData = getUrlData(SSO_URL . '?sess_key=' . $_COOKIE['pmc_sess_key'], SSO_AGENT_KEY);
				
				if (!$urlData) {
					Context::printErrorPage(array(
						'en' => 'cannot load sso data',
						'kr' => 'SSO 데이터를 불러올 수 없습니다'
					));
					unset($_SESSION['pmc_sso_data']);
					return false;
				}

				$ssoData = json_decode($urlData);
				if (!$ssoData || $ssoData->result === 'fail') {
					Context::printErrorPage(array(
						'en' => 'fail loading sso data',
						'kr' => 'SSO 데이터를 불러오는데 실패하였습니다.'
					));
					unset($_SESSION['pmc_sso_data']);
					return false;
				}
				$userData = $ssoData->userData;
				$_SESSION['pmc_sso_data'] = $ssoData;

				User::initCurrent();
				return true;
			}
			else {
				User::initCurrent();
				return true;
			} 
		}

		/**
		 * print content
		 * exec layout cache and merge with doctype, header tags etc...
		 * if encoding is not ut-8, convert encoding to defined encoding
		 */
		public function procLayout() {
			if (!$this->contentPrintable) return; // if error printed, return

			ob_start();
			
			if ($this->mobileMode && is_file(ROOT_DIR . '/layouts/m.' . $this->layout . '/layout.html'))
				$this->layout = 'm.' . $this->layout;

			CacheHandler::execTemplate('/layouts/' . $this->layout . '/layout.html');
			

			if ($this->printAlone) {
				ob_end_flush();
			}else {
				$content = ob_get_clean();
				OB_GZIP ? ob_start('ob_gzhandler') : ob_start();
			
				$output = $this->getDoctype() . LINE_END .
						  '<html>' . LINE_END .
						  '<head>' . LINE_END .
						  $this->getHead() . 
						  '</head>' . LINE_END .
						  '<body>' . LINE_END .
						  $this->getBodyTop() . LINE_END.
				 		  $content . LINE_END .
						  $this->getBodyBottom() . LINE_END .
						  '</body>' . LINE_END .
						  '</html>';

				if (TEXT_ENCODING != 'utf-8')
					$output = iconv('utf-8', TEXT_ENCODING, $output);
				
				echo $output;
				ob_end_flush();
			}
		}

		/*
		 * get module content
		 */
		public function getModuleContent($moduleID=NULL, $moduleAction=NULL, $queryParam=NULL) {
			if (!$moduleAction && !$moduleID)	$moduleAction = $this->moduleAction;
			if (!$moduleID) 					$moduleID = $this->moduleID;

			$module = ModuleHandler::initModule(
				$moduleID,
				$moduleAction,
				$queryParam
			);
			if (!$module)
				$this->printWarning(array(
					'en'=>'Cannot load module content',
					'kr'=>'모듈 콘텐츠를 불러올 수 없습니다'
				));
			else {
				$module->exec();
			}
		}
	}
