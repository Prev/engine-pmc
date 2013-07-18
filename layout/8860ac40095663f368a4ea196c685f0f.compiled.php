<?php if (!defined('PMC')) exit; ?>
<?php Context::getInstance()->addHeaderFile('/layouts/error/stylesheet.css', -1, 'head', NULL,NULL); ?><div id="box"><div id="container"><h1><?php echo fetchLocale(array(
			'en' =>'Error on this page',
			'kr' =>'페이지에 오류가 있습니다'
		)); ?></h1><div id="content"><?php echo $__attr->errorMessage; ?></div></div><div id="bottom" class="clearfix"><div id="left">Powered by 'PMC'</div><div id="right">&copy; Prev (prevdev@gmail.com)</div></div></div>