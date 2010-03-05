<?php
/**
 * Base application plugin controller
 * Used as a base plugin controller other plugins must extend from
 */
abstract class Cx_Plugin_Controller
{
	protected $cx;
	
	
	/**
	 * Kernel to handle dependenies
	 */
	public function __construct(Cx $cx)
	{
		$this->cx = $cx;
	}
	
	
	/**
	 * Called immediately upon instantiation, before the action is called
	 */
	public function init() {}
	
	
	/**
	 * Return current plugin name, based on class naming conventions
	 * Expected: Plugin_[Name]_Controller
	 */
	public function name()
	{
		return str_replace("_Controller", "", get_class($this));
	}
}