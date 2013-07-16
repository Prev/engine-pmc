<?php
	
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