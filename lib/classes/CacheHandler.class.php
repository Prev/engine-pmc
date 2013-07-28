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
			self::$siteCacheDir = '/files/cache/' . getServerInfo()->host . urlencode(getServerInfo()->uri);

			if (!is_dir(ROOT_DIR . '/files/')) {
				mkdir(ROOT_DIR . '/files/');
				chmod(ROOT_DIR . '/files/', 0755);
			}
			if (!is_dir(ROOT_DIR . '/files/cache/')) {
				mkdir(ROOT_DIR . '/files/cache/');
				chmod(ROOT_DIR . '/files/cache/', 0755);
			}
			if (!is_dir(ROOT_DIR . self::$siteCacheDir)) {
				mkdir(ROOT_DIR . self::$siteCacheDir);
				chmod(ROOT_DIR . self::$siteCacheDir, 0755);
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
				
			if (!is_file(ROOT_DIR . $filePath)) {
				Context::printWarning(array(
					'en'=>'Error exec cache - cannot find original file("'.$filePath.'"")',
					'kr'=>'캐시 생성 실패 - 원본 파일("'.$filePath.'"")을 찾을 수 없음'
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
					readFileContent(ROOT_DIR . $filePath),
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
			return ROOT_DIR . self::$siteCacheDir . '/layout/' . urlencode($originFilePath) . '.compiled.php';
			//return ROOT_DIR . self::$siteCacheDir . '/layout/' . md5($originFilePath) . '.compiled.php';
		}
		static private function makeTemplateCache($filePath, $content) {	
			$cacheFileName = self::getTemplateCacheDir($filePath);
			$fp = fopen($cacheFileName, 'w');
			
			fwrite($fp,  '<?php if (!defined(\'PMC\')) exit; ?>' . "\r\n" . $content);
		}
		
		
		/* lessc proccess */
		static public function getLessCachePath($filePath) {
			require_once( ROOT_DIR . '/lib/others/lib.lessc.php' );
			
			if (substr($filePath, 0, strlen(ROOT_DIR)) == ROOT_DIR) $filePath = substr($filePath, strlen(ROOT_DIR));
			if (substr($filePath, 0, 1) != '/') $filePath = '/' . $filePath;

			if (!is_dir(ROOT_DIR . self::$siteCacheDir . '/lessc/'))
				mkdir(ROOT_DIR . self::$siteCacheDir . '/lessc/');

			$cachePath = ROOT_DIR . self::$siteCacheDir . '/lessc/' . md5($filePath) . '.css';
			$originPath = ROOT_DIR . $filePath;
			$relativePath = substr($filePath, 0, strrpos($filePath, '/')+1);

			if (!is_file($cachePath) ||
				filemtime($cachePath) < filemtime($originPath) ||
				DEBUG_MODE) {

				if (!isset(self::$lessc))
					self::$lessc = new lessc();

				$content = readFilecontent($originPath);
				$content = preg_replace('`url\(/(.*)\)`', 'url(' . RELATIVE_URL . '/$1)', $content);
				$content = preg_replace('`url\((.*)\)`', 'url(' . RELATIVE_URL . $relativePath . '$1)', $content);
				
				$content = self::$lessc->compile($content);

				$fp = fopen($cachePath, 'w');
				fwrite($fp, $content);
			}
			return substr($cachePath, strpos($cachePath, ROOT_DIR)+strlen(ROOT_DIR));
		}
		
	}
	