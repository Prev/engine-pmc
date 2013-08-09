<?php

	class BoardController extends Controller {

		public function checkIsBoardAdmin($boardAdminGroup) {
			$adminGroup = isset($boardAdminGroup) ? 
				array_merge(json_decode($boardAdminGroup), User::getMasterAdmin()) :
				User::getMasterAdmin();
			
			$me = User::getCurrent();
			return ($me && $me->checkGroup($adminGroup));
		}

	}