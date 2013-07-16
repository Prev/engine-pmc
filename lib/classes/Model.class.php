<?php
	
	class Model {

		protected $module;
		protected $view;
		protected $controller;

		final public function setProperties($module, $model, $view, $controller) {
			$this->module = $module;
			$this->view = $view;
			$this->controller = $controller;
		}

	}