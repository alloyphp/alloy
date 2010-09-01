<?php
/**
 * Base View Helper
 * 
 * @package Alloy Framework
 * @link http://alloyframework.com
 */
abstract class Alloy_View_Helper
{
	protected $_view;
	
	public function __construct(Alloy_View $view)
	{
		$this->_view = $view;
		$this->init();
	}
	
	
	/**
	 * Initialization called upon instantiation
	 * Used so extending classes don't have to override constructor
	 */
	public function init() {}
}