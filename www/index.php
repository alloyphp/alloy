<?php
// Show all errors by default
error_reporting(-1);
ini_set('display_errors', 'On');

// PHP version must be 5.2 or greater
if(version_compare(phpversion(), "5.2.0", "<")) {
	exit("<b>Fatal Error:</b> PHP version must be 5.2.0 or greater to run in Cont-xt.");
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
require $cfg['cx']['path_lib'] . '/Cx/Kernel.php';

// Run!
$kernel = false;
try {
	$kernel = cx($cfg);
	spl_autoload_register(array($kernel, 'load'));
	set_error_handler(array($kernel, 'errorHandler'));
	
	// Debug?
	if($kernel->config('cx.debug')) {
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
	
	$kernel->trigger('cx_boot');
	
	// Initial response code (not sent to browser yet)
	$responseStatus = 200;
	$response = $kernel->response($responseStatus);
	
	// Router - Add routes we want to match
	$router = $kernel->router();
	$pageRouteItem = '<*page>';
	$kernel->trigger('cx_boot_router_before', array($router));
	
	// HTTP Errors
	$router->route('http_error', 'error/<#errorCode>(.<:format>)')
		->defaults(array('module' => 'Error', 'action' => 'display', 'format' => 'html', 'page' => '/'));
	
	// User reserved route
	$router->route('user', 'user/<:action>(.<:format>)')
		->defaults(array('module' => 'User', 'format' => 'html'));
	
	// Admin reserved route
	$router->route('admin', 'admin/<:action>(.<:format>)')
		->defaults(array('module' => 'Page_Admin', 'format' => 'html'));
	
	// Normal Routes
	$router->route('module', $pageRouteItem . 'm,<:module_name>,<#module_id>(/<:module_action>)(.<:format>)')
		->defaults(array('page' => '/', 'module' => 'Page', 'action' => 'index', 'module_action' => 'index', 'format' => 'html'))
		->get(array('module_action' => 'index'))
		->post(array('module_action' => 'post'))
		->put(array('module_action' => 'put'))
		->delete(array('module_action' => 'delete'));
	
	/*
	$router->route('module_item_action', $pageRouteItem . 'm,<:module_name>,<#module_id>/<#module_item>/<:module_action>(.<:format>)')
		->defaults(array('page' => '/', 'module' => 'Page', 'action' => 'index', 'format' => 'html'));
	/*/
		
	$router->route('module_item', $pageRouteItem . 'm,<:module_name>,<#module_id>/<#module_item>(/<:module_action>)(.<:format>)')
		->defaults(array('page' => '/', 'module' => 'Page', 'action' => 'index', 'module_action' => 'view', 'format' => 'html'))
		->get(array('module_action' => 'view'))
		->post(array('module_action' => 'post'))
		->put(array('module_action' => 'put'))
		->delete(array('module_action' => 'delete'));
	
	$router->route('page_action', $pageRouteItem . '/<:action>(.<:format>)')
		->defaults(array('page' => '/', 'module' => 'Page', 'format' => 'html'));
	
	$router->route('index_action', '<:action>\.<:format>')
		->defaults(array('page' => '/', 'module' => 'Page', 'format' => 'html'));
	
	$router->route('page', $pageRouteItem)
		->defaults(array('page' => '/', 'module' => 'Page', 'action' => 'index', 'format' => 'html'))
		->post(array('action' => 'post'))
		->put(array('action' => 'put'))
		->delete(array('action' => 'delete'));
		
	$kernel->trigger('cx_boot_router_after', array($router));
	
	// Router - Match HTTP request and return named params
	$request = $kernel->request();
	$requestUrl = isset($_GET['r']) ? $_GET['r'] : '/';
	$requestMethod = $request->method();
	// Emulate REST for browsers
	if($request->isPost() && $request->post('_method')) {
		$requestMethod = $request->post('_method');
	}
	$params = $router->match($requestMethod, $requestUrl);
	// Set matched params back on request object
	$request->setParams($params);
	$request->route = $router->matchedRoute()->name();
	
	// Required params
	$module = $params['module'];
	$action = $params['action'];
	
	// Set class load paths - works for all classes using the PEAR/Zend naming convention placed in 'lib'
	$kernel->addLoadPath($kernel->config('cx.path_lib'));
	// Module paths
	$kernel->addLoadPath($kernel->config('cx.path_modules'), 'Module_');
	
	// Run/execute
	$content = "";
	
	$kernel->trigger('cx_boot_dispatch_before', array(&$content));
	
	$content .= $kernel->dispatchRequest($request, $module, $action, array($request));
	
	$kernel->trigger('cx_boot_dispatch_after', array(&$content));
 
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

/*
// Error handling through core error module
if($kernel && $content && $responseStatus >= 400 && !$request->isAjax()) {
	try {
		$content = $kernel->dispatch('Error', 'displayAction', array($request, $responseStatus, $content));
	} catch(Exception $e) {
		// Let original error go through and be displayed
	}
}
//*/

// Exception detail depending on mode
if($content instanceof Exception) {
	$content = "[ERROR] " . $e->getMessage();
	// Show debugging info?
	if($cfg['cx']['debug']) {
		$content .= "<p>File: " . $e->getFile() . " (" . $e->getLine() . ")</p>";
		$content .= "<pre>" . $e->getTraceAsString() . "</pre>";
	}
}

// Send proper response
if($kernel) {
	$kernel->trigger('cx_response_before', array(&$content));
	
	// Set content and send response
	if($responseStatus != 200) {
		$response->status($responseStatus);
	}
	$response->content($content);
	$response->send();
	
	$kernel->trigger('cx_response_after');
	$kernel->trigger('cx_shutdown');
	
	// Debugging on?
	if($kernel->config('cx.debug')) {
		echo "<hr />";
		echo "<pre>";
		print_r($kernel->trace());
		echo "</pre>";	
	}
	
} else {
	header("HTTP/1.0 500 Internal Server Error");
	echo "<h1>Internal Server Error</h1>";
	echo $content;
}