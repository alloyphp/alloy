<?php
/**
 * @package Alloy
 * @link http://alloyframework.org
 */

// Show all errors by default
error_reporting(-1);
ini_set('display_errors', 'On');

// PHP version must be 5.3 or greater
if(version_compare(phpversion(), "5.3.2", "<")) {
	throw new \RuntimeException("PHP version must be 5.3.2 or greater to run Alloy Framework.<br />\nCurrent PHP version: " . phpversion());
}


/**
 * Configuration settings
 */
$cfg = require(dirname(__DIR__) . '/app/config/app.php');

// Host-based config file for overriding default settings in different environments
$cfgHost = array();
$cfgHostFile = $cfg['path']['config'] . '/' . strtolower(php_uname('n')) . '/app.php';
if(file_exists($cfgHostFile)) {
	$cfgHost = require($cfgHostFile);
	// Override lib path if provided before manually requiring in base classes
	if(isset($cfgHost['path']['lib'])) {
        $cfg['path']['lib'] = $cfgHost['path']['lib'];
	}
}

// Ensure at least a lib path is set
if(!isset($cfg['path']['lib'])) {
    throw new \InvalidArgumentException("Configuration must have at least \$cfg['path']['lib'] set in order to load required classes.");
}


/**
 * Load Kernel
 */
try {
    // Get Kernel with config and host config
    require $cfg['path']['lib'] . '/Alloy/Kernel.php';
    $kernel = \Alloy\Kernel::getInstance($cfg);
    $kernel->config($cfgHost);


    /**
     * Class autoloaders - uses PHP 5.3 SplClassLoader
     */
    require $kernel->config('path.lib') . '/Alloy/ClassLoader.php';

    // Autoload: Alloy lib
    $alloyLoader = new \Alloy\ClassLoader('Alloy', $kernel->config('path.lib'));
    $alloyLoader->register();

    // Autoload: Modules in app
    $moduleLoader = new \Alloy\ClassLoader('Module', $kernel->config('path.app'));
    $moduleLoader->register();
    
    // Debug?
	if($kernel->config('debug')) {
		// Enable debug mode
		$kernel->debug(true);
		
		// Show all errors
		error_reporting(-1);
		ini_set('display_errors', 'On');
	} else {
		// Show NO errors
		//error_reporting(0);
		//ini_set('display_errors', 'Off');
	}
} catch(Exception $e) {
    echo $e->getTraceAsString();
    exit();
}


/**
 * Get and return instance of Alloy Kernel
 * Checks if 'Alloy' function already exists (so it can be overridden/customized if needed)
 */
if(!function_exists('Alloy')) {
	function Alloy(array $config = array()) {
		return \Alloy\Kernel::getInstance($config);
	}
}
