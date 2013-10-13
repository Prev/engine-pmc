<?php
	
	class PageModel extends Model {

		public function getPageName() {
			if (isset($_GET['page']))
				return $_GET['page'];
			
			$row = DBHandler::for_table('menu')
				->select_many('title', 'extra_vars')
				->where('id', Context::getInstance()->selectedMenu->id)
				->find_one();


			if (isset($row->extra_vars)) {
				$o = json_decode($row->extra_vars);
				if (!isset($o) || !isset($o->page))
					return;
				return $o->page;
			}else if (isset($row->title))
				return $row->title;
			else
				return NULL;
		}

		public function getPagePath($page) {
			return ModuleHandler::getModuleDir($this->module->moduleID) .
				'/pages/' . $page . '/' . $page . '.html';
		}

		public function changeMenu($menuTitle) {
			$parentMenuTitles = array();

			$row = DBHandler::for_table('menu')
				->where('title', $menuTitle)
				->find_one();
			
			Context::getInstance()->selectedMenu = $row->getData();
			
			while ($row->parent_id != NULL) {
				$row = DBHandler::for_table('menu')
					->where('id', $row->parent_id)
					->find_one();
				
				array_unshift(Context::getInstance()->parentMenus, $row->getData());
			}
		}
	}