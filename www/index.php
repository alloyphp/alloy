<?php
// Show all errors by default
error_reporting(-1);
ini_set('display_errors', 'On');

// PHP version must be 5.2 or greater
if(version_compare(phpversion(), "5.2.0", "<")) {
	exit("<b>Fatal Error:</b> PHP version must be 5.2.0 or greater to run Alloy Framework.");
}

// Configuration settings
$cfg = require(dirname(dirname(__FILE__)) . '/app/config.php');

// Host-based config file for overriding default settings in different environments
$cfgHostFile = dirname(dirname(__FILE__)) . '/app/config.' . strtolower(php_uname('n')) . '.php';
if(file_exists($cfgHostFile)) {
	$cfgHost = require($cfgHostFile);
	$cfg = array_merge($cfg, $cfgHost);
}

// Cont-xt Kernel
require $cfg['alloy']['path_lib'] . '/Alloy/Kernel.php';

// Run!
$kernel = false;
try {
	$kernel = Alloy($cfg);
	spl_autoload_register(array($kernel, 'load'));
	set_error_handler(array($kernel, 'errorHandler'));
	
	// Debug?
	if($kernel->config('alloy.debug')) {
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
	
	// Initial response code (not sent to browser yet)
	$responseStatus = 200;
	$response = $kernel->response($responseStatus);
	
	// Router - Add routes we want to match
	$router = $kernel->router();
	
	$router->route('module_item_action', '/<:module>/<#item>/<:action>(.<:format>)')
		->defaults(array('format' => 'html'));
	
	$router->route('module_item', '/<:module>/<#item>(.<:format>)')
		->defaults(array('action' => 'view', 'format' => 'html'))
		->get(array('action' => 'view'))
		->post(array('action' => 'post'))
		->put(array('action' => 'put'))
		->delete(array('action' => 'delete'));
		
	$router->route('module', '/<:module>(.<:format>)')
		->defaults(array('action' => 'index', 'format' => 'html'));
	
	/*
	$router->route('default', '/')
		->defaults(array('module' => 'Home', 'action' => 'index', 'format' => 'html'));
	*/
	
	// Router - Match HTTP request and return named params
	$request = $kernel->request();
	$requestUrl = !empty($_GET['r']) ? $_GET['r'] : '/';
	$requestMethod = $request->method();
	// Emulate REST for browsers
	if($request->isPost() && $request->post('_method')) {
		$requestMethod = $request->post('_method');
	}
	
	// Cheat on default route for now
	if($requestUrl == '/') {
		$params = array('module' => 'Home', 'action' => 'index', 'format' => 'html');
	} else {
		$params = $router->match($requestMethod, $requestUrl);
		$request->route = $router->matchedRoute()->name();
	}
	// Set matched params back on request object
	$request->setParams($params);
	
	// Required params
	$module = $params['module'];
	$action = $params['action'];
	
	// Set class load paths - works for all classes using the PEAR/Zend naming convention placed in 'lib'
	$kernel->addLoadPath($kernel->config('alloy.path_lib'));
	// Module paths
	$kernel->addLoadPath($kernel->config('alloy.path_modules'), 'Module_');
	
	// Run/execute
	$content = $kernel->dispatchRequest($request, $module, $action, array($request));
	
// Authentication Error
} catch(Cx_Exception_Auth $e) {
	$responseStatus = 403;
	$content = $e;
	$kernel->dispatch('user', 'loginAction', array($request));
 
// 404 Errors
} catch(Cx_Exception_FileNotFound $e) {
	$responseStatus = 404;
	$content = $e;

// Method Not Allowed
} catch(Cx_Exception_Method $e) {
	$responseStatus = 405; // 405 - Method Not Allowed
	$content = $e;

// HTTP Exception
} catch(Cx_Exception_Http $e) {
	$responseStatus = $e->getCode(); 
	$content = $e;

// Module/Action Error
} catch(Cx_Exception $e) {
	$responseStatus = 500;
	$content = $e;
 
// Generic Error
} catch(Exception $e) {
	$responseStatus = 500;
	$content = $e;
}

// Exception detail depending on mode
if($content instanceof Exception) {
	$content = "[ERROR] " . $e->getMessage();
	// Show debugging info?
	if($cfg['alloy']['debug']) {
		$content .= "<p>File: " . $e->getFile() . " (" . $e->getLine() . ")</p>";
		$content .= "<pre>" . $e->getTraceAsString() . "</pre>";
	}
	// Send HTTP Response
	$response->status($responseStatus);
}

// Send proper response
if($kernel) {
	$response->content($content);
	$response->send();
	
} else {
	header("HTTP/1.0 500 Internal Server Error");
	echo "<h1>Internal Server Error</h1>";
	echo $content;
}