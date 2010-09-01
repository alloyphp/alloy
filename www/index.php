<?php
// Show all errors by default
error_reporting(-1);
ini_set('display_errors', 'On');

// PHP version must be 5.3 or greater
if(version_compare(phpversion(), "5.3.0", "<")) {
	throw new \RuntimeException("PHP version must be 5.3.0 or greater to run Alloy Framework.<br />\nCurrent PHP version: " . phpversion());
}


/**
 * Configuration settings
 */
$cfg = require(dirname(dirname(__FILE__)) . '/app/config/app.php');

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
 * Run
 */
$kernel = false;
try {
    
	// Get Kernel with config and host config
	require $cfg['path']['lib'] . '/Alloy/Kernel.php';
	$kernel = \Alloy\Kernel::getInstance($cfg);
	$kernel->config($cfgHost);
	
	
	/**
     * Class autoloaders - uses PHP 5.3 SplClassLoader
     */
    require $cfg['path']['lib'] . '/Alloy/ClassLoader.php';

    // Autoload: Alloy lib
    $alloyLoader = new \Alloy\ClassLoader('Alloy', $cfg['path']['lib']);
    $alloyLoader->register();

    // Autoload: Modules in app
    $moduleLoader = new \Alloy\ClassLoader('Module', $cfg['path']['app']);
    $moduleLoader->register();
	
	set_error_handler(array($kernel, 'errorHandler'));
	ini_set("session.cookie_httponly", true); // Mitigate XSS javascript cookie attacks for browers that support it
	ini_set("session.use_only_cookies", true); // Don't allow session_id in URLs
	session_start();
	
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
	
	// Global setup based on config settings
	date_default_timezone_set($kernel->config('i18n.timezone', 'America/Chicago'));
	ini_set("session.gc_maxlifetime", $kernel->config('session.lifetime', 28000));
	
	// Initial response code (not sent to browser yet)
	$responseStatus = 200;
	$response = $kernel->response($responseStatus);
	
	// Router - Add routes we want to match
	$router = $kernel->router();
	require $kernel->config('path.config') . '/routes.php';
	
	// Router - Match HTTP request and return named params
	$request = $kernel->request();
	$requestUrl = isset($_GET['u']) ? $_GET['u'] : '/';
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
	
	// Required params
	if(isset($params['module']) && isset($params['action'])) {
		$module = $params['module'];
		$action = $params['action'];
		
		// Run/execute
		$content = $kernel->dispatchRequest($request, $module, $action, array($request));
	} else {
		$content = false;
	}
	
	// Raise 404 error on boolean false result
 	if(false === $content) {
		throw new \Alloy\Exception_FileNotFound("Requested file or page not found. Please check the URL and try again.");
	}
	
// Authentication Error
} catch(\Alloy\Exception_Auth $e) {
	$responseStatus = 403;
	$content = $e;
	$kernel->dispatch('user', 'loginAction', array($request));
 
// 404 Errors
} catch(\Alloy\Exception_FileNotFound $e) {
	$responseStatus = 404;
	$content = $e;

// Method Not Allowed
} catch(\Alloy\Exception_Method $e) {
	$responseStatus = 405; // 405 - Method Not Allowed
	$content = $e;

// HTTP Exception
} catch(\Alloy\Exception_Http $e) {
	$responseStatus = $e->getCode(); 
	$content = $e;

// Module/Action Error
} catch(\Alloy\Exception $e) {
	$responseStatus = 500;
	$content = $e;
 
// Generic Error
} catch(\Exception $e) {
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
	$e = $content;
	$content = "<h1>ERROR</h1><p>" . get_class($e) . " (Code: " . $e->getCode() . ")<br />" . $e->getMessage() . "</p>";
	// Show debugging info?
	if($kernel->config('debug')) {
		$content .= "<p>File: " . $e->getFile() . " (" . $e->getLine() . ")</p>";
		$content .= "<pre>" . $e->getTraceAsString() . "</pre>";
	}
}

// Send proper response
if($kernel) {
	// Set content and send response
	if($responseStatus != 200) {
		$response->status($responseStatus);
	}
	$response->content($content);
	$response->send();
	
	// Debugging on?
	if($kernel->config('debug')) {
		echo "<hr />";
		echo "<pre>";
		print_r($kernel->trace());
		echo "</pre>";
		
		// Executed queries
		echo "<hr />";
		echo "<h1>Executed Queries (" . Spot_Log::queryCount() . ")</h1>";
		echo "<pre>";
		print_r(Spot_Log::queries());
		echo "</pre>";
	}
	
} else {
	header("HTTP/1.0 500 Internal Server Error");
	echo "<h1>Internal Server Error</h1>";
	echo $content;
}
