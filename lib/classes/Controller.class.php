<?php
	
	/**
	 * @author prevdev@gmail.com
	 * @2013.07
	 *
	 *
	 * (abstract) Controller Class
	 */

	class Controller extends MVC {

		public $module;
		public $model;
		public $view;

		final public function setMMVC($module, $model, $view, $controller) {
			$this->module = $module;
			$this->model = $model;
			$this->view = $view;
		}

	}