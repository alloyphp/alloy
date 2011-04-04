<?php
namespace Alloy\Module;
use Alloy;

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
    
    
    /**
     * Kernel to handle dependenies
     */
    public function __construct(Alloy\Kernel $kernel)
    {
        $this->kernel = $kernel;
    }
    
    
    /**
     * Called immediately upon instantiation, before the action is called
     */
    public function init($action = null) {}
    
    
    /**
     * Return current class path
     */
    public function path()
    {
        $class = get_called_class(); // Thank you late static binding!
        $path = str_replace('\\', '/', str_replace('\\Controller', '', $class));
        return \Kernel()->config('app.path.root') . '/' . $path;
    }
    
    
    /**
     * Return current module name, based on class naming conventions
     * Expected: \Module\[Name]\Controller
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
     * @return \Alloy\View\Template
     */
    public function template($file, $format = null)
    {
        $kernel = $this->kernel;
        $format = (null !== $format) ? $format : $kernel->request()->format;
        $view = new Alloy\View\Template($file, $format, $this->path() . "/views/");
        return $view;
    }


    /**
     * New generic module response
     *
     * @param string $file Template filename
     * @return \Alloy\Module\Response
     */
    public function response($content, $status = 200)
    {
        $res = new Alloy\Module\Response();
        return $res->content($content)
            ->status($status);
    }
}