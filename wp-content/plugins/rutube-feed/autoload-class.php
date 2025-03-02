<?php

spl_autoload_register(function ($class) {
	$class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
	$class = str_replace('App', 'app', $class);
	$file = plugin_dir_path(__FILE__)   . $class . '.php';
	
	if (file_exists($file)) {
		require_once $file;
	}
});
