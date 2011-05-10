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
$cfgPath = __DIR__ . '/config';

// Host-based config file for overriding default settings in different environments
$cfg = array();
$cfgHostFile = $cfgPath . '/' . strtolower(php_uname('n')) . '/app.php';
if(file_exists($cfgHostFile)) {
    // Host-based config file
    $cfg = require($cfgHostFile);
} else {
    // Default config file
    $cfg = require($cfgPath . '/app.php');
}

// Ensure at least a lib path is set for both alloy and app
if(!isset($cfg['alloy']['path']['lib']) || !isset($cfg['app']['path']['lib'])) {
    throw new \InvalidArgumentException("Configuration must have at least \$cfg['alloy']['path']['lib'] and \$cfg['app']['path']['lib'] set in order to load required classes.");
}

/**
 * Load Kernel
 */
try {
    // Get Kernel with config and host config
    require_once $cfg['alloy']['path']['lib'] . '/Alloy/Kernel.php';
    $kernel = \Kernel($cfg);
    unset($cfg);

    /**
     * Class autoloaders - uses PHP 5.3 SplClassLoader
     */
    $loader = $kernel->loader();

    // Register classes with namespaces
    $loader->registerNamespaces($kernel->config('app.autoload.namespaces', array()));

    // Register a library using the PEAR naming convention
    $loader->registerPrefixes($kernel->config('app.autoload.prefixes', array()));
    
    // Activate the autoloader
    $loader->register();
    
    /**
     * Development Mode & Debug Handling
     */
    if($kernel->config('app.mode.development')) {
        if($kernel->config('app.debug')) {
            // Enable debug mode
            $kernel->debug(true);
        }
    } else {
        // Show NO errors
        error_reporting(0);
        ini_set('display_errors', 'Off');
    }
} catch(\Exception $e) {
    echo '<pre>';
    echo $e->getTraceAsString();
    echo '</pre>';
    exit();
}