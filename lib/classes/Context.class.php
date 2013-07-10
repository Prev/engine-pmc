<?php

	/**
	 * @ author prevdev@gmail.com
	 * @ 2013.05 ~ 6
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
		 * headerTagHandler obj
		 * HeaderTagHandler Class
		 */
		static $headerTagHandler;
		
		
		/**
		 * layout name
		 * default value is defined in config/config.php
		 */
		static $layout;
		
		
		/**
		 * menu data
		 */
		static $selectedMenu;
		
		
		/**
		 * module ID
		 */
		static $moduleID;
		
		/**
		 * module Action
		 */
		static $moduleAction;
		
		
		/**
		 * set content printable
		 * if this var is false, can not excute printContent() method
		 */
		static private $contentPrintable;
		
		
		
		
		/**
		 * Get Context instance
		 */
		public function &getInstance() {
			if(!$GLOBALS['__Context__']) {
				$GLOBALS['__Context__'] = new Context();
			}
			
			return $GLOBALS['__Context__'];
		}
		
		/**
		 * Initalize Context instance
		 */
		public function init($db_info) {
			self::$attr = new StdClass();
			self::$headerTagHandler = new HeaderTagHandler();
			self::$contentPrintable = true;
			self::setLayout(LAYOUT_NAME);
			
			if ($_GET['locale']) setcookie('locale', $_GET['locale']);
			
			CacheHandler::init();
			DBHandler::init($db_info);
			self::initMenu($_GET);
			
			
			self::addHeaderFile('/static/css/global.css');
			self::addHeaderFile('/static/js/lie.js');
			self::addMetaTag( array('charset'=>TEXT_ENCODING) );
			
			if (X_UA_Compatible) {
				self::addMetaTag(
					array('http-equiv'=>'X-UA-Compatible', 'content'=>X_UA_Compatible)
				);
			}
			
			Context::setTitle('engine pmc');
		}
		
		/**
		 * initalize menu and set moduleID, moduleAction
		 * If menu has own module or action, set it's module, action
		 * If module is not defined by getVar or menu, set default value 'index'
		 */
		
		static function initMenu($getVars) {
			$moduleID = isset($getVars['module']) ? $getVars['module'] : NULL;
			$moduleAction = isset($getVars['action']) ? basename($getVars['action']) : NULL;
			
			if (!isset($getVars['menu']) && !$moduleID) {
				$data = DBHandler::execQueryOne("SELECT * FROM (#)menu WHERE is_index='1' LIMIT 1");
				if ($data) $getVars['menu'] = $data->title;
			}
			
			$data = DBHandler::execQueryOne("SELECT * FROM (#)menu WHERE title='".$getVars['menu']."' LIMIT 1");
			if (!$data && !$moduleID) {
				self::printErrorPage(array(
					'en' => 'Cannot find requested menu',
					'kr' => '해당 메뉴를 찾을 수 없습니다'
				));
			}else {
				self::$selectedMenu = $getVars['menu'];
				
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
			
			self::$moduleID = $moduleID;
			self::$moduleAction = $moduleAction;
		}
		
		
		/**
		 * Get menu data
		 * Add cached CSS
		 */
		public function getMenu($level, $printBlankInIndex=false) {
			$arr = DBHandler::execQuery("SELECT * FROM (#)menu WHERE level='${level}'");
			for ($i=0; $i<count($arr); $i++) {
				$arr[$i]->className = 'pmc-menu' . $level . '-' . $arr[$i]->title;
				if ($arr[$i]->title == self::$selectedMenu) {
					$arr[$i]->selected = true;
					$arr[$i]->className .= ' pmc-menu' . $level . '-selected';
				}
				if ($arr[$i]->is_index && $printBlankInIndex)
					$arr[$i]->title = '';
				
				$arr[$i]->title_locales = json_decode($arr[$i]->title_locales);
				$arr[$i]->title_locale = fetchLocale($arr[$i]->title_locales);
			}
			$menuCSSPath = CacheHandler::getMenuCachePath($arr, $level);
			self::addHeaderFile($menuCSSPath);
			
			return $arr;
		}
		
		
		/**
		 * set browser title, <title> tag
		 */
		public function setTitle($title) {
			self::$headerTagHandler->setBrowserTitle($title);
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
		static public function setLayout($name) {
			if (!is_file(ROOT_DIR . '/layouts/' . $name . '/layout.html')) {
				Context::printErrorPage(array(
					'en' => 'layout "'.$name.'" does not exist',
					'kr' => '레이아웃 파일 "'.$name.'" 이 존재하지 않습니다'
				));
				return;
			}
			
			self::$layout = $name;
		}
		
		
		/**
		 * add header files like css/js/favicon
		 * if index is -1, push file in last of array
		 * else, push file in current index
		 */
		static public function addHeaderFile($path, $index=-1, $position='head', $targetie=NULL) {
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
					self::$headerTagHandler->addCSSFile($path, $index, $position, $targetie);
					break;
					
				case 'js' :
					self::$headerTagHandler->addJsFile($path, $index, $position, $targetie);
					break;
					
				case 'lessc' :	
				case 'less' :
					self::$headerTagHandler->addLesscFile($path, $index, $position, $targetie);
					break;
					
				case 'ico' :
					self::$headerTagHandler->setFavicon($path);
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
		static public function addMetaTag($stringOrObj, $index=-1) {
			self::$headerTagHandler->addMetaTag($stringOrObj, $index);
		}
		
		/**
		 * add other header tags
		 */
		static public function addHeaderTag($string, $index=-1) {
			self::$headerTagHandler->addHeaderTag($string, $index);
		}
		
		/**
		 * get doctype by defined const 'DOCTYPE' in config/config.php
		 */
		static public function getDoctype() {
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
		static public function getHead() {
			return self::$headerTagHandler->getTags('head');
		}
		
		/**
		 * Get js and css file tags in body-top (no meta,script,etc tags)
		 */
		static public function getBodyTop() {
			return self::$headerTagHandler->getTags('body-top');
		}
		
		/**
		 * Get js and css file tags in body-bottom (no meta,script,etc tags)
		 */
		static public function getBodyBottom() {
			return self::$headerTagHandler->getTags('body-bottom');
		}
		
		/**
		 * Excute function in layout/template file
		 */
		static public function execFunction($function, $args) {
			if ($args && count($args) > 1)
				$args = '\'' . join('\',\'', $args) . '\'';
			else if ($args && count($args) == 1)
				$args = '\'' . $args[0] . '\'';
			else
				$args = '';
			
			if (function_exists($function)) {
				$func = create_function('', "return $function($args);");
				$r = $func();
				
				if ($r !== NULL) echo $r;
				return;
			}
			if (!$GLOBALS['__Module__']) return;
			$m = $GLOBALS['__Module__'];
			
			if (method_exists($m, $function)) {
				$func = create_function('', 'return $GLOBALS[\'__Module__\']->' . $function . "($args);");
				$r = $func();
			}else if ($m->getModel() && method_exists($m->getModel(), $function)) {
				$func = create_function('', 'return $GLOBALS[\'__Module__\']->getModel()->' . $function . "($args);");
				$r = $func();
			}else if ($m->getView() && method_exists($m->getView(), $function)) {
				$func = create_function('', 'return $GLOBALS[\'__Module__\']->getView()->' . $function . "($args);");
				$r = $func();
			}else if ($m->getController() && method_exists($m->getController(), $function)) {
				$func = create_function('', 'return $GLOBALS[\'__Module__\']->getContoller()->' . $function . "($args);");
				$r = $func();
			}
			if ($r !== NULL) echo $r;
		}
		
		/**
		 * print content
		 * exec layout cache and merge with doctype, header tags etc...
		 * if encoding is not ut-8, convert encoding to defined encoding
		 */
		static public function prints() {
			if (!self::$contentPrintable) return; // if error printed, return
			
			ob_start();
			CacheHandler::execLayout('/layouts/' . self::$layout . '/layout.html');
			
			$content = ob_get_clean();
			OB_GZIP ? ob_start('ob_gzhandler') : ob_start();
			
			$output = self::getDoctype() . LINE_END .
					  '<html>' . LINE_END .
					  '<head>' . LINE_END .
					  self::getHead() . 
					  '</head>' . LINE_END .
					  '<body>' . LINE_END .
					  self::getBodyTop() . LINE_END.
			 		  $content . LINE_END .
					  self::getBodyBottom() . LINE_END .
					  '</body>' . LINE_END .
					  '</html>';
				 
			if (TEXT_ENCODING != 'utf-8')
				$output = iconv('utf-8', TEXT_ENCODING, $output);
			
			echo $output;
			ob_end_flush();
		}
		
		/**
		 * Print error page
		 */
		static public function printErrorPage($content) {
			ob_clean();
			
			$content = fetchLocale($content);
			Context::set('errorMessage', $content);
			
			self::setLayout('error');
			self::printContent();
			
			exit;
		}
		
		/**
		 * Print warning line
		 */
		static public function printWarning($message) {
			$message = fetchLocale($message);
			
			$backtrace = debug_backtrace();
			$backtrace_path = str_replace("\\", '/', str_replace(ROOT_DIR, '', $backtrace[0]['file']));
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
		 * redirect to specific url
		 */
		static public function redirect($url) {
			echo	self::getDoctype() .
					'<html><head>' .
					'<meta http-equiv="refresh" content="0; url='.$url.'">' .
					'<script type="text/javascript">location.replace("'.$url.'")</script>' .
					'</head><body></body></html>';
			exit;
		}
	}
	