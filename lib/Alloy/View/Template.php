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
    protected $_vars;
    protected $_path;
    
    // Extension type
    protected $_default_format = 'html';
    protected $_default_extenstion = 'php';
    
    // Helpers
    protected static $_helpers = array();
    
    // Errors
    protected $_errors = array();
    
    
    /**
     *	Constructor function
     *
     * @param $file string	Template filename to use
     * @param $module string	Module template file resides in
     */
    public function __construct($file, $format = 'html', $path = null)
    {
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
     * Load and return view helper
     * 
     * @return Cx_View_Helper
     */
    public function helper($name)
    {
        $helperClass = 'Alloy\View\Helper_' . $name;
        
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
        }
        return $content;
    }
}