<?php

/** 
 * Parses the class name and requires the correct file.
 *
 *  @param $class
 */
function autoload($class) {
	// only try to autoload AD B2C classes or their vendor dependencies
	if ( 0 !== strpos( $class, 'B2C' ) && 0 !== strpos( $class, 'Crypt' ) && 0 !== strpos ( $class, 'Math' ) ) {
		return;
	}

	if (strpos($class, 'Crypt_') === false && strpos($class, 'Math_') === false) {
		$class_filename = 'class-' . strtolower(str_replace('_', '-', $class)) . '.php';
	} else if (strpos($class, 'Math_') === false) {
		$class_filename = 'phpseclib/Crypt/' . str_replace('Crypt_', '', $class) . '.php';
	} else {
		$class_filename = 'phpseclib/Math/' . str_replace('Math_', '', $class) . '.php';
	}

	$plugin_directory = plugin_dir_path( __FILE__ );

	if ( file_exists( $plugin_directory.$class_filename ) ) {
		require_once $class_filename;
	}
}

/**
 * Registers the autoloader.
 */
spl_autoload_register('autoload');

?>