<?php

/** 
 * Parses the class name and requires the correct file.
 */
function autoload($class) {
	if (strpos($class, 'Crypt_') === false && strpos($class, 'Math_') === false) {
		$class_filename = 'class-' . strtolower(str_replace('_', '-', $class)) . '.php';
	} else if (strpos($class, 'Math_') === false) {
		$class_filename = 'phpseclib/Crypt/' . str_replace('Crypt_', '', $class) . '.php';
	} else {
		$class_filename = 'phpseclib/Math/' . str_replace('Math_', '', $class) . '.php';
	}
	require_once $class_filename;
}

/**
 * Registers the autoloader.
 */
spl_autoload_register('autoload');

?>