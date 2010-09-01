<?php
namespace Alloy;

/**
 * Framework Application Kernel
 * 
 * Functions:
 * 	- Service Locator/Registry for object storage and central access
 * 	- Configuration storage and retrieval application-wide
 * 	- Extendable to create new functionality at runtime
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
	
	protected $instances = array();
	protected $loaded = array();
	protected $loadPaths = array();
	protected $callbacks = array();
	
	protected $binds = array();
	protected $_user = false;
	
	protected $_spotConfig;
	protected $_spotMapper = array();
	
	
	/**
 	 * Returns an instance of class itself
	 * 
	 * @param array $config Array of configuration settings to load
	 */
	public static function getInstance(array $config = array())
	{
		if(!is_object(self::$self)) {
			$className = __CLASS__;
			self::$self = new $className($config);
		} else {
			// Add new config settings if given
			if(is_array($config) && count($config) > 0) {
				self::$self->config($config);
			}
		}
		
		return self::$self;
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
		self::$traceMemoryStart = memory_get_usage();
		self::$traceTimeStart = microtime(true);
		// Set last as current starting for good zero-base
		self::$traceMemoryLast = self::$traceMemoryStart;
		self::$traceTimeLast = self::$traceTimeStart;
	}
	
	
	/**
	 * Return configuration value
	 * 
	 * @param mixed $value If string: Value key to search for, If array: Merge given array over current config settings
	 * @param string $default Default value to return if $value not found
	 * @return string
	 */
	public function config($value = null, $default = false) {
		// Setter
		if(is_array($value)) {
			if(count($value) > 0) {
				// Merge given config settings over any previous ones (if $value is array)
				self::$cfg = $this->array_merge_recursive_replace(self::$cfg, $value);
			}
		// Getter
		} else {
			// Config array is static to persist across multiple instances
			$cfg = self::$cfg;
			
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
	 * @return object Instance of the class requested
	 * @throws InvalidArgumentException
	 */
	public function factory($className, array $args = array())
	{
		if(isset($this->instances[$className])) {
			return $this->instances[$className];
		}
		
		// Return new class instance
		if(count($args) == 0) {
			$instance = new $className();
		} else {
			$class = new ReflectionClass($className);
			$instance = $class->newInstanceArgs($args);
		}
		
		return $this->setInstance($className, $instance);
	}
	
	
	/**
	 *	Sets instance of specified class
	 *	Note: This function does not check if $class is currently in use or already instantiated.
	 *		This will override any previous instances of $class within the $instances array.
	 *
	 *	@param $class string	Name of the class you wish to locate
	 *	@param $instance object	Instance of class you wish to set in locator
	 *	@return object
	 */
	public function setInstance($class, $instance)
	{
		$this->instances[$class] = $instance;
		return $instance;
	}
	
	
	/**
	 * Add path for class loader to look in
	 *
	 * @param string $path Full root path to start looking for files in
	 * @param optional string $prefix Classname prefix condition (ex. 'Zend_' or 'sf')
	 */
	public function addLoadPath($path, $prefix = '*')
	{
		$this->loadPaths[$prefix][] = $path;
	}
	 
	 
	/**
	 * Return array of set class paths
	 * 
	 * @param optional string $prefix Classname prefix to return paths for (ex. 'Zend_' or 'sf')
	 * @return array
	 */
	public function loadPaths($prefix = '*')
	{
		return isset($this->loadPaths[$prefix]) ? $this->loadPaths[$prefix] : array();
	}
	
	
	/**
	 * Load (include) class file
	 *
	 * @param $className string	Name of the class
	 * @param $path mixed(string|array)	Path(s) to look in to load class file
	 * @return boolean
	 */
	public function load($className, $paths = array())
	{
	    var_dump($className);
	    exit(__FILE__);
	    
		// Ensure class is not already loaded
		if(class_exists($className, false)) {
			return true;
		}
		
		// Make path array if it's a string (single path)
		if(is_string($paths)) {
			$paths = (array) $paths;	
		}
		
		// Check for defined load paths for given classname prefix
		foreach($this->loadPaths as $prefix => $prefixPaths) {
			// Always add global paths
			if($prefix == '*') {
				$paths = array_merge($paths, $prefixPaths);
			}
			
			// If string matches at beginning of class name
			if(strpos($className, $prefix) === 0) {
				$paths = array_merge($paths, $prefixPaths);
			}
		}
		
		// Add custom set include paths
		$incPath = false;
		if(is_array($paths) && count($paths) > 0) {
			// Filter out duplicate paths
			$paths = array_unique($paths);
			// Change include path to have all possible paths in it
			$dirs = implode(PATH_SEPARATOR, array_reverse($paths));
			$incPath = get_include_path();
			set_include_path($dirs . PATH_SEPARATOR . $incPath);
		}
		
		// Put together the full class location (include path)
		// Assumes Zend/PEAR class naming conventions
		$className = str_replace('_', '/', $className);
		$classLocation = $className . '.php';
		
		// Trace
		$this->trace('Class Loader', array('class' => $className, 'paths' => $paths, 'classFile' => $classLocation), __METHOD__, __FILE__, __LINE__, true);
		
		// Include class file if it exists
		// Set cutom error handler to surpress inclusion failure warnings
		set_error_handler(array($this, 'throwError'));
		try {
			// Warning: The '@' before the include will surpress parse errors
			$included = include_once($classLocation);
		} catch(Exception $e) {
			$included = false;
		}
		// Restore previous error handler
		restore_error_handler();
		
		// Reset include path with original
		if($incPath) {
			set_include_path($incPath);
		}
		
		// Return result
		if($included !== false) {
			$this->loaded[] = $className;
			return true;
		} else {
			return false;
		}
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
	 * Return a resource object to work with
	 */
	public function resource($data)
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
		if(!self::$debug) {
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
					'time' => ($currentTime - self::$traceTimeLast),
					'time_total' => $currentTime - self::$traceTimeStart,
					'memory' => $currentMemory - self::$traceMemoryLast,
					'memory_total' => $currentMemory - self::$traceMemoryStart
					);
				// Store as last run
				self::$traceTimeLast = $currentTime;
				self::$traceMemoryLast = $currentMemory;
			}
			self::$trace[] = $entry;
		}
		
		return self::$trace;
	}
	
	
	/**
	 * Internal message/action trace - to separate the internal function calls from the stack
	 */
	protected function traceInternal($msg = null, $data = array(), $function = null, $file = null, $line = null)
	{
		// Don't incur the overhead if not in debug mode
		if(!self::$debug) {
			return false;
		}
		
		// Log with last parameter as 'true' - last param will always be internal marker
		return $this->trace($msg, $data, $function, $file, $line, true);
	}
	
	
	/**
	 * Get/return user
	 *
	 * @param $className string Name of the class
	 * @return object
	 */
	public function user($user = null)
	{
		if(null !== $user) {
			$this->_user = $user;
		}
		return $this->_user;
	}
	
	
	/**
	 * Load and return instantiated module class
	 *
	 * @param string $className Name of the class
	 * @return object
	 * @throws Alloy_Exception_FileNotFound
	 */
	public function module($module, $init = true)
	{
		// Clean module name to prevent possible security vulnerabilities
		$sModule = preg_replace('/[^a-zA-Z0-9_]/', '', $module);
		
		// Upper-case beginning of each word
		$sModule = str_replace(' ', '_', ucwords(str_replace('_', ' ', $sModule)));
		$sModuleClass = 'Module\\' . $sModule . '\Controller';
		
		// Instantiate module class
		$sModuleObject = new $sModuleClass($this);
		
		// Run init() setup only if supported
		if(true === $init) {
			if(method_exists($sModuleObject, 'init')) {
				$sModuleObject->init();
			}
		}
		
		return $sModuleObject;
	}
	
	
	/**
	 * Get mapper object to work with
	 * Ensures only one instance of a mapper gets loaded
	 *
	 * @param string $mapperName (Optional) Custom mapper class to load in case of custom requirements or queries
	 */
	public function mapper($mapperName = 'Spot_Mapper')
	{
		if(!isset($this->_spotMapper[$mapperName])) {
			// Create new mapper, passing in config
			$this->_spotMapper[$mapperName] = new $mapperName($this->spotConfig());
		}
		return $this->_spotMapper[$mapperName];
	}
	
	
	/**
	 * Get instance of database connection
	 */
	public function spotConfig()
	{
		if(!$this->_spotConfig) {
			$dbCfg = $this->config('database');
			if($dbCfg) {
				// New config
				$this->_spotConfig = new Spot_Config();
				foreach($dbCfg as $name => $options) {
					$this->_spotConfig->addConnection($name, $options);
				}
			} else {
				throw new Exception("Unable to load configuration for Spot - Database configuration settings do not exist.");
			}
		}
		return $this->_spotConfig;
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
		try {
			if($module instanceof \Alloy\Module\ControllerAbstract) {
				// Use current module instance
				$sModuleObject = $module;
			} else {
				// Get module instance
				$sModuleObject = $this->module($module);
			}
			
			// Module action callable (includes __call magic function if method missing)?
			if(!is_callable(array($sModuleObject, $action))) {
				throw new Alloy_Exception_FileNotFound("Module '" . $module ."' does not have a callable method '" . $action . "'");
			}
		
			// Handle result
			$paramCount = count($params);
			if($paramCount == 0) {
				$result = $sModuleObject->$action();
			} elseif($paramCount == 1) {
				$result = $sModuleObject->$action(current($params));
			} elseif($paramCount == 2) {
				$result = $sModuleObject->$action($params[0], $params[1]);
			} elseif($paramCount == 3) {
				$result = $sModuleObject->$action($params[0], $params[1], $params[2]);
			} else {
				$result = call_user_func_array(array($sModuleObject, $action), $params);
			}
		
		// Database table/datasource missing - attempt to autoinstall
		// @todo Move this elsewhere - it probably does not belong here 
		} catch(Spot_Exception_Datasource_Missing $e) {
			$module = $this->module($module, false); // Don't run init function for install
			$result = $this->dispatch($module, 'install', array($action, $params));
		}
		
		return $result;
	}
	
	
	/**
	 * Dispatch module action from HTTP request
	 * Automatically limits call scope by appending 'Action' or 'Method' to module actions
	 *
	 * @param AppKernel_Request $request
	 * @param string $moduleName Name of module to be called
	 * @param optional string $action function name to call on module
	 * @param optional array $params parameters to pass to module function
	 *
	 * @return mixed String or object that has __toString method
	 */
	public function dispatchRequest($request, $module, $action = 'indexAction', array $params = array())
	{
		$requestMethod = $request->method();
		// Emulate REST for browsers
		if($request->isPost() && $request->post('_method')) {
			$requestMethod = $request->post('_method');
		}
		
		// Append 'Action' or 'Method'
		if(strtolower($requestMethod) == strtolower($action)) {
			$action = $action . (false === strpos($action, 'Method') ? 'Method' : ''); // Append with 'Method' to limit scope to REST access only
		} else {
			$action = $action . (false === strpos($action, 'Action') ? 'Action' : ''); // Append with 'Action' to limit scope of available functions from HTTP request
		}
		
		
		return $this->dispatch($module, $action, $params);
	}
	
	
	/**
	 * Event: Trigger a named event and execute callbacks that have been hooked onto it
	 * 
	 * @param string $name Name of the event
	 * @param array $params Parameters to pass to bound event callbacks
	 */
	public function trigger($name, array $params = array())
	{
		if(isset($this->binds[$name])) {
			foreach($this->binds[$name] as $hookName => $callback) {
				call_user_func_array($callback, $params);
			}
		}
		
		$this->trace('[Event] Triggered: ' . $name);
	}
	
	
	/**
	 * Event: Add callback to be triggered on event name
	 * 
	 * @param string $name Name of the event
	 * @param string $hookName Name of the bound callback that is being added (custom for each callback)
	 * @param callback $callback Callback to execute when named event is triggered
	 */
	public function bind($eventName, $hookName, $callback)
	{
		$this->binds[$eventName][$hookName] = $callback;
		$this->trace('[Event] Hook callback added: ' . $hookName . ' on event ' . $eventName);
	}
	
	
	/**
	 * Event: Remove callback by name
	 */
	public function unbind($eventName, $hookName)
	{
		if(isset($this->binds[$eventName][$hookName])) {
			unset($this->binds[$eventName][$hookName]);
		}
		$this->trace('[Event] Hook callback removed: ' . $hookName);
	}
	
	
	/**
	 * Generate URL from given params
	 *
	 * @param string $url
	 * @return string
	 */
	public function url($routeName, array $params = array(), $queryParams = array()) {
		$urlBase = $this->config('url.root', '');
		// Use query string if URL rewriting is not enabled
		if(!$this->config('url.rewrite')) {
			$urlBase .= "?r=";
		}
		// Query params
		$queryString = "";
		if(count($queryParams) > 0) {
			$queryString = "?" . http_build_query($queryParams);
		}
		$fullUrl = $urlBase . strtolower(ltrim($this->router()->url($routeName, $params), '/')) . $queryString;
		return $fullUrl;
	}
	
	
	/**
	 * Truncates a string to a certian length & adds a "..." to the end
	 *
	 * @param string $string
	 * @return string
	 */
	public function truncate($string, $endlength="30", $end="...") {
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
	public function formatUnderscoreWord($word) {
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
	public function formatFilesize($size) {
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
	public function arrayFlipConvert(array $input) {
		$output = array();
		foreach($input as $key => $val) {
			foreach($val as $key2 => $val2) {
				$output[$key2][$key] = $val2;
			}
		}
		return $output;
	}
	
	
	/**
	 * Custom error reporting
	 */
	public function errorHandler($errno, $errstr, $errfile, $errline) {
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
		self::$debug = (boolean) $switch;
		return $this->trace();
	}
	
	
	/**
	 * Print out an array or object contents in preformatted text
	 * Useful for debugging and quickly determining contents of variables
	 */
	public function dump()
	{
		$objects = func_get_args();
		foreach($objects as $object) {
			echo "<h1>Dumping " . gettype($object) . "</h1><br />\n";
			echo "\n<pre>\n";
			print_r($object);
			echo "\n</pre>\n";
		}
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
			throw new BadMethodCallException("Method '" . __CLASS__ . "::" . $method . "' not found or the command is not a valid callback type.");	
		}
	}
	
	
	/**
	 * Merges any number of arrays of any dimensions, the later overwriting
	 * previous keys, unless the key is numeric, in whitch case, duplicated
	 * values will not be added.
	 *
	 * The arrays to be merged are passed as arguments to the function.
	 *
	 * @access public
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
}


/**
 * Get and return instance of AppKernel
 * Checks if 'Alloy' function already exists so it can be overridden/customized
 */
if(!function_exists('Alloy')) {
	function Alloy(array $config = array()) {
		return \Alloy\Kernel::getInstance($config);
	}
}
