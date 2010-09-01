<?php
namespace Alloy\View;

/**
 * Base View Helper
 * 
 * @package Alloy
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @link http://alloyframework.com/
 */
abstract class HelperAbstract
{
	protected $_view;
	
	public function __construct(\Alloy\View\Template $view)
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
