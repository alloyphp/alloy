<?php
/**
 * @package Alloy
 * @link http://alloyframework.org
 */

// Require PHPUnit
require_once 'PHPUnit/Framework.php';

// Date setup
date_default_timezone_set('America/Chicago');


/**
 * Configuration settings
 */
$cfg = require(dirname(__DIR__) . '/app/config/app.php');

/**
 * Autoload test fixtures
 */
try {
	// Get Kernel with config and host config
	require $cfg['path']['lib'] . '/Alloy/Kernel.php';
	$kernel = \Alloy\Kernel::getInstance($cfg);
	//$kernel->config($cfgHost);
	
	
	/**
     * Class autoloaders - uses PHP 5.3 SplClassLoader
     */
    require $cfg['path']['lib'] . '/Alloy/ClassLoader.php';

    // Autoload: Alloy lib
    $alloyLoader = new \Alloy\ClassLoader('Alloy', $cfg['path']['lib']);
    $alloyLoader->register();
} catch(Exception $e) {
    echo $e->getTraceAsString();
    exit(__FILE__);
}


/**
 * Get and return instance of AppKernel
 * Checks if 'Alloy' function already exists so it can be overridden/customized
 */
if(!function_exists('Alloy')) {
	function Alloy(array $config = array()) {
		return \Alloy\Kernel::getInstance($config);
	}
}
