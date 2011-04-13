<?php
namespace Alloy;

/**
* Framework Application Kernel
* 
* Functions:
*   - Service Locator/Registry for object storage and central access
*   - Configuration storage and retrieval application-wide
*   - Extendable to create new functionality at runtime
*
* @package Alloy
* @link http://alloyframework.com/
* @license http://www.opensource.org/licenses/bsd-license.php
*/
class Kernel
{
    protected static $self;
    
    protected static $cfg = array();
    protected static $trace = array();
    protected static $traceMemoryStart = 0;
    protected static $traceMemoryLast = 0;
    protected static $traceTimeStart = 0;
    protected static $traceTimeLast = 0;
    protected static $debug = false;
    
    protected $lastDispatch = array();
    protected $instances = array();
    protected $callbacks = array();
    
    
    /**
     * Returns an instance of class itself
     * 
     * @param array $config Array of configuration settings to load
     */
    public static function getInstance(array $config = array())
    {
            if(!is_object(static::$self)) {
                    $className = get_called_class();
                    static::$self = new $className($config);
            } else {
                    // Add new config settings if given
                    if(is_array($config) && count($config) > 0) {
                            static::$self->config($config);
                    }
            }
            
            return static::$self;
    }
    
    
    /**
     * Protected constructor to enforce singleton pattern
     *
     * @param array $config Array of configuration settings to store. Passed array will be stored as a static variable and automatically merged with previous stored settings
     */
    protected function __construct(array $config = array())
    {
        $this->config($config);
        
        // Save memory starting point
        static::$traceMemoryStart = memory_get_usage();
        static::$traceTimeStart = microtime(true);
        // Set last as current starting for good zero-base
        static::$traceMemoryLast = static::$traceMemoryStart;
        static::$traceTimeLast = static::$traceTimeStart;
    }
    
    
    /**
     * Return configuration value
     * 
     * @param mixed $value If string: Value key to search for, If array: Merge given array over current config settings
     * @param string $default Default value to return if $value not found
     * @return string
     */
    public function config($value = null, $default = false)
    {
        // Setter
        if(is_array($value)) {
            if(count($value) > 0) {
                // Merge given config settings over any previous ones (if $value is array)
                static::$cfg = $this->array_merge_recursive_replace(static::$cfg, $value);
            }
        // Getter
        } else {
            // Config array is static to persist across multiple instances
            $cfg = static::$cfg;
            
            // No value passed - return entire config array
            if($value === null) { return $cfg; }
            
            // Find value to return
            if(strpos($value, '.') !== false) {
                $cfgValue = $cfg;
                $valueParts = explode('.', $value);
                foreach($valueParts as $valuePart) {
                    if(isset($cfgValue[$valuePart])) {
                        $cfgValue = $cfgValue[$valuePart];
                    } else {
                        $cfgValue = $default;
                    }
                }
            } else {
                $cfgValue = $cfg;
                if(isset($cfgValue[$value])) {
                    $cfgValue = $cfgValue[$value];
                } else {
                    $cfgValue = $default;
                }
            }
            
            return $cfgValue;
        }
    }
    
    
    /**
     * Factory method for loading and instantiating new objects
     *
     * @param string $className Name of the class to attempt to load
     * @param array $params Array of parameters to pass to instantiate new object with
     * @return object Instance of the class requested
     * @throws InvalidArgumentException
     */
    public function factory($className, array $params = array())
    {
        $instanceHash = md5($className . var_export($params, true));
        
        // Return already instantiated object instance if set
        if(isset($this->instances[$instanceHash])) {
            return $this->instances[$instanceHash];
        }
        
        // Return new class instance
        // Reflection is known for incurring overhead - hack to avoid it if we can
        $paramCount = count($params);
        if(0 === $paramCount) {
            $instance = new $className();
        } elseif(1 === $paramCount) {
            $instance = new $className(current($params));
        } elseif(2 === $paramCount) {
            $instance = new $className($params[0], $params[1]);
        } elseif(3 === $paramCount) {
            $instance = new $className($params[0], $params[1], $params[2]);
        } else {
            $class = new \ReflectionClass($className);
            $instance = $class->newInstanceArgs($args);
        }
        
        return $this->setInstance($instanceHash, $instance);
    }
    
    
    /**
     * Class-level object instance cache
     * Note: This function does not check if $class is currently in use or already instantiated.
     *  This will override any previous instances of $class within the $instances array.
     *
     * @param $hash string Hash or name of the object instance to cache
     * @param $instance object Instance of object you wish to cache
     * @return object
     */
    public function setInstance($hash, $instance)
    {
        $this->instances[$hash] = $instance;
        return $instance;
    }
    
    
    /**
     * Custom error-handling function for failed file include error surpression
     */
    protected function throwError($errno, $errstr, $errfile, $errline)
    {
        $msg = "";
        switch ($errno) {
            case E_USER_ERROR:
            case E_USER_WARNING:
                $msg .= "<b>ERROR</b> [$errno] $errstr<br />\n";
                $msg .= "  Fatal error on line $errline in file $errfile";
                $msg .= ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                $msg .= "Aborting...<br />\n";
                throw new Exception($msg);
                //exit(1);
            break;

            case E_USER_NOTICE:
            default:
        }
        
        // Don't execute PHP internal error handler
        return true;
    }
    
    
    /**
     * Get router object
     */
    public function router()
    {
        return $this->factory(__NAMESPACE__ . '\Router');
    }
    
    
    /**
     * Get request object
     */
    public function request()
    {
        return $this->factory(__NAMESPACE__ . '\Request');
    }
    
    
    /**
     * Get HTTP REST client
     */
    public function client()
    {
        return $this->factory(__NAMESPACE__ . '\Client');
    }
    
    
    /**
     * Send HTTP response header
     * 
     * @param int $statusCode HTTP status code to set
     */
    public function response($statusCode = null)
    {
        $response = $this->factory(__NAMESPACE__ . '\Response');
        if(is_numeric($statusCode)) {
            $response->status($statusCode);
        }
        return $response;
    }
    
    
    /**
     * Get class loader object
     */
    public function loader()
    {
        $class = __NAMESPACE__ . '\ClassLoader';
        if(!class_exists($class, false)) {
            require(__DIR__ . '/ClassLoader.php');
        }
        return $this->factory($class);
    }
    
    
    /**
     * Return a resource object to work with
     */
    public function resource($data = array())
    {
        return new Resource($data);
    }
    
    
    /**
     * Return a session object to work with
     */
    public function session()
    {
        return $this->factory(__NAMESPACE__ . '\Session');
    }
    
    
    /**
     * Get events object with given namespace
     *
     * @param string Event namespace (default is 'alloy')
     */
    public function events($ns = 'alloy')
    {
        return $this->factory(__NAMESPACE__ . '\Events', array($ns));
    }
    
    
    /**
     * Send HTTP 302 redirect
     * 
     * @param string $url URL to redirect to
     */
    public function redirect($url)
    {
        header("Location: " . $url);
        exit();
    }
    
    
    /**
     * Trace messages and return message stack
     * Used for debugging and performance monitoring
     *
     * @param string $msg Log Message
     * @param array $data Arroy of any data to log that is related to the message
     * @param string $function Function or Class::Method call
     * @param string $file File path where message originated from
     * @param int $line Line of the file where message originated
     * @return array Message stack
     */
    public function trace($msg = null, $data = array(), $function = null, $file = null, $line = null, $internal = false)
    {
        // Don't incur the overhead if not in debug mode
        if(!static::$debug) {
            return false;
        }
        
        // Build log entry
        if(null !== $msg) {
            $entry = array(
                'message' => $msg,
                'data' => $data,
                'function' => $function,
                'file' => $file,
                'line' => $line,
                'internal' => (int) $internal,
                );
            // Only log time & memory for non-internal method calls
            if(!$internal) {
                $currentTime = microtime(true);
                $currentMemory = memory_get_usage();
                $entry += array(
                    'time' => ($currentTime - static::$traceTimeLast),
                    'time_total' => $currentTime - static::$traceTimeStart,
                    'memory' => $currentMemory - static::$traceMemoryLast,
                    'memory_total' => $currentMemory - static::$traceMemoryStart
                    );
                // Store as last run
                static::$traceTimeLast = $currentTime;
                static::$traceMemoryLast = $currentMemory;
            }
            static::$trace[] = $entry;
        }
        
        return static::$trace;
    }
    
    
    /**
     * Internal message/action trace - to separate the internal function calls from the stack
     */
    protected function traceInternal($msg = null, $data = array(), $function = null, $file = null, $line = null)
    {
        // Don't incur the overhead if not in debug mode
        if(!static::$debug) {
                return false;
        }
        
        // Log with last parameter as 'true' - last param will always be internal marker
        return $this->trace($msg, $data, $function, $file, $line, true);
    }
    
    
    /**
     * Load and return instantiated module class
     *
     * @param string $module Name of the module class
     * @return object
     */
    public function module($module, $init = true, $dispatchAction = null)
    {
        // Clean module name to prevent possible security vulnerabilities
        $sModule = preg_replace('/[^a-zA-Z0-9_]/', '', $module);
        
        // Upper-case beginning of each word
        $sModule = str_replace(' ', '\\', ucwords(str_replace('_', ' ', $sModule)));
        $sModuleClass = 'Module\\' . $sModule . '\Controller';
        
        // Ensure class exists / can be loaded
        if(!class_exists($sModuleClass)) {
            return false;
        }
        
        // Instantiate module class
        $sModuleObject = new $sModuleClass($this);
        
        // Run init() setup only if supported
        if(true === $init) {
            if(method_exists($sModuleObject, 'init')) {
                $sModuleObject->init($dispatchAction);
            }
        }
        
        return $sModuleObject;
    }


    /**
     * Load and return instantiated plugin class
     *
     * @param string $plugin Name of the plugin to get the instance for
     * @throws \InvalidArgumentException When plugin is not found by name
     * @return object
     */
    public function plugin($plugin, $init = true)
    {
        // Module plugin
        //   ex: 'Module\User'
        if(false !== strpos($plugin, 'Module\\')) {
            $sPluginClass = $plugin . '\Plugin';

        // Named plugin
        //   ex: 'Spot'
        } else {
            // Upper-case beginning of each word
            $sPlugin = str_replace(' ', '\\', ucwords(str_replace('_', ' ', $plugin)));
            $sPluginClass = 'Plugin\\' . $sPlugin . '\Plugin';
        }
        
        // Ensure class exists / can be loaded
        if(!class_exists($sPluginClass, (boolean)$init)) {
            if ($init) {
                throw new \InvalidArgumentException("Unable to load plugin '" . $sPluginClass . "'. Remove from app config or ensure plugin files exist in 'app' or 'alloy' load paths.");
            }

            return false;
        }
        
        // Instantiate module class
        $sPluginObject = new $sPluginClass($this);
        return $sPluginObject;
    }
    
    
    /**
     * Dispatch module action
     *
     * @param string $moduleName Name of module to be called
     * @param optional string $action function name to call on module
     * @param optional array $params parameters to pass to module function
     *
     * @return mixed String or object that has __toString method
     */
    public function dispatch($module, $action = 'index', array $params = array())
    {
        if($module instanceof \Alloy\Module\ControllerAbstract) {
            // Use current module instance
            $sModuleObject = $module;
        } else {
            // Get module instance
            $sModuleObject = $this->module($module, true, $action);
            
            // Does module exist?
            if(false === $sModuleObject) {
                throw new Exception\FileNotFound("Module '" . $module ."' not found");
            }
        }

        // Store last dispatch request info
        $this->lastDispatch = array(
            'module' => $module,
            'action' => $action,
            'params' => $params
        );
        
        // Module action callable (includes __call magic function if method missing)?
        if(!is_callable(array($sModuleObject, $action))) {
            throw new Exception\FileNotFound("Module '" . $module ."' does not have a callable method '" . $action . "'");
        }

        // Handle result
        $result = call_user_func_array(array($sModuleObject, $action), $params);
        
        return $result;
    }
    
    
    /**
     * Dispatch module action from HTTP request
     * Automatically limits call scope by appending 'Action' or 'Method' to module actions
     *
     * @param string $moduleName Name of module to be called
     * @param optional string $action function name to call on module
     * @param optional array $params parameters to merge onto Request object
     *
     * @return mixed String or object that has __toString method
     */
    public function dispatchRequest($module, $action = 'indexAction', array $params = array())
    {
        $request = $this->request();
        $requestMethod = $request->method();
        
        // Append 'Action' or 'Method'
        if(strtolower($requestMethod) == strtolower($action)) {
            $action = $action . (false === strpos($action, 'Method') ? 'Method' : ''); // Append with 'Method' to limit scope to REST access only
        } else {
            $action = $action . (false === strpos($action, 'Action') ? 'Action' : ''); // Append with 'Action' to limit scope of available functions from HTTP request
        }
        
        // Set params on Request object
        $request->setParams($params);
        
        // Run normal dispatch
        return $this->dispatch($module, $action, array($request));
    }


    /**
     * Get information about the last dispatch request executed
     *
     * @return array Dispatch information
     */
    public function lastDispatch()
    {
        return $this->lastDispatch;
    }
    
    
    /**
     * Generate URL from given params
     *
     * @param array $params Named URL parts
     * @param array $queryParams Named querystring URL parts (optional)
     * @return string
     */
    public function url($params = array(), $routeName = null, $queryParams = array(), $qsAppend = false)
    {
        $urlBase = $this->config('url.root', '');
        
        // HTTPS Secure URL?
        $isSecure = false;
        if(isset($params['secure']) && true === $params['secure']) {
            $isSecure = true;
            $urlBase = str_replace('http:', 'https:', $urlBase);
            unset($params['secure']);
        }
        
        // Detemine what URL is from param types
        if(is_string($params)) {
            $routeName = $params;
            $params = array();
        } elseif(!is_array($params)) {
            throw new Exception("First parameter of URL must be array or string route name");
        }
        
        
        // Is there query string data?
        $queryString = "";
        $request = $this->request();
        if(true === $qsAppend && $request->query()) {
            $queryParams = array_merge($request->query(), $queryParams);
        }
        if(count($queryParams) > 0) {
            // Build query string from array $qsData
            $queryString = http_build_query($queryParams, '', '&amp;');
        } else {
            $queryString = false;
        }
        
        // Get URL from router object by reverse match
        $url = str_replace('%2f', '/', strtolower($this->router()->url($params, $routeName)));
        
        // Use query string if URL rewriting is not enabled
        if($this->config('url.rewrite')) {
            $url = $urlBase . $url . (($queryString !== false) ? '?' . $queryString : '');
        } else {
            $url = $urlBase . '?u=' . $url . (($queryString !== false) ? '&amp;' . $queryString : '');
        }
        
        // Return fully assembled URL
        return $url;
    }
    
    
    /**
     * Truncates a string to a certian length & adds a "..." to the end
     *
     * @param string $string
     * @return string
     */
    public function truncate($string, $endlength="30", $end="...")
    {
        $strlen = strlen($string);
        if($strlen > $endlength) {
            $trim = $endlength-$strlen;
            $string = substr($string, 0, $trim); 
            $string .= $end;
        }
        return $string;
    }
    
    
    /**
     * Converts underscores to spaces and capitalizes first letter of each word
     *
     * @param string $word
     * @return string
     */
    public function formatUnderscoreWord($word)
    {
        return ucwords(str_replace('_', ' ', $word));
    }
    
    
    /**
     * Format given string to valid URL string
     *
     * @param string $url
     * @return string URL-safe string
     */
    public function formatUrl($string)
    {
        // Allow only alphanumerics, underscores and dashes
        $string = preg_replace('/([^a-zA-Z0-9_\-]+)/', '-', strtolower($string));
        // Replace extra spaces and dashes with single dash
        $string = preg_replace('/\s+/', '-', $string);
        $string = preg_replace('|-+|', '-', $string);
        // Trim extra dashes from beginning and end
        $string = trim($string, '-');

        return $string;
    }

    
    /**
     * Filesize Calculating function
     * Retuns the size of a file in a "human" format
     * 
     * @param int $size Filesize in bytes
     * @return string Calculated filesize with units (ex. "4.58 MB")
     */
    public function formatFilesize($size)
    {
        $kb=1024;
        $mb=1048576;
        $gb=1073741824;
        $tb=1099511627776;
    
        if($size < $kb) {
            return $size." B";
        } else if($size < $mb) {
            return round($size/$kb,2)." KB";
        } else if($size < $gb) {
            return round($size/$mb,2)." MB";
        } else if($size < $tb) {
            return round($size/$gb,2)." GB";
        } else {
            return round($size/$tb,2)." TB";
        }
    }


    /**
     * Generate random string
     * 
     * @param int $length Character length of returned random string
     * @return string Random string generated
     */
    public function randomString($length = 32)
    {
        $string = "";
        $possible = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789`~!@#$%^&*()-_+=";
        for($i=0;$i < $length;$i++) {
            $char = $possible[mt_rand(0, strlen($possible)-1)];
            $string .= $char;
        }
        return $string;
    }
    
    
    /**
     * Convert to useful array style from HTML form input style
     * Useful for matching up input arrays without having to increment a number in field names
     * 
     * Input an array like this:
     * [name]	=>	[0] => "Google"
     *				[1] => "Yahoo!"
     * [url]	=>	[0] => "http://www.google.com"
     *				[1] => "http://www.yahoo.com"
     *
     * And you will get this:
     * [0]	=>	[name] => "Google"
     *			[title] => "http://www.google.com"
     * [1]	=>	[name] => "Yahoo!"
     *			[title] => "http://www.yahoo.com"
     *
     * @param array $input
     * @return array
     */
    public function arrayFlipConvert(array $input)
    {
        $output = array();
        foreach($input as $key => $val) {
            foreach($val as $key2 => $val2) {
                $output[$key2][$key] = $val2;
            }
        }
        return $output;
    }


    /**
     * Merges any number of arrays of any dimensions, the later overwriting
     * previous keys, unless the key is numeric, in whitch case, duplicated
     * values will not be added.
     *
     * The arrays to be merged are passed as arguments to the function.
     *
     * @return array Resulting array, once all have been merged
     */
    public function array_merge_recursive_replace() {
     // Holds all the arrays passed
        $params =  func_get_args();
   
        // First array is used as the base, everything else overwrites on it
        $return = array_shift($params);
   
        // Merge all arrays on the first array
        foreach ($params as $array) {
            foreach($array as $key => $value) {
                // Numeric keyed values are added (unless already there)
                if(is_numeric($key) && (!in_array($value, $return))) {
                    if(is_array($value)) {
                        $return[] = $this->array_merge_recursive_replace($return[$key], $value);
                    } else {
                        $return[] = $value;
                    }
                      
                // String keyed values are replaced
                } else {
                    if (isset($return[$key]) && is_array($value) && is_array($return[$key])) {
                        $return[$key] = $this->array_merge_recursive_replace($return[$key], $value);
                    } else {
                        $return[$key] = $value;
                    }
                }
            }
        }
   
        return $return;
    }
    
    
    /**
     * Custom error reporting
     */
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $errorMsg = $errstr . " (Line: " . $errline . ")";
        if($errno != E_WARNING && $errno != E_NOTICE && $errno != E_STRICT) {
            throw new Exception($errorMsg, $errno);
        } else {
            return false; // Let PHP handle it
        }
    }
    
    
    /**
     * Debug mode switch
     */
    public function debug($switch = true)
    {
        static::$debug = (boolean) $switch;
        return $this->trace();
    }
    
    
    /**
     * Print out an array or object contents in preformatted text
     * Useful for debugging and quickly determining contents of variables
     */
    public function dump()
    {
        $objects = func_get_args();
        $content = "\n<pre>\n";
        foreach($objects as $object) {
            $content .= print_r($object, true);
        }
        return $content . "\n</pre>\n";
    }
    
    
    /**
     * Add a custom user method via PHP5.3 closure or PHP callback
     */
    public function addMethod($method, $callback)
    {
        $this->traceInternal("Added '" . $method . "' to " . __METHOD__ . "()");
        $this->callbacks[$method] = $callback;
    }
    
    
    /**
     * Run user-added callback
     * @throws BadMethodCallException
     */
    public function __call($method, $args)
    {
        if(isset($this->callbacks[$method]) && is_callable($this->callbacks[$method])) {
            $this->trace("Calling custom method '" . $method . "' added with " . __CLASS__ . "::addMethod()", $args);
            $callback = $this->callbacks[$method];
            return call_user_func_array($callback, $args);
        } else {
            throw new \BadMethodCallException("Method '" . __CLASS__ . "::" . $method . "' not found or the command is not a valid callback type.");	
        }
    }
}