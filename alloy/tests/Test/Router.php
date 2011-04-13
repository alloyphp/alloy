<?php
/**
 * Alloy Router generic tests
 */
class Test_Router extends \PHPUnit_Framework_TestCase
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
    
    public function testReset()
    {
        $this->assertEquals(0, count($this->router->routes()));
        
        $this->router->route('test', '/<:test>');
        $this->assertEquals(1, count($this->router->routes()));
        
        $this->router->reset();
        $this->assertEquals(0, count($this->router->routes()));
    }
    
    public function testRouteSingleAlpha()
    {
        $this->router->route('module', '/<:module>');
        $params = $this->router->match("GET", "test");
        $this->assertEquals('test', $params['module']);
        
        // With beginning slash
        $params = $this->router->match("GET", "/test");
        $this->assertEquals('test', $params['module']);
    }
    
    public function testRouteMVCAction()
    {
        $router = $this->router;
        $route = $router->route('mvc', '/<:controller>/<:action>.<:format>');
        
        $params = $router->match("GET", "/user/profile.html");
        
        $this->assertEquals('user', $params['controller']);
        $this->assertEquals('profile', $params['action']);
        $this->assertEquals('html', $params['format']);
    }
    
    public function testRouteMVCItem()
    {
        $router = $this->router;	
        $router->route('mvc_item', '/<:controller>/<:action>/<#id>.<:format>');
        
        $params = $router->match("GET", "/blog/show/55.json");
        
        $this->assertEquals('blog', $params['controller']);
        $this->assertEquals('show', $params['action']);
        $this->assertEquals('55', $params['id']);
        $this->assertEquals('json', $params['format']);
    }
    
    public function testRouteBlogPost()
    {
        $router = $this->router;	
        $router->route('blog_post', '/<:dir>/<#year>/<#month>/<:slug>');
        
        $params = $router->match("GET", "/blog/2009/10/blog-post-title");
        
        $this->assertEquals('blog', $params['dir']);
        $this->assertEquals('2009', $params['year']);
        $this->assertEquals('10', $params['month']);
        $this->assertEquals('blog-post-title', $params['slug']);
    }
    
    public function testRouteWildcard()
    {
        $router = $this->router;
            
        $route = $router->route('url', '<*url>');
        $params = $router->match("GET", "/blog/2009/10/27/my-post-title");
        $this->assertEquals('blog/2009/10/27/my-post-title', $params['url']);
    }
    
    public function testRouteWildcard2()
    {
        $router = $this->router;
            
        $router->route('url2', '/<:dir>/<*url>');
        $params = $router->match("GET", "/blog/2009/10/27/my-post-title");
        $this->assertEquals('blog', $params['dir']);
        $this->assertEquals('2009/10/27/my-post-title', $params['url']);
    }
    
    /**
     * Static route - no matched parameters
     */
    public function testRouteStatic()
    {
        $this->router->route('login_route', '/user/login')
            ->defaults(array('controller' => 'user', 'action' => 'login'));
        
        // Attempt to match URL
        $params = $this->router->match("GET", "/user/login");
        $route = $this->router->matchedRoute();
        
        // Match route name?
        $this->assertEquals("login_route", $route->name());
        // Match resulting URL?
        $this->assertEquals("user", $params['controller']);
        $this->assertEquals("login", $params['action']);
    }
    
    public function testRouteWithSpaces()
    {
        $this->router->route('module', '/<:module>');
        $params = $this->router->match("GET", "test ing");
        $this->assertEquals('test ing', $params['module']);
    }
    
    public function testRouteWithUrlEncodingIsDecoded()
    {
        $this->router->route('module', '/<:module>');
        $params = $this->router->match("GET", "test+ing");
        $this->assertEquals('test ing', $params['module']);
    }
    
    public function testRouteWithUrlEncodingPercentSignIsDecoded()
    {
        $this->router->route('module', '/<:module>');
        $params = $this->router->match("GET", "test%20ing");
        $this->assertEquals('test ing', $params['module']);
    }
    
    public function testUrlEncodedSymbols()
    {
        // Model
        $this->router->route('vehicle_view', '/<#year>/<:make>/<:model>.<:format>')
                ->defaults(array('module' => 'vehicle', 'action' => 'view'));
        
        $params = $this->router->match("GET", "/2007/chrysler/town+%26+country.json");
        
        $this->assertEquals("vehicle", $params['module']);
        $this->assertEquals("view", $params['action']);
        $this->assertEquals("2007", $params['year']);
        $this->assertEquals("chrysler", $params['make'] );
        $this->assertEquals("town & country", $params['model']);
        $this->assertEquals("json", $params['format']);
    }

    public function testRouteConditionFalse()
    {
        $this->router->route('module', '/<:module>')
            ->condition(function($params, $method, $url) {
                return ($url != "test");
            });
        $this->router->route('module2', '/<:module2>');
        
        // Match SECOND route (first one should skip)
        $params = $this->router->match("GET", "test");
        $this->assertEquals(array('module2' => 'test'), $params);
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRouteConditionCallbackInvalidThrowsException()
    {
        $this->router->route('module', '/<:module>')
            ->condition('funnystuff');
    }

    public function testRouteAfterMatchCallback()
    {
        $this->router->route('module', '/<:module>')
            ->afterMatch(function($params, $method, $url) {
                // Override 'module' to 'someValue'
                $params['module'] = 'someValue';
                return $params;
            });
        
        $params = $this->router->match("GET", "anything");
        $this->assertEquals(array('module' => 'someValue'), $params);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRouteAfterMatchCallbackInvalidThrowsException()
    {
        $this->router->route('module', '/<:module>')
            ->afterMatch('non_existant_function_should_throw_exception');
    }
}