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
	}
}