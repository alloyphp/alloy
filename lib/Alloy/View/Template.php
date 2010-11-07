<?php
namespace Alloy\View;

/**
 * View template class that will display and handle view templates
 *
 * @package Alloy
 * @link http://alloyframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Template
{
    // Template specific stuff
    protected $_file;
    protected $_fileFormat;
    protected $_vars = array();
    protected $_path;
    
    // Extension type
    protected $_default_format = 'html';
    protected $_default_extenstion = 'php';
    
    // Helpers
    protected static $_helpers = array();
    
    // Errors
    protected $_errors = array();
    
    // Kernel instance
    protected $kernel;
    
    
    /**
     * Constructor function
     *
     * @param $file string	Template filename to use
     * @param $module string	Module template file resides in
     */
    public function __construct($file, $format = 'html', $path = null)
    {
        $this->kernel = \Kernel();
        
        $this->file($file, $format);
        $this->path($path);
        
        $this->init();
    }
    
    
    /**
     * Setup for view, used for extensibility without overriding constructor
     */
    public function init() {}
    
    
    /**
     * HTML Head helper object
     */
    public function head()
    {
        return $this->helper('Head');
    }
    
    
    /**
     * Cache prototype
     * Not functional as a cache yet
     */
    public function cache($closure, $name)
    {
        if(is_array($closure) || !is_callable($closure)) {
            throw new \InvalidArgumentException("Cache helper expected a closure");
        }
        
        $helper = $this->helper('Cache');
        $cached = $helper->get($name);
        if(!$cached) {
            $cached = $helper->set($name, $closure);
        }
        echo $cached($this);
    }
    
    
    /**
     * Gets a view variable
     *
     * Surpress notice errors for variables not found to
     * help make template syntax for variables simpler
     *
     * @param  string  key
     * @return mixed   value if the key is found
     * @return null    if key is not found
     */
    public function __get($var)
    {
        if(isset($this->_vars[$var])) {
            return $this->_vars[$var];
        } else {
            return null;
        }
    }
    
    
    /**
     * Sets a view variable.
     *
     * @param   string   key
     * @param   string   value
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }
    
    
    /**
     * Gets a view variable
     */
    public function get($var)
    {
        return $this->__get($var);
    }
    
    
    /**
     *	Assign template variables
     *
     *	@param string key
     *	@param mixed value
     */
    public function set($key, $value='')
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                if(!empty($k)) {
                    $this->_vars[$k] = $v;
                }
            }
        } else {
            if(!empty($key)) {
                $this->_vars[$key] = $value;
            }
        }
        return $this; // Fluent interface
    }
    
    
    /**
     * Get template variables
     * 
     * @return array
     */
    public function vars()
    {
        return $this->_vars;
    }
    
    
    /**
     * Load and return generic view template
     * 
     * @return Alloy\View\Template
     */
    public function generic($name, $template = null)
    {
        $helperClass = 'Alloy\View\Generic\\' . $name;
        $template = (null === $template) ? strtolower($name) : $template;
        return new $helperClass($template);
    }
    
    
    /**
     * Load and return view helper
     * 
     * @return Alloy\View\Helper\HelperAbstract
     */
    public function helper($name)
    {
        $helperClass = 'Alloy\View\Helper\\' . $name;
        
        if(!isset(self::$_helpers[$helperClass])) {
            self::$_helpers[$helperClass] = new $helperClass(\Kernel(), $this);
        }
        return self::$_helpers[$helperClass];
    }
    
    
    /**
     * Get/Set path to look in for templates
     */
    public function path($path = null)
    {
        if(null === $path) {
            return $this->_path;
        } else {
            $this->_path = $path;
            return $this; // Fluent interface
        }
    }
    
    
    /**
     * Get template name that was set
     *
     * @return string
     */
    public function file($view = null, $format = null)
    {
        if(null === $view) {
            return $this->_file;
        } else {
            $this->_file = $view;
            $this->_fileFormat = ($format) ? $format : $this->_default_extenstion;
            return $this; // Fluent interface
        }
    }
    
    
    /**
     * Returns full template filename with format and extension
     *
     * @param OPTIONAL $template string (Name of the template to return full file format)
     * @return string
     */
    public function filePath($template = null)
    {
        if(null === $template) {
            $template = $this->file();
        }
        return $template . '.' . $this->format() . '.' . $this->_default_extenstion;
    }
    
    
    /**
     * Get/Set layout format to use
     * Templates will use: <template>.<format>.<extension>
     * Example: index.html.php
     *
     * @param $format string (html|xml)
     */
    public function format($format = null)
    {
        if(null === $format) {
            return $this->_fileFormat;
        } else {
            $this->_fileFormat = $format;
            return $this; // Fluent interface
        }
    }
    
    
    /**
     * Set errors to template
     *
     * @param array $errors
     */
    public function errors($errors = null)
    {
        //$this->set('errors', $errors);
        if(null === $errors) {
            return $this->_errors;
        } elseif(is_string($errors)) {
            return isset($this->_errors[$errors]) ? $this->_errors[$errors] : array();
        } else {
            $this->_errors = $errors;
            return $this; // Fluent interface
        }
        return $this; // Fluent interface
    }
    
    
    /**
     * Escapes HTML entities
     * Use to prevent XSS attacks
     *
     * @link http://ha.ckers.org/xss.html
     */
    public function h($str)
    {
        return htmlentities($str, ENT_NOQUOTES, "UTF-8");
    }
    
    
    /**
     * Full URL to different component/action
     */
    public function url($params, $route = null, array $qsData = array(), $qsAppend = false)
    {
        return $this->kernel->url($params, $route, $qsData, $qsAppend);
    }
    
    
    /**
     * HTML link tag to specified URL or route
     */
    public function link($title, $params, $route = null, array $extra = array(), $qsAppend = false)
    {
        $qsData = isset($params['?']) && is_array($params['?']) ? $params['?'] : array();
        $extra['title'] = isset($extra['title']) ? $extra['title'] : trim(strip_tags($title));
        $tag = '<a href="' . $this->url($params, $route, $qsData, $qsAppend) . '"' . $this->toTagAttributes($extra) . '>' . $title . '</a>';
        return $tag;
    }
    
    
    /**
     * List array values as HTML tag attributes
     */
    public function toTagAttributes(array $extra)
    {
        $str = '';
        foreach($extra as $key => $value) {
            // Javascript "confirm" box
            if($key == "confirm") {
                $msg = (empty($value) || $value == "true") ? "Are you sure?" : $value;
                $str .= ' onClick="if(!confirm(\'' . $msg . '\')){ return false; }"';
            } else {
                $str .= ' ' . $key . '="' . $value . '"';
            }
        }
        return $str;
    }
    
    
    /**
     * Date/time string to date format
     *
     * @param mixed $input Date string or timestamp
     * @param string $format Optional string format
     * @return string
     */
    public function toDate($input = null, $format = 'M d, Y')
    {
        $format = $this->kernel->config('i18n.date_format', $format);
        return $input ? date($format, (is_int($input) ? $input : strtotime($input))) : date($format);
    }
    
    
    /**
     * Date/time string to date format
     *
     * @param mixed $input Date string or timestamp
     * @param string $format Optional string format
     * @return string
     */
    public function toDateTime($input = null, $format = null)
    {
        $format = ($format) ? $format : ($this->kernel->config('i18n.date_format') . ' ' . $this->kernel->config('i18n.time_format'));
        return $input ? date($format, (is_int($input) ? $input : strtotime($input))) : date($format);
    }
    
    
    /**
     * Load and return new Alloy view for partial
     *
     * @param string $template Template file to use
     * @param array $vars Variables to pass to partial
     * @return Alloy\View\Template
     */
    public function partial($template, array $vars = array())
    {
        $partial = new static($template, $this->format(), $this->path());
        return $partial->set($vars);
    }
    
    
    /**
     * Display template file (read and echo contents)
     */
    public function render()
    {
        echo $this->content();
    }
    
    
    /**
     * Read template file into content string and return it
     *
     * @return string
     */
    public function content($parsePHP = true)
    {
        $vpath = $this->path();
        $template = $this->filePath();
        $vfile = $vpath . $template;
        
        // Empty template name
        if(empty($vpath)) {
            throw new \RuntimeException("Base template path is not set!  Use '\$view->path('/path/to/template')' to set root path to template files!");
        }
        
        // Ensure template file exists
        if(!file_exists($vfile)) {
            throw new \RuntimeException("The template file '" . $template . "' does not exist.<br />Path: " . $vpath);
        }
        
        // Include() and parse PHP code
        if($parsePHP) {
            ob_start();
            // Extract set variables into local template scope
            extract($this->vars());
            include($vfile);
            $templateContent = ob_get_contents();
            ob_end_clean();
        } else {
            // Just get raw file contents
            $templateContent = file_get_contents($vfile);
        }
            
        return $templateContent;
    }
    
    
    /**
     * Converts view object to string on the fly
     *
     * @return  string
     */
    public function __toString()
    {
        // Exceptions cannot be thrown in __toString method (results in fatal error)
        // We have to catch any that may be thrown and return a string
        try {
            $content = $this->content();
        } catch(Alloy\Exception_View $e) {
            $content = $e->getError();
        } catch(\Exception $e) {
            $content = "<strong>TEMPLATE RENDERING ERROR:</strong><br />" . $e->getMessage();
            if($this->kernel->config('debug')) {
                $this->kernel->dump($e->getTraceAsString());
            }
        }
        return $content;
    }
}