<?php
	
	/**
	 * @ proj P.M.C
	 *	(Parameter MVC Core)
	 *
	 * @ author prevdev@gmail.com
	 *
	 * @ https://github.com/Prev/engine-pmc
	 * @ 2013.05 ~ 08
	 * @ MIT LICENSE
	 *
	 * @ thanks to luaviskang@gmail.com
	 */
	
	define('PMC', true);
	define('ROOT_DIR', dirname(__FILE__));

	require ROOT_DIR . '/config/config.php';

	$oContext = Context::getInstance();
	$oContext->init(getDBInfo());
	
	if ($oContext->checkSSO()) {
		ModuleHandler::initModule($oContext->moduleID, $oContext->moduleAction);
		$oContext->procLayout();
	}

