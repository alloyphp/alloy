<?php
namespace Alloy\Module;

/**
 * Base application module controller
 * Used as a base module class other modules must extend from
 *
 * @package Alloy
 * @link http://alloyframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
abstract class ControllerAbstract
{
    protected $kernel;
    protected $_file = __FILE__;
    
    
    /**
     * Kernel to handle dependenies
     */
    public function __construct(\Alloy\Kernel $kernel)
    {
        $this->kernel = $kernel;
    }
    
    
    /**
     * Called immediately upon instantiation, before the action is called
     */
    public function init() {}
    
    
    /**
     * Return current class path
     */
    public function path()
    {
        $class = get_called_class();
        $path = str_replace('\\', '/', str_replace('\\Controller', '', $class));
        return $this->kernel->config('path.app') . '/' . $path;
    }
    
    
    /**
     * Return current module name, based on class naming conventions
     * Expected: Module_[Name]_Controller
     */
    public function name()
    {
        $name = str_replace("\\Controller", "", get_class($this));
        return str_replace("Module\\", "", $name);
    }
    
    
    /**
     * New module view template
     *
     * @param string $file Template filename
     * @param string $format Template output format
     */
    public function template($file, $format = null)
    {
        $format = ($format) ?: $this->kernel->request()->format;
        $view = new \Alloy\View\Template($file, $format, $this->path() . "/views/");
        $view->format($this->kernel->request()->format);
        $view->set('kernel', $this->kernel);
        return $view;
    }
}