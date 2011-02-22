<?php
namespace Module\Home;
use App, Alloy;

/**
 * Home Module
 * 
 * Extends from base Application controller so custom functionality can be added easily
 *   lib/App/Module/ControllerAbstract
 */
class Controller extends App\Module\ControllerAbstract
{
    /**
     * Index
     * @method GET
     */
    public function indexAction(Alloy\Request $request)
    {
    	$greeting = "Hello World";

    	// Returns Alloy\View\Template object that renders template on __toString:
    	//   views/indexAction.html.php
        return $this->template(__FUNCTION__)
        	->set(compact('greeting'));
    }


    /**
     * Return raw string content
     * @method GET
     */
    public function helloAction(Alloy\Request $request)
    {
        return "Hello World!";
    }


    /**
     * Returns 400 "Bad Request" HTTP Status
     * @method GET
     */
    public function helloBadAction(Alloy\Request $request)
    {
        // Set to any HTTP status code you want
        return $this->response("Hello Bad Request!", 400);
    }


    /**
     * Returns generic "404 File Not Found" error
     *  Useful for silent failure conditions like trying to retrieve a DB record that does not exist
     * 
     * @method GET
     */
    public function hello404(Alloy\Request $request)
    {
        return false;
    }
}