<?php
	
	/**
	 * @ author prevdev@gmail.com
	 * @ 2013.05
	 *
	 *
	 * HeaderTagHandler class
	 * Control <head> tag's innerHTML
	 */
	
	class HeaderTagHandler extends Handler{
		
		
		/**
		 * Browser title
		 * display as <title>_VALUE_</title>
		 */
		var $browserTitle;
		
		/**
		 * Browser favicon path
		 */
		var $faviconPath;
		
		/**
		 * Array Of CSS file's info (path, position, targetie)
		 */
		var $cssFiles;
		
		/**
		 * Array Of JS file's info  (path, position, targetie)
		 */
		var $jsFiles;
		
		/**
		 * Array Of Meta Tags
		 */
		var $metaTags;
		
		/**
		 * Array Of Header Tags (raw)
		 */
		var $headerTags;
		
		
		
		
		/**
		 * Constructure
		 * Initialize member variables
		 */
		function HeaderTagHandler() {
			$this->cssFiles = array();
			$this->jsFiles = array();
			$this->metaTags = array();
			$this->headerTags = array();
		}
		
		
		/**
		 * Insert content into array with specific index
		 */
		private function insertIntoArray(&$array, $value, $index) {
			if ($index === -1 || count($array) <= $index)
				array_push($array, $value);
			else {
				$a1 = array_slice($array, 0, $index);
				$a2 = array_slice($array, $index, count($array));
				
				$array = &array_merge($a1, array($value), $a2);
			}
		}
		
		
		/**
		 * Set Browser title
		 */
		public function setBrowserTitle($title) {
			$this->browserTitle = $title;
		}
		
		/**
		 * Set Favicon
		 */
		public function setFavicon($path) {
			$this->faviconPath = $path;
		}
		
		/**
		 * Add CSS File
		 */
		public function addCSSFile($path, $index=-1, $position='head', $requiredAgent=NULL, $targetie=NULL) {
			$this->insertIntoArray($this->cssFiles, (object) array(
				'path'=>$path,
				'position'=>$position,
				'requiredAgent'=>$requiredAgent,
				'targetie'=>$targetie
			), $index);
		}
		
		/**
		 * Add JS File
		 */
		public function addJSFile($path, $index=-1, $position='head', $requiredAgent=NULL, $targetie=NULL) {
			$this->insertIntoArray($this->jsFiles, (object) array(
				'path'=>$path,
				'position'=>$position,
				'requiredAgent'=>$requiredAgent,
				'targetie'=>$targetie
			), $index);
		}
		
		/**
		 * Add Less CSS File
		 */
		public function addLesscFile($path, $index=-1, $position='head', $requiredAgent=NULL, $targetie=NULL) {
			$this->addCSSFile(
				CacheHandler::getLessCachePath($path),
				$index,
				$position,
				$requiredAgent,
				$targetie
			);
			/*$siteCacheDir = '/cache/' . getServerInfo()->host . '_' . substr(md5(getServerInfo()->uri), 0, 8);
			if (!is_dir(ROOT_DIR . $siteCacheDir . '/lessc/')) mkdir(ROOT_DIR . $siteCacheDir . '/lessc/');
			$cachePath = $siteCacheDir . '/lessc/' . md5($path) . '.css';
			
			try {
				$lessc = new lessc();
				$lessc->checkedCompile(ROOT_DIR.$path, ROOT_DIR.$cachePath);
			}catch (Exception $e) {
				Context::printWarning(array(
					'en' => 'Less CSS Error - ' . $e->getMessage(),
					'kr' => 'Less CSS 오류 - ' . $e->getMessage()
				));
			}
			$this->addCSSFile($cachePath, $index, $position, $requiredAgent, $targetie);*/
		}
		
		/**
		 * Add Meta Tag
		 */
		public function addMetaTag($stringOrObj, $index=-1) {
			$type = gettype($stringOrObj);
			switch ($type) {
				case 'array':
				case 'object':
					$str = '<meta';
					foreach($stringOrObj as $key => $value) $str .= " ${key}=\"${value}\"";
					$str .= '>';
					
					$this->insertIntoArray($this->metaTags, $str, $index);
					break;
					
				default:
					if (strpos('<meta', $stringOrObj) !== false)
						$this->insertIntoArray($this->metaTags, $stringOrObj, $index);
					break;
			}
		}
		
		/**
		 * Add Header Tag
		 */
		public function addHeaderTag($string, $index=-1) {
			$this->insertIntoArray($this->headerTags, $string, $index);
		}
		
		
		/**
		 * Get js tags in specific position
		 */
		public function getTags($position) {
			if ($position === 'head') {
				$html = '';
				
				// print <title> tag
				if ($this->browserTitle)
					$html .= '<title>'.$this->browserTitle.'</title>' . LINE_END;
				
				// print favicon tag
				if ($this->faviconPath) {
					$html .= '<link rel="shortcut icon" href="'.RELATIVE_URL . $this->faviconPath.'">' . LINE_END;
				}
				
				// print <meta> tags
				if ($this->metaTags) {
					for ($i=0; $i<count($this->metaTags); $i++)
						$html .= $this->metaTags[$i] . LINE_END;
				}
				
				// print css, js files
				$html .= $this->getCSSAndJSTags('head');
				
				// print other header tags
				if ($this->headerTags) {
					for ($i=0; $i<count($this->headerTags); $i++)
						$html .= $this->headerTags[$i] . LINE_END;
				}
			}else {
				return $this->getCSSAndJSTags($position);
			}
			
			return $html;
		}
		
		
		/**
		 * print css and js tags
		 * merge twice array and print it out
		 */
		private function getCSSAndJSTags($position) {
			$html = '';
			$headerFiles = array_merge(($this->cssFiles ? $this->cssFiles : array()), ($this->jsFiles ? $this->jsFiles : array()));
			
			for ($i=0; $i<count($headerFiles); $i++) {
				$path = ROOT_DIR . $headerFiles[$i]->path;
				$url = RELATIVE_URL . $headerFiles[$i]->path.'?'.filemtime($path);
				
				if (!is_file($path)) {
					Context::printWarning(array(
						'en'=>"Fail to load file '$path'",
						'kr'=>"파일 '$path' 을 불러올 수 없음"
					));
					continue;
				}

				if ($headerFiles[$i]->position !== $position) continue;
				if (isset($headerFiles[$i]->requiredAgent) && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), strtolower($headerFiles[$i]->requiredAgent)) === false) continue; 
				
				if ($headerFiles[$i]->targetie !== NULL)
					$html .= '<!--[if '.$headerFiles[$i]->targetie.']>' . LINE_END;
				
				switch ($extension = substr(strrchr($path, '.'), 1)) {
					case 'css' :
						$html .= '<link rel="stylesheet" type="text/css" href="'.$url.'">' . LINE_END;
						break;
						
					case 'js' :
						$html .= '<script type="text/javascript" src="'.$url.'"></script>' . LINE_END;
						break;
						
					default :
						Context::printWarning(array(
							'en'=>'Unknown type of file',
							'kr'=>'알수없는 종류의 파일임'
						));
						break;
				}
				
				if ($headerFiles[$i]->targetie !== NULL)
					$html .= '<![endif]-->' . LINE_END;
			}
			return $html;
		}
		
	}
