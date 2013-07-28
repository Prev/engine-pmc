<?php if (!defined('PMC')) exit; ?>
<?php Context::getInstance()->addHeaderFile('/modules/index/template/stylesheet.css', -1, 'head', NULL,NULL); ?><div id="welcome" class="clearfix"><div class="fl"><img src="http://127.0.0.1/pmc/modules/index/template/img/circle.png" width="450" height="250"></div><div id="welcome-text" class="fl"><div id="welcome-title"><?php echo fetchLocale(array(
				'en'=>'Thank you for install "engine pmc"',
				'kr'=>'"engine pmc" 를 설치 해 주셔서 감사합니다.'
			)); ?></div><div id="welcome-description"><?php echo fetchLocale(array(
				'en'=>'"engine pmc" is the MVC core that make easy developing of the website, maintenance, joint work',
				'kr'=>'"engine pmc" 는 웹사이트의 개발속도, 유지보수, 공동 작업등을 쉽게 해 주는 모듈 기반의 MVC 코어입니다.'
			)); ?></div></div></div><div><?php var_dump2($__attr->user); ?></div><div><?php if($__attr->loggedin) { ?><a href="http://127.0.0.1/pmc/?module=login&action=procLogout"><?php echo fetchLocale(array( 'en'=>'Sign out', 'kr'=>'로그아웃')); ?></a><?php } ?><?php if(!$__attr->loggedin) { ?><a href="<?php echo LOGIN_URL ?>"><?php echo fetchLocale(array( 'en'=>'Sign in', 'kr'=>'로그인')); ?></a><?php } ?></div>