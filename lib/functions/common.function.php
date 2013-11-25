<?php
	
	/**
	 * @author prevdev@gmail.com
	 * @2013.05 ~ 08
	 *
	 * common functions
	 */


	
	/**
	 * 디버깅을 위한 함수
	 * 출력 내용은 var_dump와 같으나 DEBUG_MODE=true 에서 예쁘게 하이라이팅됨 
	 */
	function var_dump2($obj) {
		$bt = debug_backtrace();
		
		echo '<x y=""><pre class="vdump">';
		echo '<span class="vdump-first-line">var dumped in "' . getFilePathClear($bt[0]['file']) . '" on line ' . $bt[0]['line'] . "</span>\n";
		var_dump($obj);
		echo '</x></pre>';
	}


	/**
	 * 모듈의 내용을 불러오는 함수
	 * 다중 모듈 이용시 사용
	 * @param $moduleID의 값이 null일시 default 모듈의 내용을 불러옴
	 */
	function getContent($moduleID=NULL, $moduleAction=NULL, $queryParam=NULL) {
		Context::getInstance()->getModuleContent($moduleID, $moduleAction, $queryParam);
	}


	/**
	 * menu의 내용을 html <li> 로 가공한 내용을 반환함
	 * @param $level 은 가져올 메뉴의 level을 정의함
	 * @param $noDeco 는 <li> 태그 밑 <a> 태그에서 class="no-deco" 를 사용할지 말지 결정
	 */
	function getMenuTag($level, $noDeco=true) {
		$html = '';
		foreach(Context::getMenu($level) as $key => $menu) {
			$html .= 
				'<li class="'.$menu->className . ($menu->selected ? ' ' . $menu->className . '-selected selected' : '') . '">' .
					'<a href="' . $menu->href . '" class="'.($noDeco == true ? 'no-deco' : '').'"'.($menu->linkTarget ? ' target="'.$menu->linkTarget.'"' : '').'>' .
						$menu->title_locale .
					'</a>' .
				'</li>';
		}
		return $html;
	}
	
	/**
	 * 파일 경로를 깨끗하게 출력함
	 * ex) C:\APM_Setup\htdocs\pmc\index.php -> /index.php
	 */
	function getFilePathClear($path) {
		return str_replace("\\", '/', str_replace(ROOT_DIR, '', $path));
	}
	
	/**
	 * 상위 경로를 불러옴
	 * ex) /modules/index/template/ -> /modules/index/
	 */
	function getUpperPath($path) {
		$path = str_replace("\\", '/', $path);
		if (substr($path, strlen($path)-1) == '/') {
			$path = substr($path, 0, strlen($path)-1);
			$end = '/';
		}
		if (strrpos($path, '/') === false)
			return $path . '/..' . (isset($end) ? $end : '');
		else
			return substr($path, 0, strrpos($path, '/')) . (isset($end) ? $end : '');
	}
	
	
	/**
	 * 문자의 길이만큼 빈곳에 0을 집어넣음
	 * 시간표시시 주로 사용
	 * @param $length는 0을 채워넣을 길이를 정의함
	 * ex) 11:57:02
	 */
	function fillZero($str, $length=2) {
		for ($i=0; $i<$length-strlen($str); $i++)
			$str = '0' . $str;
		return $str;
	}
	function set0($str, $length=2) { return fillZero($str, $length); }
	
	
	/**
	 * 루트 uri, 세션으로 쿠키를 심음
	 */
	function setCookie2($name, $value, $expire=0, $secure=false, $httponly=false) {
		setcookie($name, $value, $expire, SESSION_URI, SESSION_DOMAIN, $secure, $httponly);
	}
	
	
	/**
	 * form 데이터 수신시 checkbox 내용 체크
	 * 값이 true 이거나 on 일시 true 반환
	 */
	function evalCheckbox($formData) {
		return (int)(isset($formData) && 
			($formData == true || strtolower($formData) == 'on'));
	}

	/**
	 * 상대적 시간 출력
	 * @param $time은 timestamp 값임
	 * ex) 2013-06-23 11:32:12 -> 13분 전
	*/
	function getRelativeTime($time) {
		if (is_string($time)) $time = strtotime($time);

		if ($time + 60 > time())
			return fetchLocale(array('en'=>'Now', 'ko'=>'방금 전'));
		else if ($time + 3600 > time())
			return (int)((time() - $time) / 60)  . fetchLocale(array('en'=>'minute ago', 'ko'=>'분 전'));
		else if ($time + 86400 > time())
			return (int)((time() - $time) / 3600) . fetchLocale(array('en'=>'hour ago', 'ko'=>'시간 전'));
		else
			return date('Y.m.d', $time);	
	}
	
	/**
	 * 현재 설정 언어 정보를 출력
	 * @param $compareLocal에 값을 지정시 현재 언어정보와 같은지 비교한 값을 반환 (bool type)
	 */
	function getLocale($compareLocale=NULL) {
		if (isset($_GET['locale']))
			$locale = $_GET['locale'];
		else if (isset($_COOKIE['locale']))
			$locale = $_COOKIE['locale'];
		else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			$locale = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		else
			$locale = DEFAULT_LOCALE;
		
		if ($compareLocale)
			return strtolower($locale) == strtolower($compareLocale);
		else
			return strtolower($locale);
	}
	/**
	 * 언어를 파싱함
	 * @param $data 에는 object, array, string(json), string(raw) 등이 올 수 있음
	 * @param $data 에 raw string 값이 올 시 그대로 출력함
	 * 
	 * object: (object) array('en' => 'Freeboard', 'ko' => '자유게시판')
	 * array: array('en' => 'Freeboard', 'ko' => '자유게시판')
	 * string(json): {"en":"Freeboard", "ko":"자유게시판"}
	 * string(raw): "자유게시판"
	 */
	function fetchLocale($data) {
		$locale = getLocale();
		switch (gettype($data)) {
			case 'object' :
				if (isset($data->{$locale}))
					return $data->{$locale};
				else if (isset($data->{DEFAULT_LOCALE}))
					return $data->{DEFAULT_LOCALE};
				else {
					foreach ($data as $key => $content)
						return $content;
				}
			break;
			
			case 'array' :
				if (isset($data[$locale]))
					return $data[$locale];
				else if (isset($data[DEFAULT_LOCALE]))
					return $data[DEFAULT_LOCALE];
				else {
					foreach ($data as $key => $content)
						return $content;
				}
			break;
			
			case 'string' :
				if (json_decode($data) !== NULL)
					return fetchLocale(json_decode($data));
				else
					return $data;
				break;
			
			default :
				return $data;
		}
			
	}
	
	/**
	 * 파일 내용을 모두 읽어서 출력함
	 */
	function readFileContent($filePath) {
		/*if (!is_file($filePath) || !is_readable(dirname($filePath))) return;
		
		$fp = fopen($filePath, 'r');
		$content = ''; 
		while(!feof($fp))
			$content .= fgets($fp, 1024);
		return $content;*/

		return file_get_contents($filePath);
	}
	
	/**
	 * json_encode와 비슷
	 * 유니코드 값을 \ucXXX 로 치환하지 않으며 json_encode가 구현되지 않은서버에서도 사용할 수 있음
	 */
	function json_encode2($data) {
		switch(gettype($data)) {
			case 'NULL':
				return 'null';
			case 'boolean':
				return $data ? 'true' : 'false';
			case 'integer':
			case 'double':
				return $data;
			case 'string':
				return '"' . strtr($data, array('\\' => '\\\\', '"' => '\\"')) . '"';
			case 'object':
				$data = get_object_vars($data);
			case 'array':
				$rel = FALSE; // relative array?
				$key = array_keys($data);
				foreach($key as $v) {
					if(!is_int($v)) {
						$rel = TRUE;
						break;
					}
				}
	
				$arr = array();
				foreach($data as $k => $v)
					$arr[] = ($rel ? '"' . strtr($k, array('\\' => '\\\\', '"' => '\\"')) . '":' : '') . json_encode2($v);
	
				return $rel ? '{' . join(',', $arr) . '}' : '[' . join(',', $arr) . ']';
			default:
				return '""';
		}
	}
	
	/**
	 * 쿼리 데이터 이스케이프 함수
	 * mysql_real_ecape_string과 비슷함
	 */
	function escape($string) {
		return DBHandler::escapeString($string);
	}
	
	/**
	 * @param $url 로 정의된 url으로 http 통신을 한 뒤 결과값을 반환함
	 * @param $userAgent 설정시 해당 userAgent를 첨가하여 송신
	 *
	 * @return 해당 url에서 반환한 값에서 헤더를 잘라낸뒤 출력
	 *			데이터 로딩에 실패할시 NULL 반환
	 */
	function getUrlData($url, $userAgent=NULL) {
		if (substr($url, 0, 2) == '//')
			$url = PROTOCOL . '://' . substr($url, 2);
		
		$temp = explode('://', $url);
		$temp = explode('/', $temp[1]);

		$host = $temp[0];
		$port = strstr($url, 'https://') ? 443 : 80;
		$output = '';

		if (!($fp = fsockopen(($port == 443 ? 'ssl://'.$host : $host), $port))) return NULL;
		
		fputs($fp,
			"GET ${url} HTTP/1.0\r\n" .
			"Host: ${host}\r\n" .
			'User-Agent: Mozilla/5.0 (Windows NT 6.2; WOW64) PHP fsocket' . ($userAgent ? ' ' . $userAgent : '') . "\r\n\r\n");

		while(!feof($fp))
			$output .= fgets($fp, 1024);	
		
		// 헤더 정보 잘라내기
		$output = substr($output, strpos($output, "\r\n\r\n")+4);
		
		fclose($fp);
		return $output; 
	}
	
	/**
	 * config/server-info.json에서 정의된 현재 서버의 정보를 반환
	 */
	function getServerInfo() {
		if (!empty($GLOBALS['serverInfo'])) return $GLOBALS['serverInfo'];
		
		$serverInfo = json_decode(file_get_contents(SERVER_INFO_FILE_PATH));
		foreach($serverInfo as $_key => $_value) {
			if (strrpos($_value->uri, '/') === strlen($_value->uri)-1)
				$_value->uri = substr($_value->uri, 0, strlen($_value->uri)-1);

			if ($_SERVER['HTTP_HOST'] === $_value->host . (isset($_value->port) ? ':' . $_value->port : '')) {
				if (!defined('DEBUG_MODE'))
					define('DEBUG_MODE', ($_value->type === 'test'));
				
				$GLOBALS['serverInfo'] = $_value;
				return $GLOBALS['serverInfo'];
				break;
			}
		}
		if (!defined('DEBUG_MODE'))
			define('DEBUG_MODE', true);
		return NULL;
	}
	
	/**
	 * config/server-info.json 를 기반으로 pmc 엔진의 절대 경로를 반환
	 */
	function getRelativeUrl() {
		if (defined('RELATIVE_URL')) return RELATIVE_URL;
		
		return ($serverInfo = getServerInfo()) ?
			( ($serverInfo->protocol == '//' ? '//' : $serverInfo->protocol . '://') . $serverInfo->host . (isset($serverInfo->port) ? ':' . $serverInfo->port : '') . $serverInfo->uri) :
			PROTOCOL . '://' . $_SERVER['HTTP_HOST'];
	}
	
	/**
	 * config/server-info.json 에서 정의된 session_domain을 반환
	 * config/server-info.json 에서 정의되지 않을 시 현재의 http_host를 반환
	 */
	function getSessionDomain() {
		if (defined('SESSION_DOMAIN')) return SESSION_DOMAIN;
		
		$serverInfo = getServerInfo();
		return (!empty($serverInfo) && isset($serverInfo->session_domain)) ?
			($serverInfo->session_domain) :
			$_SERVER['HTTP_HOST'];
	}

	/**
	 * config/server-info.json 에서 정의된 session_uri을 반환
	 * config/server-info.json 에서 정의되지 않을 시 기본 uri를 반환
	 */
	function getSessionUri() {
		if (defined('SESSION_URI')) return SESSION_URI;

		$serverInfo = getServerInfo();
		return (!empty($serverInfo) && isset($serverInfo->session_uri)) ?
			($serverInfo->session_uri) :
			getServerUri();
	}
	
	function getServerUri() {
		if (defined('SERVER_URI')) return SERVER_URI;

		return getServerInfo()->uri;
	}

	/**
	 * url 관련 정보를 반환
	 *
	 * @param $module 정의시 해당 모듈의 절대경로를 반환함
 	 * @param $action 까지 정의시 action이 포함된 모듈의 절대경로 반환
	 * @param $queryParame 까지 정의시 module, action 파라미터 뒤에 추가 파라미터값을 넣어 반환
	 * @param $url 까지 정의시 RELAVITE_URL이 아닌 $url을 기반으로 파라미터값을 더해서 반환
	 *		* $url에서 미리 정의된 파라미터 이름과 $queryParam 등에서 정의된 파라미터 이름이 겹칠 시 $queryParam 등에서 정의된 파라미터 값이 우선
	 *		* 우선 순위는 module==action > queryParam > url
	 * @param $module이 NULL일시 @param $action은 무시됨
	 */
	function getUrl($module=NULL, $action=NULL, $queryParam=NULL, $url=NULL, $removeAmp=false) {
		if (!$url) $url = RELATIVE_URL;
		
		$parsedUrl = parse_url($url);
		$queryObj = new StdClass();

		if (isset($parsedUrl['query']))
			$queryObj = (object) urlQueryToArray($parsedUrl['query']);
		
		if (is_string($queryParam)) (object) $queryParam = urlQueryToArray($queryParam);
		if ($queryParam) {
			foreach ($queryParam as $key => $content) {
				if (isset($key) && $content !== NULL) $queryObj->{$key} = $content;
			}
		}

		if (isset($module) && !empty($queryObj->module))
			$queryObj->module = NULL;
		if (isset($action) && !empty($queryObj->action))
			$queryObj->action = NULL;
		
		$parsedUrl['query'] = arrayToUrlQuery($queryObj);

		if (isset($module)) {
			if (isset($action))
				$parsedUrl['query'] = 'action=' . $action . '&amp;' . $parsedUrl['query'];
			$parsedUrl['query'] = 'module=' . $module . '&amp;' . $parsedUrl['query'];
		}
		
		if ($parsedUrl['query'] == '') $parsedUrl['query'] = NULL;
		
		if ($parsedUrl['query'] != NULL) {
			if (strrpos($parsedUrl['path'], '/') !== strlen($parsedUrl['path'])-1)
				$parsedUrl['path'] .= '/';
			if (strrpos($parsedUrl['query'], '&amp;') === strlen($parsedUrl['query'])-5)
				$parsedUrl['query'] = substr($parsedUrl['query'], 0, strlen($parsedUrl['query']) - 5);
		}

		if ($removeAmp)
			$parsedUrl['query'] = str_replace('&amp;', '&', $parsedUrl['query']);

		return unparse_url($parsedUrl);
	
	}

	/**
	 * getUrl함수에서 @param $module, @param $action이 빠진 하수
	 */
	function getUrlA($queryParam, $url=NULL, $removeAmp=false) {
		return getUrl(NULL, NULL, $queryParam, $url, $removeAmp);
	}
	
	/**
	 * 현재 url 반환
	 */
	function getCurrentUrl() {
		return REAL_URL;
	}


	/**
	 * 실제 url 반환
	 * @param $module이 NULL일시 현재 url을 반환하며, NULL이 아닐시 해당 모듈의 실제 url을 반환
	 */
	function getRealUrl($module=NULL) {
		if ($module === NULL)
			return REAL_URL;
		else
			return RELATIVE_URL . '/modules/' . $module;
	}

	/**
	 * parse_url()의 역함수
	 * getUrlA 함수에서 쓰임
	 */
	function unparse_url($parsed_url) { 
		$scheme	= isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '//'; 
		$host	 = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
		$port	 = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
		$user	 = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
		$pass	 = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
		$pass	 = ($user || $pass) ? "$pass@" : ''; 
		$path	 = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
		$query	= isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 
		$fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : ''; 
		
		return $scheme . $user . $pass . $host . $port . $path . $query . $fragment; 
	}
	
	/**
	 * array('param1' => 'val1', 'param2' => 'val2') 같은 배열을 'param1=val1&param2=val2' 의 string값으로 변환함
	 */
	function arrayToUrlQuery($array) {
		if (!$array) return NULL;
		else {
			$tempArr = array();
			foreach($array as $key => $content) {
				if (!$key || $content === NULL)
					continue;
				else if ($key && !isset($content))
					array_push($tempArr, $key);
				else
					array_push($tempArr, $key . '=' . $content);
			}
			return join('&amp;', $tempArr);
		}
	}

	/**
	 * arrayToUrlQuery() 의 역함수
	 * 'param1=val1&param2=val2' 같은 문자열을 array('param1' => 'val1', 'param2' => 'val2') 같은 배열 값으로 변환함
	 */
	function urlQueryToArray($query) {
		$arr = array();
		$query = join('&', explode('&amp;', $query));
		$tempArr = explode('&', $query);

		for ($i=0; $i<count($tempArr); $i++) {
			if (strpos($tempArr[$i], '=') === false)
				$arr[$tempArr[$i]] = NULL;
			else {
				$tempArr2 = explode('=', $tempArr[$i]);
				if ($tempArr2[0])
					$arr[$tempArr2[0]] = $tempArr2[1];
			}
		}
		return $arr;
	}

	/**
	 * 뒤로가기등으로 사용할 이전 url을 불러옴
	 */
	function getBackUrl() {
		if ($_GET['next'])
			return removeXSS(urldecode($_GET['next']));
		else if ($_SERVER['HTTP_REFERER'])
			return removeXSS($_SERVER['HTTP_REFERER']);
		else
			return RELATIVE_URL;
	}
	
	/**
	 * url 리다이렉트
	 */
	function redirect($url, $ob_clean=true) {
		$url = str_replace('&amp;', '&', $url);
		if ($ob_clean) {
			ob_clean();
			echo Context::getInstance()->getDoctype() .
					'<html><head>' .
					'<meta http-equiv="refresh" content="0; url='.$url.'">' .
					'<script type="text/javascript">location.replace("'.$url.'")</script>' .
					'</head><body></body></html>';
			exit;
		}else
			echo '<script type="text/javascript">location.replace("'.$url.'");</script>';
	}

	/**
	 * 로그인 페이지로 이동
	 */
	function goLogin() {
		redirect( getUrlA('next='.urlencode(REAL_URL), LOGIN_URL));
	}


	/**
	 * 뒤로 이동
	 * @param $alertMessage : 정의시 해당 메시지로 경고창을 한번 뛰운 뒤 뒤로 이동
	 * @param $clearContents : true일때 이전 내용을 ob_clean 한 후 뒤로 이동
	 */
	function goBack($alertMessage=NULL, $clearContents=true) {
		if (!is_string($alertMessage))
			$alertMessage = fetchLocale($alertMessage);

		if ($clearContents) {
			ob_clean();

			echo Context::getInstance()->getDoctype() .
				'<html><head>' .
				'<script type="text/javascript">' .
				($alertMessage ? 'alert("'.$alertMessage.'");' : '') .
				'location.replace("'.getBackUrl().'");</script>' .
				'</head><body></body></html>';
			
			exit;
			
		}else {
			echo '<script type="text/javascript">' .
				($alertMessage ? 'alert("'.$alertMessage.'");' : '') .
				'location.replace("'.getBackUrl().'");</script>';
		}
	}


	/**
	 * 파일 사이즈를 예쁘게 출력
	 * ex) 36KB, 11MB, 5GB 
	 * @param $size : 파일 크키 (정수형, 단위 : 바이트)
	 */
	function getClearFileSize($size) {
		if ($size > 1024 * 1024 * 1024)
			return round($size / (1024 * 1024 * 1024) * 10) / 10 . 'GB';

		else if ($size > 1024 * 1024)
			return round($size / (1024 * 1024) * 10) / 10 . 'MB';

		else if ($size > 1024)
			return round($size / 1024 * 10) / 10 . 'KB';

		else
			return $size . 'Byte';
	}


	/**
	 * XSS 태그 제거
	 * @param $content : HTML 콘텐츠
	 * https://gist.github.com/mbijon/1098477
	 */
	function removeXSS($data) {
		// Fix &entity\n;
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
	
		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

		// Remove javascript: and vbscript: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

		$data = preg_replace('#<([^>]*?)(form|input|select|textarea|button|iframe|object)([\s\S]*?)>#', '&lt;$1$2$3&gt;', $data);

		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
 	
		do {
				// Remove really unwanted tags
				$old_data = $data;
				$data = preg_replace('#<(/*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>)#i', '&lt;$1&gt;', $data);
		}
		while ($old_data !== $data);
 		
		// we are done...
		return $data;
}
	