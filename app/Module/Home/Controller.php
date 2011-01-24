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
        $this->kernel->events();
        return "Hello World!";
    }
}