<?php
	
	class Controller {

		protected $module;
		protected $model;
		protected $view;

		final public function setProperties($module, $model, $view, $controller) {
			$this->module = $module;
			$this->model = $model;
			$this->view = $view;
		}

	}