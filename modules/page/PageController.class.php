<?php
	
	class PageController extends Controller {

		public function init() {
			$page = $this->model->getPageName();
			
			if (!isset($page) || !$page)
				Context::printErrorPage(array(
					'en' => 'page is not defined',
					'ko' => '페이지가 정의되지 않았습니다'
				));
			else {
				if (!is_file($this->model->getPagePath($page))) {
					Context::printErrorPage(array(
						'en' => 'cannot load page "'.$page.'"',
						'ko' => '페이지 "'.$page.'" 를 불러올 수 없습니다.'
					));
				}

				if (Context::getInstance()->selectedMenu->title != $page)
					$this->model->changeMenu($page);

				$this->view->page = $page;
			}
		}

	}