<?php
namespace Alloy;

/**
 * Router
 *
 * Maps URL to named parameters for use in application
 *
 * @package Alloy
 * @link http://alloyframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Router
{
    // Store specified routes
    protected $_routes = array();
    protected $_matchedRouteName;
    
    
    /**
     * Connect route
     * 
     * @param string $name Name of the route
     * @param string $route Route to match
     * @param optional array $defaults Array of key => value parameters to supply as defaults
     */
    public function route($name, $route, array $defaults = array())
    {
        $route = new Router_Route($route);
        $this->_routes[$name] = $route->name($name);
        return $route;
    }
    
    
    /**
     * Get set routes
     */
    public function routes()
    {
        return $this->_routes;
    }
    
    
    /**
     * Match given URL string
     *
     * @param string $method HTTP Method to match for
     * @param string $url Request URL to match for
     * @return array $params Parameters with values that were matched
     */
    public function match($method, $url)
    {
        if(count($this->routes()) == 0) {
            throw new \OutOfBoundsException("There must be at least one route defined to match for.");
        }
        
        // Clean up URL for matching
        $url = trim($url, '/');
        $params = array();
        
        // Loop over set routes to find a match
        // Order matters - Looping will stop when first suitable match is found
        $routes = $this->routes();
        foreach($routes as $routeName => $route) {
            if($params = $this->routeMatch($route, $method, $url)) {
                // Check condition callback if set
                $cb = $route->condition();
                if(null !== $cb) {
                    // Pass in method, url, and matched params
                    $cbr = call_user_func($cb, $params, $method, $url);
                    // Condition returned false - no match - skip route and clear matched data
                    if(false === $cbr) {
                        $params = array();
                        $this->_matchedRouteName = null;
                        continue;
                    }
                }
                break;
            }
        }
        
        // Run 'afterMatch' callback if one is provided
        if($params) {
            // If we have an after match callback, we can use it to modify params
            $mcb = $route->afterMatch();
            if(null !== $mcb) {
                // Pass in method, url, and matched params
                $params = call_user_func($mcb, $params, $method, $url);
            }
        }

        return $params;
    }
    
    
    /**
     * Match URL against a specific given route
     */
    protected function routeMatch(Router_Route $route, $method, $url)
    {
        $params = array();
        
        // Static route - no PREG overhead
        if($route->isStatic()) {
            $routeUrl = $route->route();
            
            // Match? (already cleaned/trimmed)
            if($routeUrl == $url) {
                // Return defaults + HTTP method params
                $params = array_merge($route->defaults(), $route->methodDefaults($method));
            }
            
            // Store matched route name
            $this->_matchedRouteName = $route->name();
            
        // Match params
        } else {
            $result = preg_match($route->regexp(), $url, $matches);
            if($result) {
                // Store matched route name
                $this->_matchedRouteName = $route->name();
                
                // Shift off first "match" result - full URL input string
                array_shift($matches);
                
                // Only named params, leaving off optionals
                $namedParams = array_merge($route->namedParams(), $route->optionalParamDefaults());
                $namedParamsNotOptional = array_diff_key($namedParams, $route->optionalParamDefaults());
                $namedParamsMatched = $namedParamsNotOptional;
                
                // Equalize matched params, rely on matching order
                // @todo Switch all routes to named captures to avoid this. Man, all these regex woes make my head hurt.
                // @link http://www.regular-expressions.info/named.html
                $namedParamsIndexed = array_keys($namedParams);
                $mi = count($namedParamsNotOptional);
                while(count($matches) > $mi) {
                    $namedParamsMatched[$namedParamsIndexed[$mi]] = $namedParams[$namedParamsIndexed[$mi]];
                    $mi++;
                }
                //var_dump($route->name(), $matches, $namedParamsMatched, $namedParams);
                
                // Combine params
                if(count($namedParamsMatched) != count($matches)) {
                    // Route has inequal matches to named params
                    throw new \InvalidArgumentException("Error matching URL to route params: matched(" . count($matches) . ") != named(" . count($namedParamsMatched) . ")");
                }
                $params = array_combine(array_keys($namedParamsMatched), $matches);
                
                
                if(strtoupper($method) != "GET") {
                    // Default REST behavior is to be 'greedy' and always use the REST method defaults if supplied
                    $params = array_merge($route->namedParams(), $route->defaults(), $params, $route->methodDefaults($method));
                } else {
                    $params = array_merge($route->namedParams(), $route->defaults(), $route->methodDefaults($method), $params);
                }
            }
        }
        return array_map('urldecode', $params);
    }
    
    
    /**
     * Return last matched route
     */
    public function matchedRoute()
    {
        if($this->_matchedRouteName) {
            return $this->_routes[$this->_matchedRouteName];
        } else {
            throw new \LogicException("Unable to return last route matched - No route has been matched yet.");
        }
    }
    
    
    /**
     * Put a URL together by matching route name and params
     *
     * @param array $params Array of key => value params to fill in for given route
     * @param string $routeName Name of the route previously defined
     * 
     * @return string Full matched URL as string with given values put in place of named parameters
     * @throws UnexpectedValueException For non-existent route name or params that don't match given route name (Unable to create URL string)
     */
    public function url($params = array(), $routeName = null)
    {
        // If params is string, assume route name for static route
        if(null === $routeName && is_string($params)) {
            $routeName = $params;
            $params = array();
        }
        
        if(!$routeName) {
            throw new \UnexpectedValueException("Error creating URL: Route name must be specified.");
        }
        
        if(!isset($this->_routes[$routeName])) {
            throw new \UnexpectedValueException("Error creating URL: Route name '" . $routeName . "' not found in defined routes.");
        }
        
        $routeUrl = "";
        $route = $this->_routes[$routeName];
        $routeUrl = $route->route();
        
        // Static routes - let's save some time here
        if($route->isStatic()) {
            return $routeUrl;
        }
        
        
        $routeDefaults = $route->defaults();
        $routeParams = array_merge($routeDefaults, $route->namedParams());
        $optionalParams = $route->optionalParams();
        
        // Match all params on route that do not have defaults
        $matchedParams = $routeDefaults; // Begin with defaults
        foreach(array_merge($matchedParams, $params) as $key => $value) {
            // Optional params
            if(isset($optionalParams[$key])) {
                // If no given value, or given value is the same as default, set value to empty
                if((isset($routeDefaults[$key]) && !isset($params[$key]))) {
                    $matchedParams[$key] = '';
                } else {
                    $matchedParams[$key] = $optionalParams[$key]['prefix'] . $value . $optionalParams[$key]['suffix'];
                }
                $routeParams[$key] = $optionalParams[$key]['routeSegment'];
            // Required/standard param
            } elseif(isset($routeParams[$key])) {
                $matchedParams[$key] = $value;
            }
        }
        
        //var_dump($matchedParams);
        
        // Ensure all params have been matched, exception if not
        if(count(array_diff_key($matchedParams, $routeParams)) > 0) {
            throw new \UnexpectedValueException("Error creating URL: Route '" . $routeName . "' has parameters that have not been matched.");
        }
        
        // Fill in values and put URL together
        foreach($routeParams as $paramName => $paramPlaceholder) {
            if(!isset($matchedParams[$paramName])) {
                throw new \UnexpectedValueException("Error creating URL for route '" . $routeName . "': Required route parameter '" . $paramName . "' has not been supplied.");
            }
            $routeUrl = str_replace($paramPlaceholder, urlencode($matchedParams[$paramName]), $routeUrl);
        }
        
        // Remove all optional parameters with no supplied match or default value
        foreach($optionalParams as $param) {
            $routeUrl = str_replace($param['routeSegment'], '', $routeUrl);
        }
        
        // Ensure escaping characters are removed
        $routeUrl = str_replace('\\', '', $routeUrl);
        
        return $routeUrl;
    }
    
    
    /**
     * Clear existing routes to start over
     */
    public function reset()
    {
        $this->_routes = array();
    }
}