<?php

	/**
	 * @author prevdev@gmail.com
	 * @2013.05 ~ 08
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
		 *
		 * Attributes 컨테이너 객체
		 * Context::set 메소드로 설정하고 Context::get 메소드로 데이터를 불러올 수 있음
		 * 템플릿 내부 변수객체는 모두 이곳에서 관리됨
		 */
		static $attr;
		

		/**
		 * module ID (name)
		 * 메인에서 사용되고 있는 모듈 아이디(이름)
		 */
		public $moduleID;
		

		/**
		 * module Action
		 * 메인에서 사용되고 있는 모듈 액션
		 */
		public $moduleAction;
		

		/**
		 * selected menu
		 * first set by $_GET['menu']
		 * in board or page module, can modify it
		 *
		 * $_GET['menu'] 변수로 설정된 선택된 메뉴
		 * 게시판 모듈이나 페이지 모듈등에서 이를 수정할 수 있음
		 */
		public $selectedMenu;


		/**
		 * parent menu's ids
		 * 부모 메뉴들의 id값
		 */
		public $parentMenus;


		/**
		 * only print module content without doctype, header tags
		 * doctype이나 header 태그를 출력하지 않고 모듈의 내용만 출력할 것인지를 정의 
		 */
		public $printAlone;


		/**
		 * define mobile mode
		 * 모바일 모드인지 정의
		 */
		public $isMobileMode;
		

		/**
		 * define connected in real mobile device
		 * 실제 모바일 디바이스에서 접속했는지를 정의
		 */
		public $isRealMobile;


		/**
		 * HeaderTagHandler object that contains header tag like script, style, meta
		 * script, style, meta 태그등의 header 태그를 담고있는 HeaderTagHandler 객체
		 */
		private $headerTagHandler;
		
		
		/**
		 * layout name
		 * default value is defined in config/config.php
		 * in 'module/info.json' file, you can set the layout of the module or action
		 * modify by using Context::setLayout method
		 *
		 * 현재 사용중인 layout 이름
		 * config/config.php 파일에서 기본 레이아웃을 설정 할 수 있음
		 * module/info.json 파일에서 모듈별 혹은 액션별 레이아웃을 설정할 수 있음
		 * Context::setLayout 메소드로 수정 가능
		 */
		private $layout;
		

		/**
		 * set content printable
		 * if this var is false, can not excute Context::printContent() method
		 *
		 * 컨텐츠가 출력 가능한지를 설정
		 * 이 값이 false일시 Context::printContent() 메소드를 실행 할 수 없음
		 */
		private $contentPrintable = true;


		/**
		 * Get Context instance
		 * Context 인스턴스 반환
		 */
		public static function getInstance() {
			if(!isset($GLOBALS['__Context__'])) {
				$GLOBALS['__Context__'] = new Context();
			}
			
			return $GLOBALS['__Context__'];
		}
		
		/**
		 * Initalize Context instance
		 * Context 인스턴스 초기화
		 */
		public function init($db_info) {
			self::$attr = new StdClass();
			
			$this->headerTagHandler = new HeaderTagHandler();
			$this->setLayout(LAYOUT_NAME);
			$this->printAlone = false;
			$this->isMobileMode = false;

			$mobileAgents  = array('iphone','lgtelecom','skt','mobile','samsung','nokia','blackberry','android','android','sony','phone');
			
			for ($i=0; $i<count($mobileAgents); $i++){ 
				if (preg_match("/{$mobileAgents[$i]}/", strtolower($_SERVER['HTTP_USER_AGENT']))) {
					$this->isMobileMode = true;
					$this->isRealMobile = true;
					break;
				} 
			}
			
			// mobile 이라는 변수가 넘어왔을 때, 쿠키를 심어 지속되도록 설정
			if (isset($_COOKIE['mobile'])) {
				$this->isMobileMode = (int)$_COOKIE['mobile'];
			}
			
			if (isset($_GET['mobile'])) {
				if ($_GET['mobile']) {
					$this->isMobileMode = true;
					setCookie2('mobile', 1, 0);
				}else {
					$this->isMobileMode = false;
					setCookie2('mobile', 0, 0);
				}
			}

			// locale 이라는 변수가 넘어왔을 때, 쿠키를 심어 지속되로고 설정
			if (isset($_GET['locale']))
				setCookie2('locale', $_GET['locale'], 0);
			
			// page 라는 변수가 넘어왔고 module 변수가 넘어오지 않을 때 모듈을 page로 설정
			if (isset($_GET['page']) && !isset($_GET['module'])) $_GET['module'] = 'page';
			
			CacheHandler::init();

			// config/server_info.json 파일에서 해당 서버정보를 설정핮 않아 serverInfo 변수가 정의되지 않았을때 
			if (!isset($GLOBALS['serverInfo'])) {
				Context::printErrorPage(array(
					'en' => 'Cannot find connected server with the server defined in config/server_info.json',
					'ko' => 'config/server_info.json 파일에서 현재 서버와 연결된 서버를 찾을 수 없습니다'
				));
				return;
			}

			// orm 모듈 사용에 필요한 pdo 클래스가 정의되지 않았을 때
			if (!class_exists('PDO')) {
				Context::printErrorPage(array(
					'en' => 'php extension "PDO" does not exists',
					'ko' => 'php 확장 모듈 "PDO"가 존재하지 않습니다'
				));
				return;
			}
			
			ModuleHandler::init();
			DBHandler::init($db_info);
			
			$this->initMenu($_REQUEST);
			
			$this->addMetaTag( array('charset'=>TEXT_ENCODING) );
			$this->addHeaderFile('/static/css/global.css');
			$this->addHeaderFile('/static/js/global.js');
			
			// getUrl 메소드등을 사용할때 필요한 변수 출력
			$this->addHeaderTag(
				'<script type="text/javascript">' .
					'var RELATIVE_URL = "'.RELATIVE_URL . '";' .
					'var USE_SHORT_URL = "'.USE_SHORT_URL . '";' .
					'var REAL_URL = "'.REAL_URL . '";' .
					'var DEFAULT_LOCALE = "'.DEFAULT_LOCALE.'";' .
					'var locale = "'.getLocale().'";' .
				'</script>'
			);

			// DEBUG_MODE가 활성화 되 있을때, var_dump2로 출력한 결과를 하이라이팅 해 주는 js
			if (DEBUG_MODE)
				$this->addHeaderFile('/static/js/vdump.js');
			
			// IE Edge 모드 등 설정
			if (X_UA_Compatible) {
				$this->addMetaTag(
					array('http-equiv'=>'X-UA-Compatible', 'content'=>X_UA_Compatible)
				);
			}
		}
		
		/**
		 * initalize menu and set moduleID, moduleAction
		 * If menu has own module or action, set it's module, action
		 * If module is not defined by getVar or menu, select the menu which setted is_index in database
		 *
		 * 메뉴를 초기화하고 moduleID와 moduleAction을 설정함
		 * 만약 해당 메뉴가 모듈이나 액션을 정의하고 있다면 그 데이터로 설정함
		 * 만약 모듈이 getVar이나 메뉴에 의해 정이되지 않았으면, DB상에서 설정된 is_index menu를 선택시킴
		 */
		
		private function initMenu($getVars) {
			$this->parentMenus = array();

			if (isset($getVars['module'])) {
				// 모듈이 정의됬을때
				$this->moduleID = basename($getVars['module']);
				$this->moduleAction = isset($getVars['action']) ? basename($getVars['action']) : NULL;

			}else if ((isset($getVars['menu']) || isset($getVars['menu1'])) && !isset($getVars['menu2'])) {
				// 1단계 메뉴만 정의됬을때
				if (!isset($getVars['menu']) && isset($getVars['menu1']))
					$getVars['menu'] = $getVars['menu1'];

				$selectedData = DBHandler::for_table('menu')
					->where('title', $getVars['menu'])
					->where('level', 1)
					->find_one();

			}else if ((isset($getVars['menu1']) || isset($getVars['menu'])) && isset($getVars['menu2'])) {
				// 2단계 메뉴 이상
				if (!isset($getVars['menu1']) && isset($getVars['menu']))
					$getVars['menu1'] = $getVars['menu'];

				$i = 1;
				while (!empty($getVars['menu'.$i])) {
					$record = DBHandler::for_table('menu')
						->where('title', $getVars['menu'.$i]);
						
					if (isset($parentId))
						$record = $record->where('parent_id', $parentId);

					if (!empty($getVars['menu'.($i+1)])) {
						$data = $record->find_one();
						if ($data) {
							$parentId = $data->id;
							array_push($this->parentMenus, $data->getData());
						}
					}else {
						$selectedData = $record->find_one();
					}
					$i++;
				}

			}else {
				// 모듈, 메뉴 모두 정의되지 않을 때 -> 기본 메뉴 로드
				$selectedData = DBHandler::for_table('menu')
					->where('is_index', 1)
					->find_one();
			}

			if ($selectedData) {
				$this->moduleID = $selectedData->module;
				$this->moduleAction = $selectedData->action;
				$this->selectedMenu = $selectedData->getData();

				if (!empty($selectedData->extra_vars)) {
					$extraVars = json_decode($selectedData->extra_vars);

					if ($extraVars && $extraVars->linkToSubMenu == true && $selectedData->level == 1) {
						// 2단계 메뉴를 불러옴
						$subMenu = self::getMenu(2);
						// 2단계 메뉴가 1개 이상 존재할때
						if ($subMenu && count($subMenu) > 0)
							// 2단계 메뉴로 리다리렉트
							redirect(getUrl() . (USE_SHORT_URL ? '/'.$selectedData->title.'/' : '/?menu1='.$selectedData->title.'&menu2=') . $subMenu[0]->title);
					}
				}
			}else if ($selectedData === false && !$this->moduleID) {
				self::printErrorPage(array(
					'en' => 'Cannot find requested menu',
					'ko' => '해당 메뉴를 찾을 수 없습니다'
				));
				return;
			}
		}
		
		
		/**
		 * Get menu data
		 * 메뉴 데이터를 반환
		 */
		static public function getMenu($level) {
			if ($level == 1) {
				// 최상위 메뉴 로드
				$arr = DBHandler::for_table('menu')
					->where('level', 1)
					->find_many();
			}else {
				$parentMenus = Context::getInstance()->parentMenus;
				// 부모 메뉴 로드
				if (count($parentMenus) < $level-2) {
					// 부모 메뉴가 없으면 빈 배열 등록
					$arr = array();
				
				}else if (count($parentMenus) == $level-2) {
					// 자식 메뉴가 있을 경우 메뉴 로드	
					$arr = DBHandler::for_table('menu')
						->where('parent_id', Context::getInstance()->selectedMenu->id)
						->find_many();

				}else {
					$arr = DBHandler::for_table('menu')
						->where('parent_id', $parentMenus[$level-2]->id)
						->find_many();
				}
			}

			$menuDatas = array_merge(
				Context::getInstance()->parentMenus,
				array(Context::getInstance()->selectedMenu)
			);
			$menuTitleDatas = array();
			
			for ($i=0; $i<count($menuDatas); $i++) {
				if ($i > $level - 2) continue;
				array_push($menuTitleDatas, $menuDatas[$i]->title);
			}
			

			for ($i=0; $i<count($arr); $i++) {
				$arr[$i] = $arr[$i]->getData();
				$arr[$i]->className = 'menu-' . $arr[$i]->title;
				
				if (Context::getInstance()->selectedMenu->id == $arr[$i]->id)
					$arr[$i]->selected = true;
				
				for ($j=0; $j < count(Context::getInstance()->parentMenus); $j++) { 
					if ($arr[$i]->id == Context::getInstance()->parentMenus[$j]->id)
						$arr[$i]->selected = true;
				}

				if (isset($arr[$i]->visible_group) && User::getCurrent() && !User::getCurrent()->checkGroup($arr[$i]->visible_group))
					$arr[$i]->visible = false;

				if ($arr[$i]->is_index && USE_SHORT_URL)
					$arr[$i]->title = '';

				
				if (!empty($arr[$i]->extra_vars)) {
					$arr[$i]->extra_vars = json_decode($arr[$i]->extra_vars);
					$arr[$i]->extraVars = $arr[$i]->extra_vars;
					
					if (!empty($arr[$i]->extraVars->link)) {
						$arr[$i]->link = $arr[$i]->extraVars->link;
						$arr[$i]->href = $arr[$i]->link;
					}
					if (!empty($arr[$i]->extraVars->linkTarget))
						$arr[$i]->linkTarget = $arr[$i]->extraVars->linkTarget;
				}

				$tempArr = array_merge(
					$menuTitleDatas,
					array($arr[$i]->title)
				);

				if (!isset($arr[$i]->href)) {
					if (USE_SHORT_URL)
						$arr[$i]->href = RELATIVE_URL . '/' . join('/', $tempArr);
					else {
						$arr[$i]->href = RELATIVE_URL . '/?';
						if (count($tempArr) == 1)
							$arr[$i]->href .= 'menu=' . $tempArr[0];
						else {
							for ($j=0; $j<count($tempArr); $j++)
								$arr[$i]->href .= 'menu' . ($j+1) . '=' . $tempArr[$j] . '&';
							$arr[$i]->href = substr($arr[$i]->href, 0, strlen($arr[$i]->href)-1);
						}
					}
				}
				$arr[$i]->title_locale = fetchLocale($arr[$i]->title_locales);
			}
			return $arr;
		}

		/**
		 * using in getMenu() method, get parent menus
		 * getMenu() 메소드에서 사용, 부모 메뉴를 가져옴
		 */
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
		 * set context var ($attr)
		 * Context 변수($attr) 설정
		 */
		static public function set($key, $value) {
			if ($value === NULL)
				unset(self::$attr->{$key});
			else
				self::$attr->{$key} = $value;
		}
		
		/**
		 * get context var ($attr)
		 * Context 변수($attr) 불러옴
		 */
		static public function get($key) {
			if (!isset(self::$attr->{$key}))
				return NULL;
			else
				return self::$attr->{$key};
		}
		
		/**
		 * set the layout
		 * 레이아웃 파일 설정
		 */
		public function setLayout($name) {
			if (!is_file(ROOT_DIR . '/layouts/' . $name . '/layout.html')) {
				Context::printErrorPage(array(
					'en' => 'layout "'.$name.'" does not exist',
					'ko' => '레이아웃 파일 "'.$name.'" 이 존재하지 않습니다'
				));
				return;
			}
			
			$this->layout = $name;
		}
		
		
		/**
		 * set browser title, <title> tag
		 * <title> 태그로 정의되는 브라우저 제목 설정 
		 */
		public function setTitle($title) {
			$this->headerTagHandler->setBrowserTitle($title);
		}
		
		
		/**
		 * add header files like css/js/favicon
		 * @param $path : path of file
		 * @param $index : if $index is -1, push file in last of array, else push file in defined index
		 * @param $position : position of header files / generally added in head, body-top or body-bottom
		 * @param $requiredAgent : if $requiredAgent !== NULL, check user agent and matching of them.
		 *							if $requiredAgent doesn't matche with userAgent, header file is not added
		 * @param $targetie : target of added ie, if @param is not NULL, header tag is reaplaced to <!--[if @param]>HEADER_TAG<![endif]-->
		 *
		 *
		 * css/js/favicon 같은 헤더 파일을 추가함
		 * @param $path : 파일 경로 설정
		 * @param $index : $index가 -1 일때 베열 마지막에 파일 추가, 그렇지 않으면 설정된 index에 집어넣음
		 * @param $position : 헤더 파일의 위치를 정의함 / 일반적으로 head 나 body-top, body-bottom 에 추가함
		 * @param $requiredAgent : 만약 $requiredAgent이 NULL이 아니면, user agent 를 체크하고 match 하는지 파악, match 하지 않으면 파일 추가 안됨
		 * @param $targetie : 추가될 IE 정의, @param 이 NULL이 아니면, 헤더 태그는 <!--[if @param]>HEADER_TAG<![endif]--> 로 교체됨
		 *
		 */
		public function addHeaderFile($path, $index=-1, $position='head', $targetie=NULL, $requiredAgent=NULL, $mobile=NULL) {
			if (substr($path, 0, 2) != '//' && strpos($path, '://') === false) {
				if (substr($path, 0, 1) != '/')
					$path = '/' . $path;

				$pos = strrpos($path, '/');
				if ($this->isMobileMode && is_file(ROOT_DIR . substr($path, 0, $pos) . '/m.' . substr($path, $pos+1)))
					$path = substr($path, 0, $pos) . '/m.' . substr($path, $pos+1);
				
				if (!is_file(ROOT_DIR . $path)) {
					self::printWarning(array(
						'en' => 'fail to load file "<b>'.$path.'"</b>',
						'ko' => '파일을 불러오는데 실패했습니다 - "<b>'.$path.'"</b>'
					));
					return;
				}
			}
			
			if (isset($requiredAgent) && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), strtolower($requiredAgent)) === false)
				return;	
			if (isset($mobile) && !$this->isMobileMode)
				return;

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
						'ko' => '알수없는 파일을 불러오려했습니다 - <b>"'.$path.'"</b>'
					));
					break;
			}
		}
		
		/**
		 * add meta tag
		 * @param $stringOrObj can be Sting type of Object(Array) type.
		 *		In string type, input as '<meta name="name" value="data">'
		 *		In Object(Array) type, input as array('name'=>'name', 'value'=>'data')
		 *
		 * 메타 태그 추가
		 * @param $stringOrObj 인자는 문자열 타입이거나 객체(배열) 타입이다
		 *		문자열 타입의 경우, '<meta name="name" value="data">' 처럼 입력한다
		 *		객체(배열) 타입의 경우, array('name'=>'name', 'value'=>'data') 처럼 입력한다
		 */
		public function addMetaTag($stringOrObj, $index=-1) {
			$this->headerTagHandler->addMetaTag($stringOrObj, $index);
		}
		

		/**
		 * add header tags without js/css/favicon/meta tag
		 * js/css/favicon/meta 외의 헤더 태그를 추가함
		 */
		public function addHeaderTag($string, $index=-1) {
			$this->headerTagHandler->addHeaderTag($string, $index);
		}
		
		/**
		 * get doctype tag by defined const 'DOCTYPE' in config/config.php
		 * config/config.php 에 정의된 DOCTYPE 상수에 따라 doctype 태그를 출력함
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
		 * Get header files tags in <head>
		 * <head> 태그 안에 들어갈 헤더 태그 반환
		 */
		public function getHead() {
			return $this->headerTagHandler->getTags('head');
		}
		
		/**
		 * Get js and css file tags in body-top (without meta,script,etc tags)
		 * body-top(<body> 태그 내부 맨위) 안에 들어갈 헤더 태그 반환 (meta,script 태그 등은 출력되지 않음)
		 */
		public function getBodyTop() {
			return $this->headerTagHandler->getTags('body-top');
		}
		
		/**
		 * Get js and css file tags in body-bottom (without meta,script,etc tags)
		 * body-bottom(<body> 태그 내부 맨아래) 안에 들어갈 헤더 태그 반환 (meta,script 태그 등은 출력되지 않음)
		 */
		public function getBodyBottom() {
			return $this->headerTagHandler->getTags('body-bottom');
		}
		
		/**
		 * Print error page
		 * 오류 페이지 출력
		 */
		static public function printErrorPage($content) {
			ob_clean();
			
			$content = str_replace("\r\n", '<br>', $content);
			$content = str_replace("\n", '<br>', $content);
			$content = fetchLocale($content);
			Context::set('errorMessage', $content);
			
			self::getInstance()->setLayout('error');
			self::getInstance()->procLayout();

			exit;
		}
		
		/**
		 * Print warning line
		 * 경고 라인 (분홍색) 표시
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
		 * Check SSO (Single-Sign-On) and initialize
		 * SSO (통합 인증 서비스) 를 체크하고 초기화함
		 *
		 * 로그인시 세션값을 지닌 와일드 카드 쿠키를 심고 session db에 세션을 심음 (이때 로그인 서버 )
		 * 유저 정보를 가져올땐 이 쿠키값을 sso 서버에 넘겨주면 sso 서버가 유저 정보를 반환
		 * 받은 유저정보는 PHP 세션안에 저장되며 PHP 세션이 만료되면 다시 sso 서버에 요청하며 데이터 저장
		 * 고로 쿠키는 sso를 사용하는 모든 서버에 저장되며 세션을 서버별로 저장된다
		 * 이런 구조상 어카운트 서버에서 유저 정보를 수정하고 세션을 업데이트한다해도 다른 서버에서는 업데이트 되지 않는데,
		 * 이를 방지하기 위해 synctime 세션과 쿠키를 두어 synctime이 맞지 않으면 세션을 지우고 다시 로드하는 방식을 사용한다
		 *
		 */
		public function checkSSO() {
			if (!isset($_COOKIE[SSO_COOKIE_NAME]) && isset($_SESSION[SSO_SESSION_NAME]))
				unset($_SESSION[SSO_SESSION_NAME]);

			if (!$_COOKIE[SSO_COOKIE_NAME.'_synchash'] || $_COOKIE[SSO_COOKIE_NAME.'_synchash'] != $_SESSION[SSO_SESSION_NAME.'_synchash']) {
				// 와일드 카드 쿠키와 세션의 싱크를 맞춰줌
				unset($_SESSION[SSO_SESSION_NAME]);
			}

			if (isset($_COOKIE[SSO_COOKIE_NAME]) && !isset($_SESSION[SSO_SESSION_NAME])) {
				$urlData = getUrlData(SSO_URL . '?sess_key=' . $_COOKIE[SSO_COOKIE_NAME], SSO_AGENT_KEY);
				
				if (!$urlData) {
					unset($_SESSION[SSO_SESSION_NAME]);
					setCookie2(SSO_COOKIE_NAME, '', time()-60);

					Context::printErrorPage(array(
						'en' => 'cannot load sso data',
						'ko' => 'SSO 데이터를 불러올 수 없습니다'
					));
					return false;
				}
				
				$ssoData = json_decode($urlData);

				if ($ssoData->result == 'expired') {
					unset($_SESSION[SSO_SESSION_NAME]);
					setCookie2(SSO_COOKIE_NAME, '', time()-60);
					return true;
				}

				if (!$ssoData || $ssoData->result == 'fail') {
					unset($_SESSION[SSO_SESSION_NAME]);
					setCookie2(SSO_COOKIE_NAME, '', time()-60);

					Context::printErrorPage(array(
						'en' => 'fail loading sso data',
						'ko' => 'SSO 데이터를 불러오는데 실패하였습니다.'
					));
					return false;
				}
				$userData = $ssoData->userData;
				
				$_SESSION[SSO_SESSION_NAME] = $ssoData;
				$_SESSION[SSO_SESSION_NAME.'_synchash'] = md5($urlData);
				setCookie2(SSO_COOKIE_NAME.'_synchash', md5($urlData), strtotime($ssoData->expireTime));
				setCookie2(SSO_COOKIE_NAME, $_COOKIE[SSO_COOKIE_NAME], strtotime($ssoData->expireTime));
				
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
		 * if encoding is not utf-8, convert encoding to defined encoding
		 * if $this->printAlone is true, do not exec layout
		 *
		 * 컨텐츠를 출력함
		 * 레이아웃 캐시를 실행하고 doctype, header 태그등과 함침
		 * 인코딩이 utf-8이 아니면 정의된 인코딩으로 변형하여 출력
		 * $this->printAlone 이 true 이면 레이아웃 없이 내용만 출력함
		 */
		public function procLayout() {
			if (!$this->contentPrintable) return; // if error printed, return

			ob_start();
			
			if ($this->isMobileMode && is_file(ROOT_DIR . '/layouts/m.' . $this->layout . '/layout.html'))
				$this->layout = 'm.' . $this->layout;
			

			if ($this->printAlone) {
				$this->getModuleContent();
				ob_end_flush();
				
			}else {
				CacheHandler::execTemplate('/layouts/' . $this->layout . '/layout.html');

				$content = ob_get_clean();
				
				$acceptEncodings = explode(',', $_SERVER['HTTP_ACCEPT_ENCODING']);
				$gzipAvailable = array_search('gzip', $acceptEncodings) !== false;

				(OB_GZIP && $gzipAvailable) ? ob_start('ob_gzhandler') : ob_start();

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

		/**
		 * get module content
		 * same with getContent() function
		 *
		 * 모듈의 컨텐츠를 불러옴
		 * getContent() 함수와 같음
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
					'ko'=>'모듈 콘텐츠를 불러올 수 없습니다'
				));
			else {
				$module->exec();
			}
		}
	}
