<?php
	
	/*
	 * @ author prevdev@gmail.com
	 * @ https://github.com/Prev/engine-pmc
	 *
	 * @ 2013.05 - 2013.07
	 *
	 * @ proj P.M.C (Parameter MVC Core)
	 */

	define('PMC', true);
	define('ROOT_DIR', dirname(__FILE__));

	require ROOT_DIR . '/config/config.php';

	$oContext = Context::getInstance();
	$oContext->init(getDBInfo());

	ModuleHandler::initModule(
		$oContext->moduleID,
		$oContext->moduleAction
	);

	$oContext->procLayout();


	$data = DBHandler::for_table('user')->find_one(1);
	$user = new User($data);
	$user->input_id = 'steven';

	var_dump2($user);
