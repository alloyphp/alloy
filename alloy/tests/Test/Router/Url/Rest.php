<?php
/**
 * Alloy Router REST URL tests
 * 
 * $router->route('/<:controller>(/<:action>(/<:id>))(.<:format>)'); // NESTED optional params - hopefully we can get here someday...
 *  "/resource" - GET (list) and POST operations
 *  "/resource/<:id>" - GET (view), PUT, and DELETE operations
 */
class Test_Router_Url_Rest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        
        $this->router = \Kernel()->router();
        if($this->router instanceof \Alloy\Router) {
            $this->router->reset();
        }
    }
    
    public function testInstance()
    {
        $this->assertTrue($this->router instanceof \Alloy\Router);
    }
    
    
    /**
     * Merb-like routes
     */
    public function testRouteRest()
    {
        $router = $this->router;
        $route = $router->route('default', '/<:module>/<#id>(.<:format>)')
                ->defaults(array('format' => 'html', 'action' => 'index'))
                ->get(array('action' => 'view'))
                ->put(array('action' => 'update'))
                ->delete(array('action' => 'delete'));
        
        // Match URL
        $params = $router->match("GET", "/user/235");
        
        // Check matched params
        $this->assertEquals('user', $params['module']);
        $this->assertEquals('view', $params['action']);
        $this->assertEquals('235', $params['id']);
        $this->assertEquals('html', $params['format']);
    }
    
    public function testRouteRestWithOptionalParam()
    {
        $router = $this->router;
        $route = $router->route('default', '/<:module>/<#id>(.<:format>)')
                ->defaults(array('format' => 'html', 'action' => 'index'))
                ->get(array('action' => 'view'))
                ->put(array('action' => 'update'))
                ->delete(array('action' => 'delete'));
        
        // Match URL
        $params = $router->match("GET", "/event/164.html");
        
        // Check matched params
        $this->assertEquals('event', $params['module']);
        $this->assertEquals('view', $params['action']);
        $this->assertEquals('164', $params['id']);
        $this->assertEquals('html', $params['format']);
    }

    public function testRouteRestWithAction()
    {
        $router = $this->router;
        $route = $router->route('test', '/<:module>/<:action>')
            ->defaults(array('format' => 'html', 'action' => 'index'))
            ->get(array('action' => 'view'));
        
        // Match URL
        $params = $router->match("GET", "/user/list");
        
        // Check matched params
        $this->assertEquals('user', $params['module']);
        $this->assertEquals('list', $params['action']);
        $this->assertEquals('html', $params['format']);
    }

    public function testRouteRestWithActionPost()
    {
        $router = $this->router;
        $route = $router->route('test', '/<:module>(/<:action>)')
            ->defaults(array('format' => 'html', 'action' => 'index'))
            ->post(array('action' => 'new'));
        
        // Match URL with action
        $params = $router->match("POST", "/user/list");
        
        // Check matched params
        $this->assertEquals('user', $params['module']);
        // Expect to preserve action POSTED to
        $this->assertEquals('list', $params['action']);
        $this->assertEquals('html', $params['format']);
    }

    public function testRouteRestWithoutActionPost()
    {
        $router = $this->router;
        $route = $router->route('test', '/<:module>(/<:action>)')
            ->defaults(array('format' => 'html', 'action' => 'index'))
            ->post(array('action' => 'new'));
        
        // Match URL without action
        $params = $router->match("POST", "/user");
        
        // Check matched params
        $this->assertEquals('user', $params['module']);
        // Expect to fill-in with supplied method action 'new'
        $this->assertEquals('new', $params['action']);
        $this->assertEquals('html', $params['format']);
    }
}