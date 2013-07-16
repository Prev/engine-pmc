<?php
	
	class Controller extends MVC {

		protected $module;
		protected $model;
		protected $view;

		final public function setMMVC($module, $model, $view, $controller) {
			$this->module = $module;
			$this->model = $model;
			$this->view = $view;
		}

	}