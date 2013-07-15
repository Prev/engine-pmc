<?php
	
	abstract class Model {

		protected $module;
		protected $view;
		protected $controller;

		//abstract public function init();

		final public function setProperties($module, $model, $view, $controller) {
			$this->module = $module;
			//$this->model = $model;
			$this->view = $view;
			$this->controller = $controller;
		}

	}