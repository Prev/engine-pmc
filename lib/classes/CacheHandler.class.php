<?php
	
	/**
	 * @author prevdev@gmail.com
	 * @2013.05
	 *
	 *
	 * CacheHandler Class
	 * create cache, compile layout, and load cache
	 */
	
	class CacheHandler extends Handler {
		
		static $lessc;
		static $siteCacheDir;

		static public function init() {
			$host = getServerInfo()->host ? getServerInfo()->host : 'none';
			self::$siteCacheDir = '/files/cache/' . $host . urlencode(getServerInfo()->uri);

			if (!is_writable(ROOT_DIR . '/files/')) {
				$message = fetchLocale(array(
					'en' => 'There is no write permission in directory "files".<br>Give write permission to directory "files" like 777.',
					'ko' => '"files" 폴더에 쓰기 권한이 없습니다.<br>"files" 폴더에 777등의 쓰기 권한을 주십시오.'
				));
				echo '<html><head><title>engine pmc</title></head><body><center><h1>Error on page</h1>'.$message.'</center><hr><center>powered by engine pmc</center></body></html>';
				exit;
			}

			if (!is_dir(ROOT_DIR . '/files/')) {
				mkdir(ROOT_DIR . '/files/');
			}
			if (!is_dir(ROOT_DIR . '/files/cache/')) {
				mkdir(ROOT_DIR . '/files/cache/');
			}
			if (!is_dir(ROOT_DIR . self::$siteCacheDir)) {
				mkdir(ROOT_DIR . self::$siteCacheDir);
			}
		}
		
		
		/* template(layout) proccess */
		/**
		 * Execute Layout Cache
		 * if there is no cache, make it first
		 * if cache and origin file is diff, update cache
		 *
		 * @param $filePath is like '/layouts/default/layout.html'
		 */
		static public function execTemplate($filePath, $module=NULL) {
			if (substr($filePath, 0, strlen(ROOT_DIR)) == ROOT_DIR) $filePath = substr($filePath, strlen(ROOT_DIR));
			if (substr($filePath, 0, 1) != '/') $filePath = '/' . $filePath;
			
			if (!is_dir(ROOT_DIR . self::$siteCacheDir . '/layout/'))
				mkdir(ROOT_DIR . self::$siteCacheDir . '/layout/');

			if (!is_dir(ROOT_DIR . self::$siteCacheDir . '/template/'))
				mkdir(ROOT_DIR . self::$siteCacheDir . '/template/');
				
			if (!is_file(ROOT_DIR . $filePath)) {
				Context::printWarning(array(
					'en'=>'Error exec cache - cannot find original file("'.$filePath.'")',
					'ko'=>'캐시 생성 실패 - 원본 파일("'.$filePath.'")을 찾을 수 없음'
				));
				return;
			}
			
			if ($module) 
				$view = $module->view;

			// cache does not exist or cache and original file is diff or DEBUG_MODE
			if (!is_file(self::getTemplateCacheDir($filePath)) ||
				filemtime(self::getTemplateCacheDir($filePath)) < filemtime(ROOT_DIR . $filePath) ||
				DEBUG_MODE ) {

				/* 
				 *	module relative path
				 *	$filePath == /modules/index/template/welcome.html
				 *		=> $relativePath = /modules/index/template/
				 */
				$relativePath = substr($filePath, 0, strrpos($filePath, '/')+1);
				$content = TemplateHandler::compileTemplate(
					file_get_contents(ROOT_DIR . $filePath),
					$module,
					$relativePath
				);
				self::makeTemplateCache(
					$filePath,
					$content
				);
			}

			$__attr = new StdClass();
			$__attr->relativePath = $relativePath;
			$__attr->templateDir = ROOT_DIR . $relativePath;
			$__attr->currentUrl = REAL_URL;

			foreach (Context::$attr as $key => $value)
				$__attr->{$key} = $value;

			if (isset($view)) {
				foreach ($view as $key => $value)
					$__attr->{$key} = $value;
			}
			require self::getTemplateCacheDir($filePath);
		}
		static private function getTemplateCacheDir($originFilePath) {
			$originFilePath = getFilePathClear($originFilePath);
			
			if (strpos($originFilePath, '/layouts') === 0) {
				$originFilePath = substr($originFilePath, strlen('/layouts/'));
				return ROOT_DIR . self::$siteCacheDir . '/layout/' . urlencode($originFilePath) . '.compiled.php';
			}else {
				$originFilePath = substr($originFilePath, strpos($originFilePath, '/modules/') + strlen('/modules/'));
				return ROOT_DIR . self::$siteCacheDir . '/template/' . urlencode($originFilePath) . '.compiled.php';
			}
		}
		static private function makeTemplateCache($filePath, $content) {	
			$cacheFileName = self::getTemplateCacheDir($filePath);
			$fp = fopen($cacheFileName, 'w');
			
			fwrite($fp,  '<?php if (!defined(\'PMC\')) exit; ?>' . "\r\n" . $content);
		}
		
		static $lessRelativePath;
		
		/* lessc proccess */
		static public function getLessCachePath($filePath) {
			if (substr($filePath, 0, strlen(ROOT_DIR)) == ROOT_DIR) $filePath = substr($filePath, strlen(ROOT_DIR));
			if (substr($filePath, 0, 1) != '/') $filePath = '/' . $filePath;

			if (!is_dir(ROOT_DIR . self::$siteCacheDir . '/lessc/'))
				mkdir(ROOT_DIR . self::$siteCacheDir . '/lessc/');

			$cachePath = ROOT_DIR . self::$siteCacheDir . '/lessc/' . md5($filePath) . '.css';
			$originPath = ROOT_DIR . $filePath;
			
			self::$lessRelativePath = substr($filePath, 0, strrpos($filePath, '/')+1);

			if (!is_file($cachePath) ||
				filemtime($cachePath) < filemtime($originPath) ||
				DEBUG_MODE) {

				require_once( ROOT_DIR . '/lib/others/lib.lessc.php' );

				if (!isset(self::$lessc))
					self::$lessc = new lessc();

				$content = file_get_contents($originPath);
				$content = preg_replace_callback('`url\((.*?)\)`', array('CacheHandler', 'parseLessUrl'), $content);
				
				$content = self::$lessc->compile($content);

				$fp = fopen($cachePath, 'w');
				fwrite($fp, $content);
			}
			return substr($cachePath, strpos($cachePath, ROOT_DIR)+strlen(ROOT_DIR));
		}

		static private function parseLessUrl($matches) {
			$url = $matches[1];

			if (strpos($url, '://') !== false || substr($url, 0, 1) == '#' || substr($url, 0, 2) == '//'  || substr($url, 0, 5) == 'data:')
				$url = $url;

			else {
				if (substr($url, 0, 1) == '\'' && substr($url, strlen($url)-1, 1) == '\'') $url = substr($url, 1, strlen($url)-2);
				if (substr($url, 0, 1) == '"' && substr($url, strlen($url)-1, 1) == '"') $url = substr($url, 1, strlen($url)-2);

				if (substr($url, 0, 1) != '/')
					$url = self::$lessRelativePath . $url;

				$url =  '\'' . RELATIVE_URL . $url . '\'';
			}

			return 'url('.$url.')';
		}
		
	}
	