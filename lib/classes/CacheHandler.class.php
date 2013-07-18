<?php
	
	/**
	 * @ author prevdev@gmail.com
	 * @ 2013.05
	 *
	 *
	 * CacheHandler Class
	 * create cache, compile layout, and load cache
	 */
	
	class CacheHandler extends Handler {
		
		static $lessc;
		static $siteCacheDir;

		static public function init() {
			self::$siteCacheDir = '/cache/' . getServerInfo()->host . '_' . substr(md5(getServerInfo()->uri), 0, 8);

			if (!is_dir(ROOT_DIR . '/cache/')) {
				mkdir(ROOT_DIR . '/cache/');
				chmod(ROOT_DIR . '/cache/', 0755);
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
		 * argument '$filePath' is like '/layouts/default/layout.html'
		 */
		static public function execTemplate($filePath, $module=NULL) {
			if (substr($filePath, 0, strlen(ROOT_DIR)) == ROOT_DIR) $filePath = substr($filePath, strlen(ROOT_DIR));
			if (substr($filePath, 0, 1) != '/') $filePath = '/' . $filePath;
				
			if (!is_dir(ROOT_DIR . self::$siteCacheDir . '/layout/'))
				mkdir(ROOT_DIR . self::$siteCacheDir . '/layout/');
				
			if (!is_file(ROOT_DIR . $filePath)) {
				Context::printWarning(array(
					'en'=>'Error exec cache - cannot find original file',
					'kr'=>'캐시 생성 실패 - 원본 파일을 찾을 수 없음'
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
				$relatviePath = substr($filePath, 0, strrpos($filePath, '/')+1);
				$content = TemplateHandler::compileTemplate(
					readFileContent(ROOT_DIR . $filePath),
					$module,
					$relatviePath
				);
				self::makeTemplateCache(
					$filePath,
					$content
				);
			}

			$__attr = new StdClass();
			foreach (Context::$attr as $key => $value)
				$__attr->{$key} = $value;
			if (isset($view)) {
				foreach ($view as $key => $value)
					$__attr->{$key} = $value;
			}
			require self::getTemplateCacheDir($filePath);
		}
		static private function getTemplateCacheDir($originFilePath) {
			return ROOT_DIR . self::$siteCacheDir . '/layout/' . md5($originFilePath) . '.compiled.php';
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
			$relatviePath = substr($filePath, 0, strrpos($filePath, '/')+1);

			if (!is_file($cachePath) ||
				filemtime($cachePath) < filemtime($originPath) ||
				DEBUG_MODE) {

				if (!isset(self::$lessc))
					self::$lessc = new lessc();

				$content = readFilecontent($originPath);
				$content = preg_replace('`url\(/(.*)\)`', 'url(' . RELATIVE_URL . '/$1)', $content);
				$content = preg_replace('`url\((.*)\)`', 'url(' . RELATIVE_URL . $relatviePath . '$1)', $content);
				
				$content = self::$lessc->compile($content);

				$fp = fopen($cachePath, 'w');
				fwrite($fp, $content);
			}
			return substr($cachePath, strpos($cachePath, ROOT_DIR)+strlen(ROOT_DIR));
		}


		/* menu proccess */
		static public function getMenuCachePath($menuData, $level) {
			if (!is_file(self::getMenuCacheDir($level)))
				self::makeMenuCache($menuData, $level);
			
			$fp = fopen(self::getMenuCacheDir($level), 'r');
			$firstLine = fgets($fp, 1024);
			
			// menu hash
			$tmp = explode('/*H:', $firstLine);
			$tmp = explode('*/', $tmp[1]);
			
			// compare hash and json encoded data
			if ($tmp[0] != md5(json_encode2($menuData)))
				self::makeMenuCache($menuData, $level);
			
			return self::$siteCacheDir . '/menu/menu' . $level . '.css';
		}
		static private function getMenuCacheDir($level) {
			return ROOT_DIR . self::$siteCacheDir . '/menu/menu' . $level . '.css';
		}
		static private function makeMenuCache($menuData, $level) {
			if (!is_dir(ROOT_DIR . self::$siteCacheDir . '/menu/')) {
				mkdir(ROOT_DIR . self::$siteCacheDir . '/menu/');
			}
			
			$str = '@charset "utf-8";' . "\r\n\r\n";
			for ($i=0; $i<count($menuData); $i++) {
				$n = '.pmc-menu' . $level . '-' . $menuData[$i]->title;
				
				$str .= $n . '{'.$menuData[$i]->css_property.'}' . "\r\n";
				$str .= $n . ':hover{'.$menuData[$i]->css_hover_property.'}' . "\r\n";
				$str .= $n . ':active{'.$menuData[$i]->css_active_property.'}' . "\r\n";
			}
			
			$dataHash = '/*H:' . md5(json_encode2($menuData)) . '*/';
			
			$fp = fopen(self::getMenuCacheDir($level), 'w');
			fwrite($fp,  $dataHash . "\r\n" . $str);
		}
		
	}
	