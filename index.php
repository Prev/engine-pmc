<?php
	
	/*
	 * @ author prevdev@gmail.com
	 * @ 2013.05 - 2013.07
	 *
	 * @ proj P.M.C (Parameter MVC Core)
	 */

	/*	    ____  __  _________
		   / __ \/  |/  / ____/
		  / /_/ / /|_/ / /     
		 / ____/ /  / / /___   
		/_/   /_/  /_/\____/   
	*/	                       

	define('PMC', true);
	define('ROOT_DIR', dirname(__FILE__));
 	 
	require ROOT_DIR . '/config/config.php';
	
	$oContext = &Context::getInstance();
	$oContext->init(getDBInfo());
	
	$oModuleHandler = new ModuleHandler($oContext->moduleID);
	$oModuleHandler->procModule();
	
	if ($GLOBALS['__Module__']) {
		$oContext->printContext();
	}
	