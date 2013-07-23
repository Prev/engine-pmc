<?php
	
	/**
	 * @author prevdev@gmail.com
	 * @2013.05
	 *
	 *
	 * ErrorLogger Class
	 */
	
	class ErrorLogger extends Handler {
		
		static $fp;
		
		public static function log($str, $backtrace=NULL) {
			if (!$fp) $fp = fopen(LOG_FILE_PATH, 'a');
			if (!$backtrace) $backtrace = debug_backtrace();
			
			$output = $str . '  - ' . date('Y-m-d H:i:s') . '  - ' . $_SERVER['REMOTE_ADDR'];
			for ($i=0; $i<count($backtrace); $i++) {
				$path = getFilePathClear($backtrace[$i]['file']);
				
				$output .= "\r\n\t in \"" . $path . '" on line '. $backtrace[$i]['line'];
			}
			fwrite($fp, $output ."\r\n\r\n");
			chmod(LOG_FILE_PATH, 0755);
		}
		
	}