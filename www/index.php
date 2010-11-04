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
    
    // Required params
    if(isset($params['module']) && isset($params['action'])) {
        $module = $params['module'];
        $action = $params['action'];
        
        // Run/execute
        $content = $kernel->dispatchRequest($module, $action);
    } else {
        $content = false;
    }
    
    // Raise 404 error on boolean false result
    if(false === $content) {
        throw new \Alloy\Exception_FileNotFound("Requested file or page not found. Please check the URL and try again.");
    }
    
    // Wrap returned content in a layout
    if($request->format == 'html' && !$request->isAjax() && !$request->isCli()) {
        
        $pageTitle = '';
        if($content instanceof \Alloy\View\Template) {
            $pageTitle = ($content->title) ? $content->title : '';
        }
        
        $layout = new \Alloy\View\Template('app');
        $layout->path($kernel->config('path.layouts'))
            ->format($request->format)
            ->set(array(
                'title' => $pageTitle,
                'kernel' => $kernel,
                'content' => $content
                ));
        $content = $layout;
        $response->contentType('text/html');
        
    } elseif(in_array($request->format, array('json', 'xml'))) {
        // No cache and hide potential errors
        ini_set('display_errors', 0);
        $response->header("Expires", "Mon, 26 Jul 1997 05:00:00 GMT"); 
        $response->header("Last-Modified", gmdate( "D, d M Y H:i:s" ) . "GMT"); 
        $response->header("Cache-Control", "no-cache, must-revalidate"); 
        $response->header("Pragma", "no-cache");
        
        // Correct content-type
        if('json' == $request->format) {
            $response->contentType('application/json');
        } elseif('xml' == $request->format) {
            $response->contentType('text/xml');
        }
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
        echo "<h1>Executed Queries (" . \Spot\Log::queryCount() . ")</h1>";
        echo "<pre>";
        print_r(\Spot\Log::queries());
        echo "</pre>";
    }

} else {
    header("HTTP/1.0 500 Internal Server Error");
    echo "<h1>Internal Server Error</h1>";
    echo $content;
}