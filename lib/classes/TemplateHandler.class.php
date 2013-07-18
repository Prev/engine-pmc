<?php
	
	/**
	 * @ author prevdev@gmail.com
	 * @ 2013.05
	 *
	 *
	 * TemplateHandler Class
	 * complie template
	 */
	
	class TemplateHandler extends Handler {
		
		private $module;
		private $relativePath;
		private static $instance;


		public static function compileTemplate($html, $module=NULL, $relativePath='/') {
			if (!self::$instance) self::$instance = new TemplateHandler();
			return self::$instance->_compileTemplate($html, $module, $relativePath);
		}
		private function _compileTemplate($html, $module=NULL, $relativePath='/') {
			$this->module = $module;
			$this->relativePath = $relativePath;

			// import css/js... header file
			$html = preg_replace_callback('/\#? ?<import([^>]+)>/', array($this, 'handleImportTags'), $html);
			
			// {# Locale code }
			// ex) {# 'en'=>'Error on this page', 'kr'=> '이 페이지에 오류가 있습니다' }
			$html = preg_replace('/{#([\s\S]+?)}/', '{@ echo fetchLocale(array($1)); }', $html);
			
			// {@ PHPCode }
			$html = preg_replace_callback('/{@([\s\S]+?)}/', array($this, 'parseCode'), $html);
			
			// {$a} -> echo  $__attr->a ( Context::get('a') or View->a )
			$html = preg_replace('/{\$([\>a-zA-Z0-9_-]*)}/', '<?php echo \$__attr->$1; ?>', $html);
			
			// {func()} -> Context::execFunction('func', array())
			$html = preg_replace_callback("`{([a-zA-Z0-9_\s]+)\((.*)\)}`", array($this, 'parseFunc'), $html);
			
			// insert RELATIVE_URL in absolute src (/.*), href and action
			$html = preg_replace("`src=\"/(.*)\"`i", 'src="' . RELATIVE_URL . '/$1"', $html);
			$html = preg_replace("`href=\"/(.*)\"`i", 'href="' . RELATIVE_URL . '/$1"', $html);
			$html = preg_replace("`action=\"/(.*)\"`i", 'action="' . RELATIVE_URL . '/$1"', $html);
			
			
			// insert module realitve url in relative src (./.*), href and action
			$html = preg_replace("`src=\"\./(.*)\"`i", 'src="' . RELATIVE_URL . $this->relativePath . '$1"', $html);
			$html = preg_replace("`href=\"\./(.*)\"`i", 'href="' . RELATIVE_URL . $this->relativePath . '$1"', $html);
			$html = preg_replace("`action=\"\./(.*)\"`i", 'action="' . RELATIVE_URL . $this->relativePath . '$1"', $html);
			
			// targetie condition
			$html = preg_replace('`<condition\s+targetie="([^"]+)"\s*>([\s\S]*?)</condition>`', '<!--[if $1]>$2<![endif]-->', $html);

			// condition
			$html = preg_replace_callback('/<condition\s+do="([^"]+)"\s*>/', array($this, 'parseConditions'), $html);
			$html = str_replace('</condition>', '<?php } ?>', $html);
			
			$html = join('$_SERVER', explode('$__attr->_SERVER', $html));
			$html = join('$_COOKIE', explode('$__attr->_COOKIE', $html));
			$html = join('$GLOBALS', explode('$__attr->GLOBALS', $html));
			$html = join('$_GET', explode('$__attr->_GET', $html));
			$html = join('$_POST', explode('$__attr->_POST', $html));
			$html = join('$_REQUEST', explode('$__attr->_REQUEST', $html));
			
			if (ZIP_BLANK) $html = $this->deleteWhiteSpace($html);
			return $html;
		}
		
		private function handleImportTags($vals) {
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
				$absolutePath = $this->relativePath . substr($importVals->path, 2);
			else
				$absolutePath = $this->relativePath . $importVals->path;
			
			
			return '<?php Context::getInstance()->addHeaderFile(' .
				'\'' . $absolutePath . '\', ' .
				(isset($importVals->index) ? $importVals->index : -1) . ', ' .
				'\'' . (isset($importVals->position) ? $importVals->position : 'head') . '\', ' .
				'' . (isset($importVals->requiredAgent) ? '\''.$importVals->requiredAgent.'\'' : 'NULL') . ',' .
				'' . (isset($importVals->targetie) ? '\''.$importVals->targetie.'\'' : 'NULL') . '' .
			'); ?>';
			
		}
		private function parseCode($m) {
			if (!$m[1]) return;
			
			$c = $m[1];
			$c = preg_replace('/\$([\>a-zA-Z0-9_-]*)/', '\$__attr->$1', $c, -1);
			$c = preg_replace('/\${([\>a-zA-Z0-9_-]*)}/', '\${__attr->$1}', $c, -1);
			
			if (substr($c, 0, 1) != ' ') $c = ' ' . $c;
			if (substr($c, strlen($c)+1, 1) != ' ')	$c .= '';
				
			return '<?php' . $c . '?>';
		}
		
		private function parseConditions($m) {
			if (!$m[1]) return;
			
			$c = $m[1];
			$c = preg_replace('/\$([\>a-zA-Z0-9_-]*)/', '\$__attr->$1', $c, -1);
			$c = preg_replace('/\${([\>a-zA-Z0-9_-]*)}/', '\${__attr->$1}', $c, -1);
				
			return '<?php if('.$c.') { ?>';
		}
		
		private function parseFunc($m) {
			if (!$m[1]) return;
			
			$function = $m[1];
			$args = $m[2];
			$args = preg_replace('/\$([\>a-zA-Z0-9_-]*)/', '\$__attr->$1', $args, -1);
			$args = preg_replace('/\${([\>a-zA-Z0-9_-]*)}/', '\${__attr->$1}', $args, -1);

			if (function_exists($function))
				return '<?php if ($func = '.$function.'('.$args.')) echo $func; ?>';

			else if ($this->module && method_exists($this->module, $function))
				return '<?php if ($func = ModuleHandler::getModule(\''.$this->module->moduleID.'\')->'.$function.'('.$args.')) echo $func; ?>';
			
			else if ($this->module && $this->module->view && method_exists($this->module->view, $function)) {
				return '<?php if ($func = ModuleHandler::getModule(\''.$this->module->moduleID.'\')->view->'.$function.'('.$args.')) echo $func; ?>';
			}
		}
		

		private function deleteWhiteSpace($content) {
			if (strpos($content, "> ") !== false) {
				$content = join('>', explode("> ", $content));
				return $this->deleteWhiteSpace($content);
			}
			if (strpos($content, ">\t") !== false) {
				$content = join('>', explode(">\t", $content));
				return $this->deleteWhiteSpace($content);
			}
			if (strpos($content, ">\r\n") !== false) {
				$content = join('>', explode(">\r\n", $content));
				return $this->deleteWhiteSpace($content);
			}
			
			if (strpos($content, " <") !== false) {
				$content = join('<', explode(" <", $content));
				return $this->deleteWhiteSpace($content);
			}
			if (strpos($content, "\t<") !== false) {
				$content = join('<', explode("\t<", $content));
				return $this->deleteWhiteSpace($content);
			}
			if (strpos($content, "\r\n<") !== false) {
				$content = join('<', explode("\r\n<", $content));
				return $this->deleteWhiteSpace($content);
			}
			
			
			return $content;
		}

	}