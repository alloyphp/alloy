<?php
/**
 * @package Alloy
 * @link http://alloyframework.org
 */

// Show all errors by default - they can be turned off later if needed
error_reporting(-1);
ini_set('display_errors', 'On');

// PHP version must be 5.3.1 or greater
if(version_compare(phpversion(), "5.3.1", "<")) {
    throw new \RuntimeException("PHP version must be 5.3.1 or greater to run Alloy Framework.<br />\nCurrent PHP version: " . phpversion());
}

/**
 * Get and return instance of \Alloy\Kernel
 * Checks if 'Kernel' function already exists so it can be overridden/customized
 */
if(!function_exists('Kernel')) {
    function Kernel(array $config = array()) {
        return \Alloy\Kernel::getInstance($config);
    }
}

/**
 * Configuration settings
 */
$cfgAlloy = require(__DIR__ . '/config/app.php');

// Host-based config file for overriding default settings in different environments
$cfgHost = array();
$cfgHostFile = $cfg['path']['config'] . '/' . strtolower(php_uname('n')) . '/app.php';
if(file_exists($cfgHostFile)) {
    $cfgHost = require($cfgHostFile);
    // Override lib path if provided before manually requiring in base classes
    if(isset($cfgHost['path']['lib'])) {
        $cfgAlloy['path']['lib'] = $cfgHost['path']['lib'];
    }
}

// Ensure at least a lib path is set
if(!isset($cfgAlloy['path']['lib'])) {
    var_dump($cfgAlloy);
    throw new \InvalidArgumentException("Configuration must have at least \$cfg['path']['lib'] set in order to load required classes.");
}

/**
 * Load Kernel
 */
try {
    // Get Kernel with config and host config
    require $cfgAlloy['path']['lib'] . '/Alloy/Kernel.php';
    $kernel = \Kernel($cfgAlloy);
    $kernel->config($cfgHost);
    unset($cfgAlloy, $cfgHost);

    /**
     * Class autoloaders - uses PHP 5.3 SplClassLoader
     */
    $loader = $kernel->loader();

    // Register classes with namespaces
    $loader->registerNamespaces(array(
        'Alloy' => $kernel->config('path.lib'),
        'App' => $kernel->config('path.lib'),
        'Module' => $kernel->config('path.app'),
        'Spot' => $kernel->config('path.lib'),
    ));

    // Register a library using the PEAR naming convention
    $loader->registerPrefixes(array(
        'Zend_' => $kernel->config('path.lib'),
    ));
    
    // Activate the autoloader
    $loader->register();
    
    /**
     * Debug?
     */
    if($kernel->config('debug')) {
        // Enable debug mode
        $kernel->debug(true);
    } else {
        // Show NO errors
        error_reporting(0);
        ini_set('display_errors', 'Off');
    }
} catch(Exception $e) {
    echo $e->getTraceAsString();
    exit();
}