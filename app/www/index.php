<?php
// Save current directory as full path to www
define('ALLOY_WEB_ROOT', __DIR__);

// Require app init (inital framework setup)
require dirname(__DIR__) . '/init.php';


/**
 * Run
 */
try {
    // Error and session setup
    set_error_handler(array($kernel, 'errorHandler'));
    ini_set("session.cookie_httponly", true); // Mitigate XSS javascript cookie attacks for browers that support it
    ini_set("session.use_only_cookies", true); // Don't allow session_id in URLs
    session_start();
    
    // Load plugins
    if($plugins = $kernel->config('app.plugins', false)) {
        if(!is_array($plugins)) {
            throw new \InvalidArgumentException("Plugin configuration from app config must be an array. Given (" . gettype($plugins) . ").");
        }

        foreach($plugins as $pluginName) {
            $plugin = $kernel->plugin($pluginName);
        }
    }
    $kernel->events()->trigger('boot_start');
    
    // Global setup based on config settings
    date_default_timezone_set($kernel->config('app.i18n.timezone', 'America/Chicago'));
    ini_set("session.gc_maxlifetime", $kernel->config('app.session.lifetime', 28000));
    
    // Initial response code (not sent to browser yet)
    $responseStatus = 200;
    $response = $kernel->response($responseStatus);
    
    // Router - Add routes we want to match
    $router = $kernel->router();
    require $kernel->config('app.path.config') . '/routes.php';
    
    // Handle both HTTP and CLI requests
    $request = $kernel->request();
    if($request->isCli()) {
        // CLI request
        $cliArgs = getopt("u:");
        
        $requestUrl = isset($cliArgs['u']) ? $cliArgs['u'] : '/';
        $qs = parse_url($requestUrl, PHP_URL_QUERY);
        $cliRequestParams = array();
        parse_str($qs, $cliRequestParams);
        
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
    $params = $router->match($requestMethod, $requestUrl);
    
    // Set matched params back on request object
    $request->setParams($params);
    $request->route = $router->matchedRoute()->name();

    // Required params
    $content = false;
    if(isset($params['module']) && isset($params['action'])) {
        $request->module = $params['module'];
        $request->action = $params['action'];
        
        // Matched route
        $kernel->events()->trigger('route_match');

        // Run/execute
        $content = $kernel->dispatchRequest($request->module, $request->action);
    } else {
        $content = $kernel->events()->filter('route_not_found', $content);
    }
    
    // Raise 404 error on boolean false result
    if(false === $content) {
        throw new \Alloy\Exception\FileNotFound("Requested file or page not found. Please check the URL and try again.");
    }

    // Run resulting content through filter
    $content = $kernel->events()->filter('dispatch_content', $content);

    // Explicitly convert response to string so Exceptions won't get caught in __toString method
    if($content instanceof Alloy\Module\Response) {
        $responseStatus = $content->status();
        $content = $content->content();
    }

// Authentication Error
} catch(\Alloy\Exception\Auth $e) {
    $responseStatus = 403;
    $content = $e;
 
// 404 Errors
} catch(\Alloy\Exception\FileNotFound $e) {
    $responseStatus = 404;
    $content = $e;

// Method Not Allowed
} catch(\Alloy\Exception\Method $e) {
    $responseStatus = 405; // 405 - Method Not Allowed
    $content = $e;

// HTTP Exception
} catch(\Alloy\Exception\Http $e) {
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


// Exception detail depending on mode
if($content instanceof \Exception) {

    // Filter to give a chance for Plugins to handle error
    $content = $kernel->events()->filter('dispatch_exception', $content);

    // Content still an exception, default display
    if($content instanceof \Exception) {
        $e = $content;
        $content = "<h1>ERROR</h1>";
        $content .= "<p>" . get_class($e) . " (Code: " . $e->getCode() . ")<br />\n";
        $content .= $e->getMessage() . "</p>";

        // Show debugging info
        if($kernel && ($kernel->config('app.debug') || $kernel->config('app.mode.development'))) {
            $content .= "<h2>Stack Trace</h2>";
            $content .= "<p>File: " . $e->getFile() . " (" . $e->getLine() . ")</p>\n";
            $content .= "<pre>" . $e->getTraceAsString() . "</pre>\n";

            // Request Data
            $content .= "<h2>Request Data</h2>";
            $content .= $kernel->dump($request->params());
        }
    }
}

// Send proper response
if($kernel) {
    $response = $kernel->response();
    
    // Set content and send response
    $response->status($responseStatus);
    $response->content($content);
    $response->send();
    
    // Debugging on?
    if($kernel->config('app.debug')) {
        echo "<hr />";
        echo "<h2>Event Trace</h2>";
        echo $kernel->dump($kernel->trace());
    }

    // Notify that response has been sent
    $kernel->events()->trigger('response_sent');

    // Notify events of shutdown
    $kernel->events()->trigger('boot_stop');

} else {
    header("HTTP/1.0 500 Internal Server Error");
    echo "<h1>Internal Server Error</h1>";
    echo $content;
}