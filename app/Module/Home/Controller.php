<?php
namespace Module\Home;

/**
 * Home Module
 */
class Controller extends \Alloy\Module\ControllerAbstract
{
	/**
	 * Index
	 * @method GET
	 */
	public function indexAction(\Alloy\Request $request)
	{
	    return $this->view(__FUNCTION__);
		return "Hello World!";
	}
}
