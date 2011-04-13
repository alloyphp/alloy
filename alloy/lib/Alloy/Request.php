<?php
namespace Alloy;

/**
 * Request Class
 *
 * Wraps around request variables for now; Will also wrap XML, JSON, and CLI variables in the future
 *
 * @package Alloy
 * @todo Handle XML, JSON, CLI, etc. requests also (maybe)
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @link http://alloyframework.com/
 */
class Request
{
    // Request parameters
    protected $_params = array();
    
    
    /**
    * Access values contained in the superglobals as public members
    * Order of precedence: 1. GET, 2. POST, 3. COOKIE, 4. SERVER, 5. ENV
    *
    * @see http://msdn.microsoft.com/en-us/library/system.web.httprequest.item.aspx
    * @param string $key
    * @return mixed
    */
    public function get($key, $default = null)
    {
        switch (true) {
            case isset($this->_params[$key]):
                $value = $this->_params[$key];
            break;
            
            case isset($_GET[$key]):
                $value = $_GET[$key];
            break;
            
            case isset($_POST[$key]):
                $value = $_POST[$key];
            break;
            
            case isset($_COOKIE[$key]):
                $value = $_COOKIE[$key];
            break;
            
            case isset($_SERVER[$key]):
                $value = $_SERVER[$key];
            break;
                
            case isset($_ENV[$key]):
                    $value = $_ENV[$key];
            break;
                
            default:
            return $default;
        }
        
        return $value;
    }
    // Automagic companion function
    public function __get($key)
    {
        return $this->get($key);
    }
    
    
    /**
     *	Override request parameter value
     *
     *	@param string $key
     *	@param string $value
     */
    public function set($key, $value)
    {
        $this->_params[$key] = $value;
    }
    // Automagic companion function
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }
    
    
    /**
    * Check to see if a property is set
    *
    * @param string $key
    * @return boolean
    */
    public function __isset($key)
    {
        switch (true) {
            case isset($this->_params[$key]):
            return true;
            case isset($_GET[$key]):
            return true;
            case isset($_POST[$key]):
            return true;
            case isset($_COOKIE[$key]):
            return true;
            case isset($_SERVER[$key]):
            return true;
            case isset($_ENV[$key]):
            return true;
            default:
            return false;
        }
    }
    
    
    /**
    * Retrieve request parameters
    *
    * @return array Returns array of all GET, POST, and set params
    */
    public function params(array $params = array())
    {
        // Setting
        if(count($params) > 0) {
            foreach($params as $pKey => $pValue) {
                $this->set($pKey, $pValue);
            }
            
        // Getting
        } else {
            $params = array_merge($_GET, $_POST, $this->_params);
        }
        return $params;
    }
    
    
    /**
    * Set additional request parameters
    */
    public function setParams($params)
    {
        if($params && is_array($params)) {
            foreach($params as $pKey => $pValue) {
                $this->set($pKey, $pValue);
            }
        }
    }
    
    
    /**
    * Retrieve a member of the $params set variables
    *
    * If no $key is passed, returns the entire $params array.
    *
    * @todo How to retrieve from nested arrays
    * @param string $key
    * @param mixed $default Default value to use if key not found
    * @return mixed Returns null if key does not exist
    */
    public function param($key = null, $default = null)
    {
        if (null === $key) {
            return $this->_params;
        }
        
        return (isset($this->_params[$key])) ? $this->_params[$key] : $default;
    }
    
    
    /**
    * Retrieve a member of the $_GET superglobal
    *
    * If no $key is passed, returns the entire $_GET array.
    *
    * @todo How to retrieve from nested arrays
    * @param string $key
    * @param mixed $default Default value to use if key not found
    * @return mixed Returns null if key does not exist
    */
    public function query($key = null, $default = null)
        {
        if (null === $key) {
            return $_GET;
        }
        
        return (isset($_GET[$key])) ? $_GET[$key] : $default;
    }
    
    
    /**
    * Retrieve a member of the $_POST superglobal
    *
    * If no $key is passed, returns the entire $_POST array.
    *
    * @todo How to retrieve from nested arrays
    * @param string $key
    * @param mixed $default Default value to use if key not found
    * @return mixed Returns null if key does not exist
    */
    public function post($key = null, $default = null)
    {
        if (null === $key) {
            return $_POST;
        }
        
        return (isset($_POST[$key])) ? $_POST[$key] : $default;
    }
    
    
    /**
    * Retrieve a member of the $_COOKIE superglobal
    *
    * If no $key is passed, returns the entire $_COOKIE array.
    *
    * @todo How to retrieve from nested arrays
    * @param string $key
    * @param mixed $default Default value to use if key not found
    * @return mixed Returns null if key does not exist
    */
    public function cookie($key = null, $default = null)
    {
        if (null === $key) {
            return $_COOKIE;
        }
        
        return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default;
    }
    
    
    /**
    * Retrieve a member of the $_SERVER superglobal
    *
    * If no $key is passed, returns the entire $_SERVER array.
    *
    * @param string $key
    * @param mixed $default Default value to use if key not found
    * @return mixed Returns null if key does not exist
    */
    public function server($key = null, $default = null)
    {
        if (null === $key) {
            return $_SERVER;
        }
        
        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
    }
    
    
    /**
    * Retrieve a member of the $_ENV superglobal
    *
    * If no $key is passed, returns the entire $_ENV array.
    *
    * @param string $key
    * @param mixed $default Default value to use if key not found
    * @return mixed Returns null if key does not exist
    */
    public function env($key = null, $default = null)
    {
        if (null === $key) {
            return $_ENV;
        }
        
        return (isset($_ENV[$key])) ? $_ENV[$key] : $default;
    }
    
    
    /**
    * Return the value of the given HTTP header. Pass the header name as the
    * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
    * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
    *
    * @param string $header HTTP header name
    * @return string|false HTTP header value, or false if not found
    */
    public function header($header)
    {
        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
            
        // This seems to be the only way to get the Authorization header on Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }
        
        return false;
    }
    
    
    /**
    * Return the method by which the request was made
    *
    * @return string
    */
    public function method()
    {
        $method = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';

        // Emulate REST for browsers
        if($method == "POST" && $this->post('_method')) {
            $method = strtoupper($this->post('_method'));
        }

        return $method;
    }
    
    
    /**
     * Get a user's correct IP address
     * Retrieves IP's behind firewalls or ISP proxys like AOL
     *
     * @return string IP Address
     */
    public function ip()
    {
        $ip = FALSE;
    
        if( !empty( $_SERVER["HTTP_CLIENT_IP"] ) )
            $ip = $_SERVER["HTTP_CLIENT_IP"];
    
        if( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ){
            // Put the IP's into an array which we shall work with shortly.
            $ips = explode( ", ", $_SERVER['HTTP_X_FORWARDED_FOR'] );
            if( $ip ){
                array_unshift( $ips, $ip );
                $ip = false;
            }
    
            for( $i = 0; $i < count($ips); $i++ ){
                if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }
    
    
    /**
     *	Determine is incoming request is POST
     *
     *	@return boolean
     */
    public function isPost()
    {
        return ($this->method() == "POST");
    }
    
    
    /**
     *	Determine is incoming request is GET
     *
     *	@return boolean
     */
    public function isGet()
    {
        return ($this->method() == "GET");
    }
    
    
    /**
     *	Determine is incoming request is PUT
     *
     *	@return boolean
     */
    public function isPut()
    {
        return ($this->method() == "PUT");
    }
    
    
    /**
     *	Determine is incoming request is DELETE
     *
     *	@return boolean
     */
    public function isDelete()
    {
        return ($this->method() == "DELETE");
    }
    
    
    /**
     *	Determine is incoming request is HEAD
     *
     *	@return boolean
     */
    public function isHead()
    {
        return ($this->method() == "HEAD");
    }
    
    
    /**
     *	Determine is incoming request is secure HTTPS
     *
     *	@return boolean
     */
    public function isSecure()
    {
        return  (bool) ((!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) != 'on') ? false : true);
    }
    
    
    /**
     * Is the request a Javascript XMLHttpRequest?
     *
     * Works with Prototype/Script.aculo.us, jQuery, YUI, Dojo, possibly others.
     *
     * @return boolean
     */
    public function isAjax()
    {
        return ($this->header('X_REQUESTED_WITH') == 'XMLHttpRequest');
    }


    /**
     * Is the request coming from a mobile device?
     *
     * Works with iPhone, Android, Windows Mobile, Windows Phone 7, Symbian, and other mobile browsers
     *
     * @return boolean
     */
    public function isMobile()
    {
        $op = strtolower($_SERVER['HTTP_X_OPERAMINI_PHONE']);
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        $ac = strtolower($_SERVER['HTTP_ACCEPT']);

        return (
            strpos($ac, 'application/vnd.wap.xhtml+xml') !== false
            || strpos($ac, 'text/vnd.wap.wml') !== false
            || $op != ''
            || strpos($ua, 'iphone') !== false
            || strpos($ua, 'android') !== false
            || strpos($ua, 'iemobile') !== false 
            || strpos($ua, 'kindle') !== false
            || strpos($ua, 'sony') !== false 
            || strpos($ua, 'symbian') !== false 
            || strpos($ua, 'nokia') !== false 
            || strpos($ua, 'samsung') !== false 
            || strpos($ua, 'mobile') !== false
            || strpos($ua, 'windows ce') !== false
            || strpos($ua, 'epoc') !== false
            || strpos($ua, 'opera mini') !== false
            || strpos($ua, 'nitro') !== false
            || strpos($ua, 'j2me') !== false
            || strpos($ua, 'midp-') !== false
            || strpos($ua, 'cldc-') !== false
            || strpos($ua, 'netfront') !== false
            || strpos($ua, 'mot') !== false
            || strpos($ua, 'up.browser') !== false
            || strpos($ua, 'up.link') !== false
            || strpos($ua, 'audiovox') !== false
            || strpos($ua, 'blackberry') !== false
            || strpos($ua, 'ericsson,') !== false
            || strpos($ua, 'panasonic') !== false
            || strpos($ua, 'philips') !== false
            || strpos($ua, 'sanyo') !== false
            || strpos($ua, 'sharp') !== false
            || strpos($ua, 'sie-') !== false
            || strpos($ua, 'portalmmm') !== false
            || strpos($ua, 'blazer') !== false
            || strpos($ua, 'avantgo') !== false
            || strpos($ua, 'danger') !== false
            || strpos($ua, 'palm') !== false
            || strpos($ua, 'series60') !== false
            || strpos($ua, 'palmsource') !== false
            || strpos($ua, 'pocketpc') !== false
            || strpos($ua, 'smartphone') !== false
            || strpos($ua, 'rover') !== false
            || strpos($ua, 'ipaq') !== false
            || strpos($ua, 'au-mic,') !== false
            || strpos($ua, 'alcatel') !== false
            || strpos($ua, 'ericy') !== false
            || strpos($ua, 'up.link') !== false
            || strpos($ua, 'vodafone/') !== false
            || strpos($ua, 'wap1.') !== false
            || strpos($ua, 'wap2.') !== false
        );
    }
    
    
    /**
     * Is the request coming from a bot or spider?
     *
     * Works with Googlebot, MSN, Yahoo, possibly others.
     *
     * @return boolean
     */
    public function isBot()
    {
        $ua =strtolower($this->server('HTTP_USER_AGENT'));
        return (
            false !== strpos($ua, 'googlebot') ||
            false !== strpos($ua, 'msnbot') ||
            false !== strpos($ua, 'yahoo!') ||
            false !== strpos($ua, 'slurp') ||
            false !== strpos($ua, 'bot') ||
            false !== strpos($ua, 'spider')
        );
    }
    
    
    /**
     * Is the request from CLI (Command-Line Interface)?
     *
     * @return boolean
     */
    public function isCli()
    {
        return !isset($_SERVER['HTTP_HOST']);
    }
    
    
    /**
     * Is this a Flash request?
     * 
     * @return bool
     */
    public function isFlash()
    {
        return ($this->header('USER_AGENT') == 'Shockwave Flash');
    }
    
    
    /**
     * Apply a user-defined function to all request parameters
     *
     * @string $function user-defined function
     */
    public function map($function)
    {
        $in = array(&$_GET, &$_POST, &$_COOKIE);
        while (list($k,$v) = each($in)) {
            if(is_array($v)) {
                foreach ($v as $key => $val) {
                    if (!is_array($val)) {
                        $in[$k][$key] = $function($val);
                    }
                    $in[] =& $in[$k][$key];
                }
            }
        }
        unset($in);
        return true;
    }
}