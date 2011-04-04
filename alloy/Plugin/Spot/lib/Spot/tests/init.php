-<?php
/**
 * @package Spot
 * @link http://spot.os.ly
 */

// Require Spot_Config
require_once dirname(__DIR__) . '/Config.php';

// Date setup
date_default_timezone_set('America/Chicago');

// Setup available adapters for testing

$cfg = new \Spot\Config();
// MySQL
$adapter = $cfg->addConnection('test_mysql', 'mysql://test:password@localhost/spot_test');
// MongoDB with adapter options
$adapter = $cfg->addConnection('test_mongodb', 'mongodb://localhost:28017', array(
    'cursor' => array(
        'timeout' => 10
    ),
    'mapper' => array(
        'translate_id' => true
	)
));


/**
 * Return Spot mapper for use
 */
$mapper = new \Spot\Mapper($cfg);
function test_spot_mapper() {
    global $mapper;
    return $mapper;
}


/**
 * Autoload test fixtures
 */
function test_spot_autoloader($className) {
    // Don't attempt to autoload 'PHPUnit_' or 'Spot' namespaced classes
    if(false !== strpos($className, 'PHPUnit_') || false !== strpos($className, 'Spot')) {
        return false;
    }
    $classFile = str_replace('_', '/', $className) . '.php';
    require __DIR__ . '/' . $classFile;
}
spl_autoload_register('test_spot_autoloader');
