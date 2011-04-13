<?php
namespace Alloy;

/**
 * Router Route
 *
 * @package Alloy
 * @link http://alloyframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Router_Route
{
    protected $_name;
    protected $_route; // Stored route as entered by user
    protected $_isStatic = false;
    protected $_regexp; // Stored compiled regexp for route
    protected $_defaultParams = array();
    protected $_namedParams = array();
    protected $_optionalParams = array();
    protected $_methodParams = array(
        'GET' => array(),
        'POST' => array(),
        'PUT' => array(),
        'DELETE' => array()
        );
    
    // Regex to match route params by name
    protected $_routeParamRegex = "\<[\:|\*|\#]([^\>]+)\>";
    protected $_routeOptionalParamRegex = "\(([^\<]*)\<[\:|\*|\#]([^\>]+)\>([^\)]*)\)";
    
    // Default regex match syntax to replace named keys with
    protected $_paramRegex = "([a-zA-Z0-9\_\-\+\%\s]+)";
    protected $_paramRegexNumeric = "([0-9]+)";
    protected $_paramRegexWildcard = "(.*)";

    // Callbacks
    protected $_condition;
    protected $_afterMatch;
    
    
    /**
     * New router object
     */
    public function __construct($route)
    {
        $routeRegex = null;
        $routeParams = array();
        $routeOptionalParams = array();
        
        // Remove leading and trailing backslashes if present (normalizes for matching)
        $route = trim($route, '/');
        $routeRegex = $route;
        
        // Detect static routes to skip regex overhead
        if(false === strpos($route, '<')) {
            $this->isStatic(true);
        }

        // No regex compile for static routes if we know ahead of time
        if(!$this->isStatic()) {
            // Extract optional named parameters from route
            $regexOptionalMatches = array();
            preg_match_all("@" . $this->_routeOptionalParamRegex . "@", $route, $regexOptionalMatches, PREG_SET_ORDER);
            if(isset($regexOptionalMatches[0]) && count($regexOptionalMatches) > 0) {
                foreach($regexOptionalMatches as $paramMatch) {
                    // Standard regex rule
                    //$routeOptionalParamsMatched[] = $paramName;
                    $routeOptionalParams[$paramMatch[2]] = array(
                        'routeSegment' => $paramMatch[0],
                        'prefix' => $paramMatch[1],
                        'suffix' => $paramMatch[3],
                    );
                    
                    // We don't know what type of route param was supplied, so we have to isolate it by stripping off the parts we know
                    $routeParamToken = substr($paramMatch[0], strlen('(' . $paramMatch[1])); // trim off prefix
                    $routeParamToken = substr($routeParamToken, 0, -strlen($paramMatch[3] . ')')); // trim off suffix
                    
                    // Re-assemble route parts with escaping for prefix and suffix
                    $routeRegex = str_replace($paramMatch[0], "(?:" . preg_quote($paramMatch[1]) . $routeParamToken . preg_quote($paramMatch[3]) . ")?", $routeRegex);
                }
            }
            
            // Extract named parameters from route
            $regexMatches = array();
            preg_match_all("@" . $this->_routeParamRegex . "@", $route, $regexMatches, PREG_PATTERN_ORDER);
            //print_r($regexMatches);
            if(isset($regexMatches[1]) && count($regexMatches[1]) > 0) {
                $routeParamsMatched = array();
                
                // Replace all keys with regex
                foreach($regexMatches[1] as $paramIndex => $paramName) {
                    // Does parameter have marker for custom regex rule?
                    if(strpos($paramName, '|') !== false) {
                        // Custom regex rule
                        $paramParts = explode('|', $paramName);
                        $routeParamsMatched[] = $paramParts[0];
                        $routeRegex = str_replace("<:" . $paramName . ">", "(" . $paramParts[1] . ")", $routeRegex);
                    } else {
                        // Standard regex rule
                        $routeParamsMatched[] = $paramName;
                        $routeRegex = str_replace("<:" . $paramName . ">", $this->_paramRegex, $routeRegex);
                        $routeRegex = str_replace("<#" . $paramName . ">", $this->_paramRegexNumeric, $routeRegex);
                        $routeRegex = str_replace("<*" . $paramName . ">", $this->_paramRegexWildcard, $routeRegex);
                    }
                }
                $routeParams = array_combine($routeParamsMatched, $regexMatches[0]);
            } else {
                $this->_isStatic = true;
            }
            
            // Escape common URL characters
            $routeRegex = str_replace('/', '\/', $routeRegex);
        }
        
        // Save route info
        $this->_route = $route;
        $this->_regexp = "/^" . $routeRegex . "$/";
        $this->_namedParams = $routeParams;
        $this->_optionalParams = $routeOptionalParams;
    }
    
    
    /**
     * Set or return default params for the route
     *
     * @param array $params OPTIONAL Array of key => values to return with route match
     * @return mixed Array or object instance
     */
    public function defaults(array $params = array())
    {
        if(count($params) > 0) {
            $this->_defaultParams = $params;
            return $this;
        }
        return $this->_defaultParams;
    }
    
    
    /**
     * Return named params for the route
     *
     * @return array $params Array of key => values to return with route match
     */
    public function namedParams()
    {
        return $this->_namedParams;
    }
    
    
    /**
     * Return optional named params for the route
     *
     * @return array $params Array of key => values to return with route match
     */
    public function optionalParams()
    {
        return $this->_optionalParams;
    }
    
    
    /**
     * Return optional named params for the route
     *
     * @return array $params Array of key => values to return with route match
     */
    public function optionalParamDefaults()
    {
        $defaultParams = $this->defaults();
        $optionalParamDefaults = array();
        if($this->_optionalParams && count($this->_optionalParams) > 0) {
            foreach($this->_optionalParams as $paramName => $opts) {
                if(isset($defaultParams[$paramName])) {
                    $optionalParamDefaults[$paramName] = $defaultParams[$paramName];
                } else {
                    $optionalParamDefaults[$paramName] = null;
                }
            }
        }
        return $optionalParamDefaults;
    }
    
    
    /**
     * Is this route static?
     *
     * @return boolean
     */
    public function isStatic($static = null)
    {
        if(null !== $static) {
            $this->_isStatic = $static;
        }
        return $this->_isStatic;
    }
    
    
    /**
     * Unique route name
     *
     * @param string $name Unique name for the router object
     * @return object Self
     */
    public function name($name = null)
    {
        if(null === $name) {
            return $this->_name;
        }
        $this->_name = $name;
        return $this;
    }
    
    
    /**
     * User-entered route
     *
     * @return string Route as user entered it
     */
    public function route()
    {
        return $this->_route;
    }
    
    
    /**
     * Compiled route regex
     *
     * @return regexp Regular expression representing route
     */
    public function regexp()
    {
        return $this->_regexp;
    }
    
    
    /**
     * Convenience functions for 'method'
     */
    public function get(array $params)
    {
        return $this->methodDefaults('GET', $params);
    }
    public function post(array $params)
    {
        return $this->methodDefaults('POST', $params);
    }
    public function put(array $params)
    {
        return $this->methodDefaults('PUT', $params);
    }
    public function delete(array $params)
    {
        return $this->methodDefaults('DELETE', $params);
    }
    
    
    /**
     * Set parameters based on request method
     *
     * @param string $method Request method (GET, POST, PUT, DELETE, etc.)
     * @param array $params OPTIONAL Array of key => value parameters to set on route for given request method
     * @return mixed object or array
     */
    public function methodDefaults($method, array $params = array())
    {
        $method = strtoupper($method);
        if(!isset($this->_methodParams[$method])) {
            $this->_methodParams[$method] = $params;
        } else {
            $this->_methodParams[$method] += $params;
        }
        
        if(count($params) == 0) {
            return $this->_methodParams[$method];
        }
        
        return $this;
    }


    /**
     * Condition callback
     *
     * @param callback $callback Callback function to be used when providing custom route match conditions
     * @throws \InvalidArgumentException When supplied argument is not a valid callback
     * @return callback
     */
    public function condition($callback = null)
    {
        // Setter
        if(null !== $callback) {
            if(!is_callable($callback)) {
                throw new \InvalidArgumentException("Condition provided is not a valid callback. Given (" . gettype($callback) . ")");
            }
            $this->_condition = $callback;
            return $this;
        }

        return $this->_condition;
    }


    /**
     * After match callback
     *
     * @param callback $callback Callback function to be used to modify params after a successful match
     * @throws \InvalidArgumentException When supplied argument is not a valid callback
     * @return callback
     */
    public function afterMatch($callback = null)
    {
        // Setter
        if(null !== $callback) {
            if(!is_callable($callback)) {
                throw new \InvalidArgumentException("The after match callback provided is not valid. Given (" . gettype($callback) . ")");
            }
            $this->_afterMatch = $callback;
            return $this;
        }

        return $this->_afterMatch;
    }
}