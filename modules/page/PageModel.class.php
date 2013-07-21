<?php
	
	class PageModel extends Model {

		public function getPageName() {
			if (isset($_GET['page']))
				return $_GET['page'];
			
			$row = DBHandler::for_table('menu')
				->select('extra_vars')
				->where('title', Context::getInstance()->selectedMenu)
				->find_one();

			if (!isset($row->extra_vars))
				return;
			else {
				$o = json_decode($row->extra_vars);
				if (!isset($o) || !isset($o->page))
					return;
				return $o->page;
			}
		}

	}