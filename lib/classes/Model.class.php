<?php
	
	/**
	 * @author prevdev@gmail.com
	 * @2013.07
	 *
	 *
	 * (abstract) Model Class
	 */

	class Model extends MVC {

		protected $module;
		protected $view;
		protected $controller;

		final public function setMMVC($module, $model, $view, $controller) {
			$this->module = $module;
			$this->view = $view;
			$this->controller = $controller;
		}
	}