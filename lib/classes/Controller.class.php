<?php
	
	abstract class Controller {

		protected $module;
		protected $model;
		protected $view;

		//abstract public function init();

		final public function setProperties($module, $model, $view, $controller) {
			$this->module = $module;
			$this->model = $model;
			$this->view = $view;
			//$this->controller = $controller;
		}

	}