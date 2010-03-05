<?php
/**
 * Base application module controller
 * Used as a base module class other modules must extend from
 *
 * @package Alloy Framework
 * @link http://alloyframework.com
 */
abstract class Alloy_Module_Controller
{
	protected $kernel;
	protected $_file = __FILE__;
	
	
	/**
	 * Kernel to handle dependenies
	 */
	public function __construct(Cx_Kernel $kernel)
	{
		$this->kernel = $kernel;
	}
	
	
	/**
	 * Called immediately upon instantiation, before the action is called
	 */
	public function init() {}
	
	
	/**
	 * Return current class path, based on given '$file' class var
	 */
	public function path()
	{
		return dirname($this->_file);
	}
	
	
	/**
	 * Return current module name, based on class naming conventions
	 * Expected: Module_[Name]_Controller
	 */
	public function name()
	{
		$name = str_replace("_Controller", "", get_class($this));
		return str_replace("Module_", "", $name);
	}
	
	
	/**
	 * New module view template
	 *
	 * @param string $template Template name/path
	 * @param string $format Template output format
	 */
	public function view($template, $format = "html")
	{
		$view = new Cx_View($template, $format, $this->path() . "/views/");
		$view->format($this->kernel->request()->format);
		$view->set('kernel', $this->kernel);
		return $view;
	}
	
	
	/**
	 * Get mapper object to work with
	 * 
	 * @param string $mapperName Name of the mapper object to load (by convention)
	 */
	public function mapper($mapperName = null)
	{
		// Append given name, if any
		if(null === $mapperName) {
			$mapperName = 'Module_' . $this->name();
		}
		
		return $this->kernel->mapper($mapperName);
	}
}