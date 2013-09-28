<?php
	
	/**
	 * @ author prevdev@gmail.com
	 * @ 2013.05 ~ 09
	 */
	
	if (!defined('PMC')) {
		exit();
	}
	
	session_start();
	error_reporting(E_ALL & ~E_NOTICE);
	date_default_timezone_set('Asia/Seoul');


	require ROOT_DIR . '/lib/functions/common.function.php';
	
	
	/**
	 * Define PMC version
	 */
	define('PMC_VERSION', '0.4.5');
	
	
	/**
	 * Define text encoding
	 * Default : utf-8
	 */
	define('TEXT_ENCODING', 'utf-8');
	
	
	/**
	 * Define X_UA_Compatible (ie edge)
	 */
	define('X_UA_Compatible', 'IE=Edge,chrome=1');

	
	/**
	 * Define default Layout name
	 */
	define('LAYOUT_NAME', 'default');
	

	/**
	 * Define using short url
	 * ex) menu -> http://engine-pmc.org/freeboard
	 * ex) article -> http://engine-pmc.org/15
	 */
	define('USE_SHORT_URL', true);
	

	/**
	 * Define using real name in board, etc
	 */
	define('USE_REAL_NAME', false);

	/**
	  * Define default locale
	  * Get current locale by calling getLocale() func
	  */
	define('DEFAULT_LOCALE', 'en');
	
	
	/**
	 * define using ob_gzhandler
	 */
	define('OB_GZIP', true);
	
	
	/**
	 * define zipping blank
	 */
	define('ZIP_BLANK', false);
	
	
		/**
		 * define line end str
		 * if zipping blank, \r\n str / else ''
		 */
		define('LINE_END', (ZIP_BLANK ? '' : "\r\n"));
	

	/**
	 * Define Debugging mode
	 * if do not define this, it will be defined automatically
	 */
	//define('DEBUG_MODE', true);
	
	
	/**
	 * Define log file's path
	 */
	define('LOG_FILE_PATH', (ROOT_DIR . '/files/pmc_error.log'));
	
	
	/**
	 * Define server info file's path
	 * Default path is '/config/server-info.txt'
	 * It will be used to get server info
	 */
	define('SERVER_INFO_FILE_PATH', (ROOT_DIR . '/config/server-info.json'));
	
		/**
		 * Define now request's protocol
		 */
		define('PROTOCOL', empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https');
		
		/**
		 * Define realavite url (=root url)
		 */
		define('RELATIVE_URL', getRelativeUrl());
		
		
		/**
		 * Define session domain
		 */
		define('SESSION_DOMAIN', getSessionDomain());
		

		/**
		 * Define server uri
		 */
		define('SERVER_URI', getServerUri());
		

		/**
		 * Define sso session name
		 */
		define('SSO_COOKIE_NAME', 'pmc_sess_key');

		/**
		 * Define sso session name
		 */
		define('SSO_SESSION_NAME', 'pmc_sso_data_' . substr(md5(RELATIVE_URL), 0, 10));


		/**
		 * Define real url
		 */
		define('REAL_URI', PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		define('REAL_URL', REAL_URI);


	
	/**
	 * login file url
	 */
	//define('LOGIN_URL', RELATIVE_URL . '/?module=login&action=dispLoginPage');
	define('LOGIN_URL', RELATIVE_URL . (USE_SHORT_URL ? '/login' : '/?module=login'));
	

	/**
	 * define sso process file url
	 */
	define('SSO_URL', RELATIVE_URL . '/sso-server/');


	/**
	 * Define Document Type
	 * html5, xhtml-t, xhtml-s, xhtml, html4-t, html4, none
	 */
	define('DOCTYPE', 'html5');
	
	
	header('Content-Type: text/html; charset=' . TEXT_ENCODING);
	ini_set('display_errors', (DEBUG_MODE ? 1 : 0));
	
	

	// lib.rsa.php, lib.lessc.php is required when is needed

	require ROOT_DIR . '/config/database.php';
	require ROOT_DIR . '/config/secure-keys.php';
	require ROOT_DIR . '/lib/others/lib.idiorm.php';

	require ROOT_DIR . '/lib/classes/Handler.class.php';
	require ROOT_DIR . '/lib/classes/DBHandler.class.php';
	require ROOT_DIR . '/lib/classes/ModuleHandler.class.php';
	require ROOT_DIR . '/lib/classes/MVC.class.php';
	require ROOT_DIR . '/lib/classes/Module.class.php';
	require ROOT_DIR . '/lib/classes/Model.class.php';
	require ROOT_DIR . '/lib/classes/View.class.php';
	require ROOT_DIR . '/lib/classes/Controller.class.php';
	require ROOT_DIR . '/lib/classes/HeaderTagHandler.class.php';
	require ROOT_DIR . '/lib/classes/ErrorLogger.class.php';
	require ROOT_DIR . '/lib/classes/TemplateHandler.class.php';
	require ROOT_DIR . '/lib/classes/CacheHandler.class.php';
	require ROOT_DIR . '/lib/classes/Context.class.php';
	require ROOT_DIR . '/lib/classes/User.class.php';