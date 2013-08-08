<?php
	
	/**
	 * @author prevdev@gmail.com
	 * @2013.07
	 *
	 *
	 * MVC Class
	 * Base of Model/Controller/View
	 */

	abstract class MVC {

		abstract public function setMMVC($module, $model, $view, $controller);
		final public function setProperties($data) {
			foreach ($data as $key => $value) {
				$this->{$key} = $value;
			}
		}
	
	}