<?php
require dirname(__DIR__) . '/app/init.php';


/**
 * Run
 */
try {
    // Error and session setup
    set_error_handler(array($kernel, 'errorHandler'));
    ini_set("session.cookie_httponly", true); // Mitigate XSS javascript cookie attacks for browers that support it
    ini_set("session.use_only_cookies", true); // Don't allow session_id in URLs
    session_start();
    
    // Global setup based on config settings
    date_default_timezone_set($kernel->config('i18n.timezone', 'America/Chicago'));
    ini_set("session.gc_maxlifetime", $kernel->config('session.lifetime', 28000));
    
    // Initial response code (not sent to browser yet)
    $responseStatus = 200;
    $response = $kernel->response($responseStatus);
    
    // Router - Add routes we want to match
    $router = $kernel->router();
    require $kernel->config('path.config') . '/routes.php';
    
    // Handle both HTTP and CLI requests
    $request = $kernel->request();
    if($request->isCli()) {
        // CLI request
        $cliArgs = getopt("u:");
        
        $requestUrl = isset($cliArgs['u']) ? $cliArgs['u'] : '/';
        $qs = parse_url($requestUrl, PHP_URL_QUERY);
        $cliRequestParams = $request->queryStringToArray($qs);
        
        // Set parsed query params back on request object
        $request->setParams($cliRequestParams);
        
        // Set requestUrl and remove query string if present so router can parse it as expected
        if($qsPos = strpos($requestUrl, '?')) {
            $requestUrl = substr($requestUrl, 0, $qsPos);
        }
        
    } else {
        // HTTP request
        $requestUrl = isset($_GET['u']) ? $_GET['u'] : '/';
    }
    
    // Router - Match HTTP request and return named params
    $requestMethod = $request->method();
    // Emulate REST for browsers
    if($request->isPost() && $request->post('_method')) {
        $requestMethod = $request->post('_method');
    }
    $params = $router->match($requestMethod, $requestUrl);
    
    // Set matched params back on request object
    $request->setParams($params);
    $request->route = $router->matchedRoute()->name();
    
    // Load plugins
    if($plugins = $kernel->config('plugins', false)) {
        if(!is_array($plugins)) {
            throw new \InvalidArgumentException("Plugin configuration from app config must be an array. Given (" . gettype($plugins) . ").");
        }

        foreach($plugins as $pluginName) {
            $plugin = $kernel->plugin($pluginName);
            if(false === $plugin) {
                throw new \Exception("Unable to load plugin '" . $pluginName . "'. Remove from app config or ensure plugin files exist in 'app' or 'vendor' load paths.");
            }
        }
    }
    $kernel->events()->trigger('plugins_loaded');

    // Required params
    $content = false;
    if(isset($params['module']) && isset($params['action'])) {
        $request->module = $params['module'];
        $request->action = $params['action'];
        
        // Matched route
        $kernel->events()->trigger('route_match');
    } else {
        $content = $kernel->events()->filter('route_not_found', $content);
    }

    // Run/execute
    $content = $kernel->dispatchRequest($request->module, $request->action);
    
    // Raise 404 error on boolean false result
    if(false === $content) {
        throw new \Alloy\Exception_FileNotFound("Requested file or page not found. Please check the URL and try again.");
    }

// Authentication Error
} catch(\Alloy\Exception_Auth $e) {
    $responseStatus = 403;
    $content = $e;
 
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

// Run resulting content through filter
$content = $kernel->events()->filter('dispatch_content', $content);

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
    }

    // Notify that response has been sent
    $kernel->events()->trigger('response_sent');

} else {
    header("HTTP/1.0 500 Internal Server Error");
    echo "<h1>Internal Server Error</h1>";
    echo $content;
}