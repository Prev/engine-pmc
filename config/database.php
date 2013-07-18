<?php
	
	function getDBInfo() {
		$dt = debug_backtrace();
		
		if ((ROOT_DIR . DIRECTORY_SEPARATOR . 'index.php' != $dt[0]['file']) && (ROOT_DIR . DIRECTORY_SEPARATOR . 'pmc.sso.php' != $dt[0]['file']) ) {
			if (class_exists(Context))
				Context::printWarning('SandBox error : call getDBInfo in other file');
			else
				throw new Exception('SandBox error : call getDBInfo in other file');
			return;
		}
		
		return (object) array(
			'type' => 'mysql',
			'host' => 'localhost',
			'username' => 'pmc_test',
			'password' => 'q1w2e3',
			'database_name' => 'pmc_test',
			'prefix' => 'pmc_'
		);
	
	}
