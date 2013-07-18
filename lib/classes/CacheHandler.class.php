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
		
		static $module;
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
		
		
		static private function getOriginFileTime($filePath) {
			$content = readFileContent(self::getLayoutCacheDir($filePath));
			
			$tmp = explode('/*T:', $content);
			$tmp = explode('*/', $tmp[1]);
			return $tmp[0];
		}
		
		static private function getLayoutCacheDir($originFilePath) {
			return ROOT_DIR . self::$siteCacheDir . '/layout/' . md5($originFilePath) . '.compiled.php';
		}
		
		static private function makeLayoutCache($filePath, $content, $fileTime) {	
			$cacheFileName = self::getLayoutCacheDir($filePath);
			$fp = fopen($cacheFileName, 'w');
			
			fwrite($fp,  '<?php if (!defined(\'PMC\')) exit; /*T:'.$fileTime.'*/ ?>' . "\r\n" . $content);
		}
		
		/**
		 * Execute Layout Cache
		 * if there is no cache, make it first
		 * if cache and origin file is diff, update cache
		 *
		 * argument '$filePath' is like '/layouts/default/layout.html'
		 */
		static public function execTemplate($filePath, $module=NULL) {
			if (substr($filePath, 0, strlen(ROOT_DIR)) == ROOT_DIR)
				$filePath = substr($filePath, strlen(ROOT_DIR));
			if (substr($filePath, 0, 1) != '/')
				$filePath = '/' . $filePath;
				
			if (!is_dir(ROOT_DIR . self::$siteCacheDir . '/layout/')) {
				mkdir(ROOT_DIR . self::$siteCacheDir . '/layout/');
			}
				
			if (!is_file(ROOT_DIR . $filePath)) {
				Context::printWarning(array(
					'en'=>'Error exec cache - cannot find original file',
					'kr'=>'캐시 생성 실패 - 원본 파일을 찾을 수 없음'
				));
				return;
			}
			
			if ($module) {
				self::$module = $module;
				$view = $module->view;
			}

			// cache does not exist or cache and original file is diff or DEBUG_MODE
			if (!is_file(self::getLayoutCacheDir($filePath)) ||
				self::getOriginFileTime($filePath) != filemtime(ROOT_DIR . $filePath) ||
				DEBUG_MODE ) {
				
				$relatviePath = substr($filePath, 0, strrpos($filePath, '/')+1); //module relative path
				$content = TemplateHandler::compileTemplate(
					readFileContent(ROOT_DIR . $filePath),
					$moudle,
				$relatviePath);

				self::makeLayoutCache(
					$filePath,
					$content,
					filemtime(ROOT_DIR . $filePath)
				);
			}

			$__attr = new StdClass();
			foreach (Context::$attr as $key => $value)
				$__attr->{$key} = $value;
			if (isset($view)) {
				foreach ($view as $key => $value)
					$__attr->{$key} = $value;
			}
			require self::getLayoutCacheDir($filePath);
		}
		
		
		
		static public function getMenuCachePath($menuData, $level) {
			if (!is_file(self::getMenuCacheDir($level)))
				self::makeMenuCache($menuData, $level);
			
			$fp = fopen(self::getMenuCacheDir($level), 'r');
			$firstLine = fgets($fp, 1024);
			
			$tmp = explode('/*H:', $firstLine);
			$tmp = explode('*/', $tmp[1]);
			
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
	