<?php

	/**
	 * @ author prevdev@gmail.com
	 * @ 2013.05 ~ 07
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
		 * headerTagHandler obj
		 * HeaderTagHandler Class
		 */
		var $headerTagHandler;
		
		
		/**
		 * layout name
		 * default value is defined in config/config.php
		 */
		var $layout;
		
		
		/**
		 * menu data
		 */
		var $selectedMenu;
		
		
		/**
		 * set content printable
		 * if this var is false, can not excute printContent() method
		 */
		private $contentPrintable = true;
		
		/**
		 * Get Context instance
		 */
		public function getInstance() {
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
			$this->headerTagHandler = new HeaderTagHandler();
			$this->setLayout(LAYOUT_NAME);
			
			if (isset($_GET['locale'])) setcookie('locale', $_GET['locale']);
			if (!isset($GLOBALS['serverInfo'])) {
				Context::printErrorPage(array(
					'en' => 'Cannot find connected server with the server defined in conf/server_info.json',
					'kr' => 'conf/server_info.json 파일에서 현재 서버와 연결된 서버를 찾을 수 없습니다'
				));
				return;
			}

			CacheHandler::init();
			DBHandler::init($db_info);
			$this->initMenu($_GET);
			
			$this->addHeaderFile('/static/css/global.css');
			$this->addHeaderFile('/static/js/lie.js');
			$this->addMetaTag( array('charset'=>TEXT_ENCODING) );
			
			if (DEBUG_MODE)
				$this->addHeaderFile('/static/js/vdump.js', -1, 'body-bottom');
			
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
			$moduleID = isset($getVars['module']) ? $getVars['module'] : NULL;
			$moduleAction = isset($getVars['action']) ? basename($getVars['action']) : NULL;
			
			if (!isset($getVars['menu']) && !$moduleID) {
				$data = DBHandler::execQueryOne("SELECT * FROM (#)menu WHERE is_index='1' LIMIT 1");
				if ($data) $getVars['menu'] = $data->title;
			}
			$data = DBHandler::execQueryOne("SELECT * FROM (#)menu WHERE title='" . escape($getVars['menu']) . "' LIMIT 1");
			if (!$data && !$moduleID) {
				self::printErrorPage(array(
					'en' => 'Cannot find requested menu',
					'kr' => '해당 메뉴를 찾을 수 없습니다'
				));
			}else {
				$this->selectedMenu = $getVars['menu'];				
				if ($data->module) {
					if ($moduleID) {
						Context::printErrorPage(array(
							'en' => 'Cannot excute module "'.$moduleID.'" in menu "'.$getVars['menu'].'"',
							'kr' => '해당 메뉴 "'.$getVars['menu'].'" 에서 연결된 모듈 '.$moduleID.'"" 을 실행 할 수 없습니다'
						));
					}else
					$moduleID = $data->module;
				}
				if ($data->module && $data->action && !$moduleAction)
					$moduleAction = $data->action;
			}
			
			$this->moduleID = $moduleID;
			$this->moduleAction = $moduleAction;
		}
		
		
		/**
		 * Get menu data
		 * Add cached CSS
		 */
		static function getMenu($level, $printBlankInIndex=false) {
			$arr = DBHandler::execQuery("SELECT * FROM (#)menu WHERE level='" . escape($level) . "'");
			for ($i=0; $i<count($arr); $i++) {
				$arr[$i]->className = 'pmc-menu' . $level . '-' . $arr[$i]->title;
				if ($arr[$i]->title == self::getInstance()->selectedMenu) {
					$arr[$i]->selected = true;
					$arr[$i]->className .= ' pmc-menu' . $level . '-selected';
				}
				if ($arr[$i]->is_index && $printBlankInIndex)
					$arr[$i]->title = '';
				
				$arr[$i]->title_locales = json_decode($arr[$i]->title_locales);
				$arr[$i]->title_locale = fetchLocale($arr[$i]->title_locales);
			}
			$menuCSSPath = CacheHandler::getMenuCachePath($arr, $level);
			self::getInstance()->addHeaderFile($menuCSSPath);
			
			return $arr;
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
		 * if index is -1, push file in last of array
		 * else, push file in current index
		 */
		public function addHeaderFile($path, $index=-1, $position='head', $targetie=NULL) {
			if (substr($path, 0, 1) != '/')
				$path = '/' . $path;
			
			if (!is_file(ROOT_DIR . '/' . $path)) {
				self::printWarning(array(
					'en' => 'fail to load file "<b>/'.$path.'"</b>',
					'kr' => '파일을 불러오는데 실패했습니다 - "<b>/'.$path.'"</b>'
				));
				return;
			}
			
			switch ($extension = substr(strrchr($path, '.'), 1)) {
				case 'css' :
					$this->headerTagHandler->addCSSFile($path, $index, $position, $targetie);
					break;
					
				case 'js' :
					$this->headerTagHandler->addJsFile($path, $index, $position, $targetie);
					break;
					
				case 'lessc' :	
				case 'less' :
					$this->headerTagHandler->addLesscFile($path, $index, $position, $targetie);
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
		 * print content
		 * exec layout cache and merge with doctype, header tags etc...
		 * if encoding is not ut-8, convert encoding to defined encoding
		 */
		public function procLayout() {
			if (!$this->contentPrintable) return; // if error printed, return

			ob_start();
			CacheHandler::execLayout('/layouts/' . $this->layout . '/layout.html');
			
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

		/*
		 * get module content
		 */
		public function getModuleContent($moduleID=NULL, $moduleAction=NULL) {
			if (!$moduleAction && !$moduleID)	$moduleAction = $this->moduleAction;
			if (!$moduleID) 					$moduleID = $this->moduleID;

			$module = ModuleHandler::initModule(
				$moduleID,
				$moduleAction
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
	