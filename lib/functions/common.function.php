<?php
	
	function var_dump2($obj) {
		$bt = debug_backtrace();
		
		echo '<pre class="vdump">';
		echo '<span class="vdump-first-line">var dumped in "' . getFilePathClear($bt[0]['file']) . '" on line ' . $bt[0]['line'] . "</span>\n";
		var_dump($obj);
		echo '</pre>';
	}


	function getContent($moduleID=NULL, $moduleAction=NULL) {
		Context::getInstance()->getModuleContent($moduleID, $moduleAction);
	}

	
	function getFilePathClear($path) {
		return str_replace("\\", '/', str_replace(ROOT_DIR, '', $path));
	}
	
	function set0($str, $length=2) {
		for ($i=0; $i<$length-strlen($str); $i++)
			$str = '0' . $str;
		return $str;
	}
	
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
	
	
	function fetchLocale($object) {
		$locale = getLocale();
		switch (gettype($object)) {
			case 'object' :
				if (isset($object->{$locale}))
					return $object->{$locale};
				else if (isset($object->en))
					return $object->en;
				else {
					foreach ($key as $object => $value)
						return $value;
				}
			break;
			
			case 'array' :
				if (isset($object[$locale]))
					return $object[$locale];
				else if (isset($object['en']))
					return $object['en'];
				else {
					foreach ($key as $object => $value)
						return $value;
				}
			break;
			
			default :
				return $object;
		}
			
	}
	
	function readFileContent($filePath) {
		if (!is_file($filePath) || !is_readable(dirname($filePath))) return;
		
		$fp = fopen($filePath, 'r');
		$content = ''; 
		while(!feof($fp))
			$content .= fgets($fp, 1024);
		return $content;
	}
	
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
	
	function escape($string) {
		return DBHandler::escapeString($string);
	}
	
	function getURLData($url, $userAgent=NULL) {
		$temp = explode('://', $url);
		$temp = explode('/', $temp[1]);
		
		$host = $temp[0];
		$port = strstr($url, 'https://') ? 443 : 80;
		$output = '';
		
		if (!($fp = fsockopen(($port == 443 ? 'ssl://'.$host : $host), $port, &$errno, &$errstr, 30))) return NULL;
		
		fputs($fp,
			"GET ${url} HTTP/1.0\r\n" .
			"Host: ${host}:${port}\r\n" .
			'User-Agent: Mozilla/5.0 (Windows NT 6.2; WOW64) PHP fsocket' . ($userAgent ? ' ' . $userAgent : '') . "\r\n\r\n");
		
		while(!feof($fp))
			$output .= fgets($fp, 1024);    
		
		$output = substr($output, strpos($output, "\r\n\r\n")+4);
		
		fclose($fp);
		return $output; 
	}
	
	function getServerInfo() {
		if (isset($GLOBALS['serverInfo'])) return $GLOBALS['serverInfo'];
		
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
	
	function getRelativeUrl() {
		/**
		 * Get server info in server info file and set relative url
		 * server info file is JSON format
		 */
		if (defined('RELATIVE_URL')) return RELATIVE_URL;
		
		return ($serverInfo = getServerInfo()) ?
			($serverInfo->protocol . '://' . $serverInfo->host . $serverInfo->uri) :
			PROTOCOL . '://' . $_SERVER['HTTP_HOST'];
	}
	
	function getSessionDomain() {
		if (defined('SESSION_DOMAIN')) return SESSION_DOMAIN;
		
		return ($serverInfo = getServerInfo()) ?
			($serverInfo->session_domain) :
			$_SERVER['HTTP_HOST'];
	}
	
	function getUrl($module=NULL, $action=NULL, $queryParam=NULL, $url=NULL) {
		if (!$url) $url = RELATIVE_URL;
		if (isset($module)) {
			if (is_array($queryParam)) $queryParam = arrayToUrlQuery($queryParam);

			return $url .
				('?module=' . $module) .
				(isset($action) ? '&action=' . $action : '') .
				(isset($queryParam) ? '&' . $queryParam : '');
		}else {
			if (is_string($queryParam)) $queryParam = urlQueryToArray($queryParam);

			$parsedUrl = parse_url($url);
			$queryObj = new StdClass();

			if ($parsedUrl['query']) {
				$tempArr = split('&', $parsedUrl['query']);
				for ($i=0; $i<count($tempArr); $i++) {
					$tempArr2 = split('=', $tempArr[$i]);
					$queryObj->{$tempArr2[0]} = $tempArr2[1];
				}
			}
			if ($queryParam) {
				foreach ($queryParam as $key => $value)
					$queryObj->{$key} = $value;
			}
			
			$parsedUrl['query'] = arrayToUrlQuery($queryObj);
			return unparse_url($parsedUrl);
		}
	}

	function getUrlA($queryParam, $url) {
		return getUrl(NULL, NULL, $queryParam, $url);
	}
	
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
	
	function arrayToUrlQuery($array) {
		if (!$array) return NULL;
		else {
			$tempArr = array();
			foreach($array as $key => $value)
				array_push($tempArr, $key . '=' . $value);
			return join('&', $tempArr);
		}
	}
	function urlQueryToArray($query) {
		$arr = array();
		$tempArr = split('&', $query);
		for ($i=0; $i<count($tempArr); $i++) {
			$tempArr2 = split('=', $tempArr[$i]);
			$arr[$tempArr2[0]] = $tempArr2[1];
		}
		return $arr;
	}

	function getBackUrl() {
		if ($_GET['next'])
			return $_GET['next'];
		else if ($_SERVER['HTTP_REFERER'])
			return $_SERVER['HTTP_REFERER'];
		else
			return RELATIVE_URL;
	}
	
	function redirect($url) {
		echo Context::getInstance()->getDoctype() .
				'<html><head>' .
				'<meta http-equiv="refresh" content="0; url='.$url.'">' .
				'<script type="text/javascript">location.replace("'.$url.'")</script>' .
				'</head><body></body></html>';
		exit;
	}
	