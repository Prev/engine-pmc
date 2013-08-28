<?php
	
	/**
	 * @author prevdev@gmail.com
	 * @2013.05 - 08
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
			
			// comment ignore
			$html = preg_replace('`\n(\t)*//(.*)`', ' ', $html);
			$html = preg_replace('`/\*([\s\S]*?)\*/`', '', $html);

			
			// import css/js... header file
			$html = preg_replace_callback('/\#?\s?<import([^>]+)>/', array($this, 'handleImportTags'), $html);
			
			// import meta tag
			$html = preg_replace('/\#?\s?<meta([^>]+)>/', '<?php Context::getInstance()->addMetaTag(\'<meta$1>\'); ?>', $html);
			
			// <title>title</title>
			$html = preg_replace_callback('`<title>(.*?)</title>`', array($this, 'parseTitle'), $html);


			// {# Locale code }
			// ex) {# 'en'=>'Error on this page', 'kr'=> '이 페이지에 오류가 있습니다' }
			$html = preg_replace('/{#([\s\S]+?)}/', '{@ echo fetchLocale(array($1)); }', $html);
			
			// {@ PHPCode }
			$html = preg_replace_callback('/{@([\s\S]+?)}/', array($this, 'parseCode'), $html);
			
			/*
			 * {$a} -> <?php echo $__attr->a ( Context::get('a') or View->a ) ?> 
			 * {&a} -> $__attr->a ( Context::get('a') or View->a )
			 */
			$html = preg_replace_callback('/{\s*(\$[^}]+?)}/', array($this, 'parseVar'), $html);
			//$html = preg_replace_callback('/{\s*(\$|&)([^}]+?)}/', array($this, 'parseVar'), $html);
			
			// {func()} -> Context::execFunction('func', array())
			$html = preg_replace_callback("`{\s*([a-zA-Z0-9_\s]+)\((.*?)\)}`", array($this, 'parseFunc'), $html);
			

			// insert RELATIVE_URL in absolute src (/.*), href and action
			// insert module realitve url in upper relative src (../.*), href and action
			// insert module realitve url in relative src (./.*), href and action
			$html = preg_replace_callback('`(src|href|action)="(.*?)"`i', array($this, 'parseUrl'), $html);
			
			// targetie condition
			$html = preg_replace('`<condition\s+targetie\s*=\s*"([^"]+)"\s*>([\s\S]*?)</condition>`i', '<!--[if $1]>$2<![endif]-->', $html);
			
			$count = 0;
			
			while (preg_match('/<condition\s+do\s*=\s*"([^"]+)"\s*>\s+<true>\s+([\s\S]*?)<\/true>\s+<false>([\s\S]*?)<\/false>\s+<\/condition>/i', $html)) {
				$html = preg_replace_callback('/<condition\s+do\s*=\s*"([^"]+)"\s*>\s+<true>\s+([\s\S]*?)<\/true>\s+<false>([\s\S]*?)<\/false>\s+<\/condition>/i', array($this, 'parseConditions'), $html);
				if (++$count > 30) break;
			}
			while (preg_match('/<condition\s+do\s*=\s*"([^"]+)"\s*>([\s\S]*?)<else>([\s\S]*?)<\/condition>/i', $html)) {
				$html = preg_replace_callback('/<condition\s+do\s*=\s*"([^"]+)"\s*>([\s\S]*?)<else>([\s\S]*?)<\/condition>/i', array($this, 'parseConditions'), $html);
				if (++$count > 30) break;
			}
			while (preg_match('/<condition\s+do\s*=\s*"([^"]+)"\s*>([\s\S]*?)<\/condition>/i', $html)) {
				$html = preg_replace_callback('/<condition\s+do\s*=\s*"([^"]+)"\s*>([\s\S]*?)<\/condition>/i', array($this, 'parseConditions'), $html);
				if (++$count > 30) break;
			}
			while (preg_match('/<condition\s+do\s*=\s*"([^"]+)"\s*>/i', $html)) {
				$html = preg_replace_callback('/<condition\s+do\s*=\s*"([^"]+)"\s*>/i', array($this, 'parseConditions'), $html);
				if (++$count > 30) break;
			}
			$html = preg_replace('/<\/condition>/', '<?php } ?>', $html);

			
			// switch tag
			$html = preg_replace_callback('/<switch\s+var="([^"]+)"\s*>([\s\S]*?)<\/switch>/i', array($this, 'parseSwitches'), $html);
			
			// <link>http://google.com</link> -> <a href="http://google.com">http://google.com</a>
			$html = preg_replace('`<link(.*?)>(.*?)</link>`', '<a href="$2"$1>$2</a>', $html);

			
			$html = join('$_SERVER', explode('$__attr->_SERVER', $html));
			$html = join('$_COOKIE', explode('$__attr->_COOKIE', $html));
			$html = join('$GLOBALS', explode('$__attr->GLOBALS', $html));
			$html = join('$_GET', explode('$__attr->_GET', $html));
			$html = join('$_POST', explode('$__attr->_POST', $html));
			$html = join('$_REQUEST', explode('$__attr->_REQUEST', $html));
			
			$html = preg_replace('/([a-zA-Z0-9_])::\$__attr->(.*)/', '$1::\$$2', $html);
			
			//$html = join('::$', explode('::$__attr->', $html));

			if (ZIP_BLANK) $html = $this->deleteWhiteSpace($html);
			return $html;
		}
		
		private function parseUrl($matches) {
			$url = $matches[2];
			
			if (strpos($url, '://') !== false || substr($url, 0, 1) == '#' || substr($url, 0, 7) == 'mailto:' || substr($url, 0, 5) == '<?php')
				$url = $matches[2];
			else if (substr($url, 0, 1) == '/')
				$url = RELATIVE_URL . $matches[2];
			else if (substr($url, 0, 3) == '../')
				$url = RELATIVE_URL . getUpperPath($this->relativePath) . substr($matches[2], 3);
			else if (substr($url, 0, 2) == './')
				$url = RELATIVE_URL . $this->relativePath . substr($matches[2], 2);
			else
				$url = RELATIVE_URL . $this->relativePath . $matches[2];

			return $matches[1] . '="' . $url . '"';
		}

		private function handleImportTags($matches) {
			if (substr($matches[0], 0, 1) == '#') return;
			
			preg_match_all('/([a-zA-Z0-9]+)="([^"]+)"/', $matches[1], $output);
			
			if (count($output) !== 3) {
				Context::printWarning(array(
					'en'=>'Complie layout error - matches length error',
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
				return '<!-- Error loading imports -->';
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

		private function parseTitle($matches) {
			$matches[1] = preg_replace('/{\$(.*?)}/', '\'.\$__attr->$1.\'', $matches[1]);
			$matches[1] = preg_replace('/{#(.*?)}/', '\'.fetchLocale(array($1)).\'', $matches[1]);

			return '<?php Context::getInstance()->setTitle(\''.$matches[1].'\'); ?>';
		}

		private function parseVar($matches) {
			$varname = $matches[1];
			$varname = preg_replace('/\$([\>a-zA-Z0-9_-]*)/', '\$__attr->$1', $varname, -1);
			$varname = preg_replace('/&([\>a-zA-Z0-9_-]*)/', '\$__attr->$1', $varname, -1);

			return '<?php echo ' . $varname . '; ?>';
		}

		private function parseFunc($matches) {
			if (!$matches[1]) return;
			
			$function = $matches[1];
			$args = $matches[2];
			$args = preg_replace('/\$([\>a-zA-Z0-9_-]*)/', '\$__attr->$1', $args, -1);
			$args = preg_replace('/\${([\>a-zA-Z0-9_-]*)}/', '\${__attr->$1}', $args, -1);

			if (function_exists($function))
				$func = $function.'('.$args.')';
				
			else if ($this->module && method_exists($this->module, $function))
				$func = 'ModuleHandler::getModule(\''.$this->module->moduleID.'\', \''.$this->module->action.'\')->'.$function.'('.$args.')';
				
			foreach (array('model', 'controller', 'view') as $key => $mvc) {
				if ($this->module && $this->module->{$mvc} && method_exists($this->module->{$mvc}, $function)) {
					$func = 'ModuleHandler::getModule(\''.$this->module->moduleID.'\', \''.$this->module->action.'\')->'.$mvc.'->'.$function.'('.$args.')';
					break;
				}
			}

			return '<?php $func=' . $func . '; if (isset($func)) echo $func; ?>';
		}

		private function parseCode($matches) {
			if (!$matches[1]) return;
			
			$c = $matches[1];
			$c = preg_replace('/([^:>])\$([\>a-zA-Z0-9_-]*)/', '$1\$__attr->$2', $c);
			$c = preg_replace('/([^:>])\${([\>a-zA-Z0-9_-]*)}/', '$1\${__attr->$2}', $c);

			$c = preg_replace_callback('/(\w+)\(/', array($this, 'parseFunc2'), $c);

			if (substr($c, 0, 1) != ' ') $c = ' ' . $c;
			if (substr($c, strlen($c)+1, 1) != ' ')	$c .= '';
				
			return '<?php' . $c . '?>';
		}

		private function parseFunc2($matches) {
			if (!$matches[1]) return;
			
			$function = $matches[1];

			if ($this->module && method_exists($this->module, $function))
				$func = 'ModuleHandler::getModule(\''.$this->module->moduleID.'\', \''.$this->module->action.'\')->'.$function;
				
			foreach (array('model', 'controller', 'view') as $key => $mvc) {
				if ($this->module && $this->module->{$mvc} && method_exists($this->module->{$mvc}, $function)) {
					$func = 'ModuleHandler::getModule(\''.$this->module->moduleID.'\', \''.$this->module->action.'\')->'.$mvc.'->'.$function;
					break;
				}
			}
			if (!$func)
				$func = $function;

			return $func . '(';
		}
		
		private function parseConditions($matches) {
			if (!$matches[1]) return;
			
			$c = $matches[1];
			$c = preg_replace('/\$([\>a-zA-Z0-9_-]*)/', '\$__attr->$1', $c, -1);
			$c = preg_replace('/\${([\>a-zA-Z0-9_-]*)}/', '\${__attr->$1}', $c, -1);
			
			$code = $matches[2];

			switch (count($matches)) {
				case 4:
					return '<?php if ('.$c.') { ?>' .$matches[2] . '<?php }else { ?>' . $matches[3] . '<?php } ?>';
				
				case 3:
					return '<?php if ('.$c.') { ?>' .$matches[2] . '<?php } ?>';
				
				default:
					return '<?php if ('.$c.') { ?>';
			}
		}

		private function parseSwitches($matches) {
			if (!$matches[1]) return;

			$c = $matches[1];
			$c = preg_replace('/\$([\>a-zA-Z0-9_-]*)/', '\$__attr->$1', $c, -1);
			$c = preg_replace('/\${([\>a-zA-Z0-9_-]*)}/', '\${__attr->$1}', $c, -1);

			$code = $matches[2];
			$code = preg_replace('/<case value="(.*?)">([\s\S]*?)<\/case>/', 'case \'$1\' : ?>$2<?php break; ?>', $code, 1);
			$code = preg_replace('/<case value="(.*?)">([\s\S]*?)<\/case>/', '<?php case \'$1\' : ?>$2<?php break; ?>', $code);
			$code = preg_replace('/<default>([\s\S]*?)<\/default>/', '<?php default : ?>$1<?php break; ?>', $code);


			return '<?php switch ('.$c.') :' . $code . '<?php endswitch; ?>';
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