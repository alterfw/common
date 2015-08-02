<?php

namespace Alter\Common;

/**
* Class Loader
*
* This class load all the user files
*/
class Loader
{

	private $app;
	private $folders;
	private $handlers = [];

	function __construct($app, $folders = ['model', 'controller', 'view', 'option'])
	{
		if(!empty($folders)) $this->folders = $folders;
		$this->app = $app;
	}

	protected function load()
	{

		// User Models, Views and Controllers

		foreach ($this->folders as $folder) {
			foreach (glob(APPLICATION_PATH . '/' . $folder . "/*.php") as $file) {
				if(!empty($file)):
					$this->loadFile($file);
				endif;
			}
		}

	}

	protected function loadFile($file){

		try{

			$name = str_replace('.php', '', $file);
			$name_arr = explode('/', $name);
			$name = $name_arr[count($name_arr) - 1];

			require $file;

			if(!class_exists($name)){
				throw new \InvalidArgumentException("The class " . $name ." cannot be found, please check if the file and class name is correct");
			}

			$instance = new $name;
			foreach($this->handlers as $handler) {
				if (is_subclass_of($instance, $handler['type'])) {
					 $handler['callback']($this->app, $instance);
				}
			}

		}catch(InvalidArgumentException $e){
			trigger_error($e->getMessage(), E_USER_WARNING);
		}

	}

	public function handle($type, $callback) {
		array_push($this->handlers, ['type'=>$type, 'callback'=>$callback]);
	}

}
