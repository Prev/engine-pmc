<?php
	
	/**
	 * 디버깅을 위한 함수
	 * 출력 내용은 var_dump와 같으나 DEBUG_MODE=true 에서 예쁘게 하이라이팅됨 
	 */
	function var_dump2($obj) {
		$bt = debug_backtrace();
		
		echo '<pre class="vdump">';
		echo '<span class="vdump-first-line">var dumped in "' . getFilePathClear($bt[0]['file']) . '" on line ' . $bt[0]['line'] . "</span>\n";
		var_dump($obj);
		echo '</pre>';
	}


	/**
	 * 모듈의 내용을 불러오는 함수
	 * 다중 모듈 이용시 사용
	 * @param $moduleID의 값이 null일시 default 모듈의 내용을 불러옴
	 */
	function getContent($moduleID=NULL, $moduleAction=NULL) {
		Context::getInstance()->getModuleContent($moduleID, $moduleAction);
	}


	/**
	 * menu의 내용을 html <li> 로 가공한 내용을 반환함
	 * @param $level 은 가져올 메뉴의 level을 정의함
	 * @param $noDeco 는 <li> 태그 밑 <a> 태그에서 class="no-deco" 를 사용할지 말지 결정
	 */
	function getMenuliTag($level, $noDeco=true) {
		$html = '';
		foreach(Context::getMenu($level) as $key => $menu) {
			$html .= 
				'<li class="'.$menu->className.'">' .
					'<a href="' . RELATIVE_URL . (USE_SHORT_URL ? '' : '?menu=') . $menu->title . '" class="'.($noDeco == true ? 'no-deco' : '').'">' .
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
	 * 문자의 길이만큼 빈곳에 0을 집어넣음
	 * 시간표시시 주로 사용
	 * @param $length는 0을 채워넣을 길이를 정의함
	 * ex) 11:57:02
	 */
	function set0($str, $length=2) {
		for ($i=0; $i<$length-strlen($str); $i++)
			$str = '0' . $str;
		return $str;
	}
	
	/**
	 * form 데이터 수신시 checkbox 내용 체크
	 * 값이 true 이거나 on 일시 true 반환
	 */
	function evalCheckbox($formData) {
		return isset($formData) && 
			($formData == true || strtolower($formData) == 'on');
	}

	/**
	 * 상대적 시간 출력
	 * @param $time은 timestamp 값임
	 * ex) 2013-06-23 11:32:12 -> 13분 전
	*/
	function getRelativeTime($time) {
		if ($time + 60 > time())
			return '방금 전';
		else if ($time + 3600 > time())
			return (int)((time() - $time) / 60)  . '분 전';
		else if ($time + 86400 > time())
			return (int)((time() - $time) / 3600) . '시간 전';
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
	 * object: (object) array('en' => 'Freeboard', 'kr' => '자유게시판')
	 * array: array('en' => 'Freeboard', 'kr' => '자유게시판')
	 * string(json): {"en":"Freeboard", "kr":"자유게시판"}
	 * string(raw): "자유게시판"
	 */
	function fetchLocale($data) {
		$locale = getLocale();
		switch (gettype($data)) {
			case 'object' :
				if (isset($data->{$locale}))
					return $data->{$locale};
				else if (isset($data->en))
					return $data->en;
				else {
					foreach ($key as $data => $value)
						return $value;
				}
			break;
			
			case 'array' :
				if (isset($data[$locale]))
					return $data[$locale];
				else if (isset($data['en']))
					return $data['en'];
				else {
					foreach ($key as $data => $value)
						return $value;
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
		if (!is_file($filePath) || !is_readable(dirname($filePath))) return;
		
		$fp = fopen($filePath, 'r');
		$content = ''; 
		while(!feof($fp))
			$content .= fgets($fp, 1024);
		return $content;
	}
	
	/**
	 * json_encode와 비슷
	 * 유니코드 값을 \ucXXX 로 치환하지 않으며 json_encode가 구현되지 않은서버에서도 사용할 수 있음
	 */
	function json_encode2($data) {
		switch(gettype($data)) {
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
	 *		   데이터 로딩에 실패할시 NULL 반환
	 */
	function getURLData($url, $userAgent=NULL) {
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
		
		$server_info = json_decode(readFileContent(SERVER_INFO_FILE_PATH));
		foreach($server_info as $_key => $_value) {
			if ($_SERVER['HTTP_HOST'] === $_value->host) {
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
			($serverInfo->protocol . '://' . $serverInfo->host . $serverInfo->uri) :
			PROTOCOL . '://' . $_SERVER['HTTP_HOST'];
	}
	
	/**
	 * config/server-info.json 에서 정의된 session_domain을 반환
	 * config/server-info.json 에서 정의되지 않을 시 현재의 http_host를 반환
	 */
	function getSessionDomain() {
		if (defined('SESSION_DOMAIN')) return SESSION_DOMAIN;
		
		return ($serverInfo = getServerInfo()) ?
			($serverInfo->session_domain) :
			$_SERVER['HTTP_HOST'];
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
	function getUrl($module=NULL, $action=NULL, $queryParam=NULL, $url=NULL) {
		if (!$url) $url = RELATIVE_URL;
		
		$parsedUrl = parse_url($url);
		$queryObj = new StdClass();

		if (isset($parsedUrl['query'])) {
			$tempArr = explode('&', $parsedUrl['query']);

			for ($i=0; $i<count($tempArr); $i++) {
				$tempArr2 = explode('=', $tempArr[$i]);
				if ($tempArr2[0])
					$queryObj->{$tempArr2[0]} = $tempArr2[1];
			}
		}

		if ($queryParam) {
			foreach ($queryParam as $key => $value) {
				if ($key) $queryObj->{$key} = $value;
			}
		}

		if (isset($module)) {
			$queryObj->module = $module;
			if (isset($action))
				$queryObj->action = $action;
		}
		
		$parsedUrl['query'] = arrayToUrlQuery($queryObj);
		return unparse_url($parsedUrl);
	
	}

	/**
	 * getUrl함수에서 @param $module, @param $action이 빠진 하수
	 */
	function getUrlA($queryParam, $url) {
		return getUrl(NULL, NULL, $queryParam, $url);
	}
	

	/**
	 * parse_url()의 역함수
	 * getUrlA 함수에서 쓰임
	 */
	function unparse_url($parsed_url) { 
		$scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : ''; 
		$host     = isset($parsed_url['host']) ? $parsed_url['host'] : ''; 
		$port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : ''; 
		$user     = isset($parsed_url['user']) ? $parsed_url['user'] : ''; 
		$pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : ''; 
		$pass     = ($user || $pass) ? "$pass@" : ''; 
		$path     = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
		$query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : ''; 
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
			foreach($array as $key => $value) {
				if (!$key)
					continue;
				else if ($key && !$value)
					array_push($tempArr, $key);
				else
					array_push($tempArr, $key . '=' . $value);
			}
			return join('&', $tempArr);
		}
	}

	/**
	 * arrayToUrlQuery() 의 역함수
	 * 'param1=val1&param2=val2' 같은 문자열을 array('param1' => 'val1', 'param2' => 'val2') 같은 배열 값으로 변환함
	 */
	function urlQueryToArray($query) {
		$arr = array();
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
			return $_GET['next'];
		else if ($_SERVER['HTTP_REFERER'])
			return $_SERVER['HTTP_REFERER'];
		else
			return RELATIVE_URL;
	}
	
	/**
	 * url 리다이렉트
	 */
	function redirect($url) {
		echo Context::getInstance()->getDoctype() .
				'<html><head>' .
				'<meta http-equiv="refresh" content="0; url='.$url.'">' .
				'<script type="text/javascript">location.replace("'.$url.'")</script>' .
				'</head><body></body></html>';
		exit;
	}
	