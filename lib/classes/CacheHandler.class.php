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
		static $rPath; // module relative path
		
		static public function init() {
			if (!is_dir(ROOT_DIR . '/cache/')) {
				mkdir(ROOT_DIR . '/cache/');
				chmod(ROOT_DIR . '/cache/', 0755);
			}
		}
		
		static public function compileLayout($html) {
			// import css/js... header file
			$html = preg_replace_callback('/\#? ?<import([^>]+)>/', array('CacheHandler', 'compileLayout_imports'), $html);
			
			// {# Locale code }
			// ex) {# 'en'=>'Error on this page', 'kr'=> '이 페이지에 오류가 있습니다' }
			$html = preg_replace('/{#([\s\S]+?)}/', '{@ echo fetchLocale(array($1)); }', $html, -1);
			
			// {@ PHPCode }
			$html = preg_replace_callback('/{@([\s\S]+?)}/', array('CacheHandler', 'compileLayout_parseCode'), $html);
			
			// {$a} -> echo  $__attr->a ( Context::get('a') or View->a )
			$html = preg_replace('/{\$([\>a-zA-Z0-9_-]*)}/', '<?php echo \$__attr->$1; ?>', $html, -1);
			
			// {func()} -> Context::execFunction('func', array())
			$html = preg_replace_callback("`{([a-zA-Z0-9_\s]+)\((.*)\)}`", array('CacheHandler', 'compileLayout_parseFunc'), $html);
			
			// insert RELATIVE_URL in absolute src (/.*), href and action
			$html = preg_replace("`src=\"/(.*)\"`i", 'src="' . RELATIVE_URL . '/$1"', $html, -1);
			$html = preg_replace("`href=\"/(.*)\"`i", 'href="' . RELATIVE_URL . '/$1"', $html, -1);
			$html = preg_replace("`action=\"/(.*)\"`i", 'action="' . RELATIVE_URL . '/$1"', $html, -1);
			
			
			// insert module realitve url in relative src (./.*), href and action
			$html = preg_replace("`src=\"\./(.*)\"`i", 'src="' . RELATIVE_URL . self::$rPath . '$1"', $html, -1);
			$html = preg_replace("`href=\"\./(.*)\"`i", 'href="' . RELATIVE_URL . self::$rPath . '$1"', $html, -1);
			$html = preg_replace("`action=\"\./(.*)\"`i", 'action="' . RELATIVE_URL . self::$rPath . '$1"', $html, -1);
			
			
			/*$html = preg_replace('/<condition\s+do="([^"]+)"\s*>/', '<?php if($1) { ?>', $html);*/
			$html = preg_replace_callback('/<condition\s+do="([^"]+)"\s*>/', array('CacheHandler', 'compileLayout_parseConditions'), $html);
			$html = str_replace('</condition>', '<?php } ?>', $html);
			
			$html = join('$_SERVER', explode('$__attr->_SERVER', $html));
			$html = join('$_COOKIE', explode('$__attr->_COOKIE', $html));
			$html = join('$GLOBALS', explode('$__attr->GLOBALS', $html));
			$html = join('$_GET', explode('$__attr->_GET', $html));
			$html = join('$_POST', explode('$__attr->_POST', $html));
			$html = join('$_REQUEST', explode('$__attr->_REQUEST', $html));
			
			if (ZIP_BLANK) $html = self::deleteWhiteSpace($html);
			return $html;
		}
		
		static private function compileLayout_imports($vals) {
			if (substr($vals[0], 0, 1) == '#') return;
			
			preg_match_all('/([a-zA-Z0-9]+)="([^"]+)"/', $vals[1], $output);
			
			if (count($output) !== 3) {
				Context::printWarning(array(
					'en'=>'Complie layout error - vals length error',
					'kr'=>'레이아웃 컴파일 에러 - 정규식 길이가 올바르지 않음'
				));
				return;
			}
			
			$keys = $output[1];
			$values = $output[2];
			$importVals = new StdClass();
			
			for ($i=0; $i<count($keys); $i++)
				$importVals->{$keys[$i]} = $values[$i];
			
			if (!$importVals->path) {
				return '<!-- Error loading imports ->';
			}
			
			if (substr($importVals->path, 0, 1) == '/')
				$absolutePath = $importVals->path;
			else if (substr($importVals->path, 0, 2) == './')
				$absolutePath = self::$rPath . substr($importVals->path, 2);
			else
				$absolutePath = self::$rPath . $importVals->path;
			
			
			return '<?php Context::getInstance()->addHeaderFile(' .
				'\'' . $absolutePath . '\', ' .
				(isset($importVals->index) ? $importVals->index : -1) . ', ' .
				'\'' . (isset($importVals->position) ? $importVals->position : 'head') . '\', ' .
				'' . (isset($importVals->targetie) ? '\''.$importVals->targetie.'\'' : 'NULL') . '' .
			'); ?>';
			
		}
		static private function compileLayout_parseCode($m) {
			if (!$m[1]) return;
			
			$c = $m[1];
			$c = preg_replace('/\$([\>a-zA-Z0-9_-]*)/', '\$__attr->$1', $c, -1);
			$c = preg_replace('/\${([\>a-zA-Z0-9_-]*)}/', '\${__attr->$1}', $c, -1);
			
			if (substr($c, 0, 1) != ' ') $c = ' ' . $c;
			if (substr($c, strlen($c)+1, 1) != ' ')	$c .= '';
				
			return '<?php' . $c . '?>';
		}
		
		static private function compileLayout_parseConditions($m) {
			if (!$m[1]) return;
			
			$c = $m[1];
			$c = preg_replace('/\$([\>a-zA-Z0-9_-]*)/', '\$__attr->$1', $c, -1);
			$c = preg_replace('/\${([\>a-zA-Z0-9_-]*)}/', '\${__attr->$1}', $c, -1);
				
			return '<?php if('.$c.') { ?>';
		}
		
		static private function compileLayout_parseFunc($m) {
			if (!$m[1]) return;
			
			$function = $m[1];
			$args = $m[2];
			$args = preg_replace('/\$([\>a-zA-Z0-9_-]*)/', '\$__attr->$1', $args, -1);
			$args = preg_replace('/\${([\>a-zA-Z0-9_-]*)}/', '\${__attr->$1}', $args, -1);

			if (function_exists($function))
				return '<?php if ($func = '.$function.'('.$args.')) echo $func; ?>';

			else if (self::$module && method_exists(self::$module, $function))
				return '<?php if ($func = ModuleHandler::getModule(\''.self::$module->moduleID.'\')->'.$function.'('.$args.')) echo $func; ?>';
			
			else if (self::$module && self::$module->view && method_exists(self::$module->view, $function)) {
				return '<?php if ($func = ModuleHandler::getModule(\''.self::$module->moduleID.'\')->view->'.$function.'('.$args.')) echo $func; ?>';
			}
		}
		


		static private function deleteWhiteSpace($content) {
			if (strpos($content, "> ") !== false) {
				$content = join('>', explode("> ", $content));
				return self::deleteWhiteSpace($content);
			}
			if (strpos($content, ">\t") !== false) {
				$content = join('>', explode(">\t", $content));
				return self::deleteWhiteSpace($content);
			}
			if (strpos($content, ">\r\n") !== false) {
				$content = join('>', explode(">\r\n", $content));
				return self::deleteWhiteSpace($content);
			}
			
			if (strpos($content, " <") !== false) {
				$content = join('<', explode(" <", $content));
				return self::deleteWhiteSpace($content);
			}
			if (strpos($content, "\t<") !== false) {
				$content = join('<', explode("\t<", $content));
				return self::deleteWhiteSpace($content);
			}
			if (strpos($content, "\r\n<") !== false) {
				$content = join('<', explode("\r\n<", $content));
				return self::deleteWhiteSpace($content);
			}
			
			
			return $content;
		}
		
		
		static private function getOriginFileTime($filePath) {
			$content = readFileContent(self::getLayoutCacheDir($filePath));
			
			$tmp = explode('/*T:', $content);
			$tmp = explode('*/', $tmp[1]);
			return $tmp[0];
		}
		
		
		static private function getLayoutCacheDir($originFilePath) {
			return ROOT_DIR . '/cache/layout/' . md5($originFilePath) . '.compiled.php';
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
		static public function execLayout($filePath, $module=NULL) {
			if (substr($filePath, 0, strlen(ROOT_DIR)) == ROOT_DIR)
				$filePath = substr($filePath, strlen(ROOT_DIR));
			if (substr($filePath, 0, 1) != '/')
				$filePath = '/' . $filePath;
				
			if (!is_dir(ROOT_DIR . '/cache/layout/')) {
				mkdir(ROOT_DIR . '/cache/layout/');
			}
				
			if (!is_file(ROOT_DIR . $filePath)) {
				Context::printWarning(array(
					'en'=>'Error exec cache - cannot find original file',
					'kr'=>'캐시 생성 실패 - 원본 파일을 찾을 수 없음'
				));
				return;
			}
			
			self::$module = $module;
			$view = $module->view;

			// cache does not exist or cache and original file is diff or DEBUG_MODE
			if (!is_file(self::getLayoutCacheDir($filePath)) ||
				self::getOriginFileTime($filePath) != filemtime(ROOT_DIR . $filePath) ||
				DEBUG_MODE ) {
				
				self::$rPath = substr($filePath, 0, strrpos($filePath, '/')+1); //module relative path
				self::makeLayoutCache(
					$filePath,
					self::compileLayout( readFileContent(ROOT_DIR . $filePath) ),
					filemtime(ROOT_DIR . $filePath)
				);
			}

			$__attr = new StdClass();
			foreach (Context::$attr as $key => $value)
				$__attr->{$key} = $value;
			if ($view) {
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
			
			return '/cache/menu/menu' . $level . '.css';
		}
		
		static private function getMenuCacheDir($level) {
			return ROOT_DIR . '/cache/menu/menu' . $level . '.css';
		}
		
		static private function makeMenuCache($menuData, $level) {
			if (!is_dir(ROOT_DIR . '/cache/menu/')) {
				mkdir(ROOT_DIR . '/cache/menu/');
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
	