<?php
/**
 * Alloy Router generic URL tests
 */
class Test_Router_Url extends \PHPUnit_Framework_TestCase
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
    
    public function testUrlMVCAction()
    {
        $router = $this->router;
        $router->route('mvc', '<:controller>/<:action>.<:format>');
        $router->route('mvc_item', '<:controller>/<:action>/<#id>.<:format>');
        $router->route('blog_post', '<:dir>/<#year>/<#month>/<:slug>');
        
        $url = $router->url(array('controller' => 'user', 'action' => 'profile', 'format' => 'html'), 'mvc');
        
        $this->assertEquals("user/profile.html", $url);
    }
    
    public function testUrlMVCItem()
    {
        $router = $this->router;
        $router->route('mvc', '<:controller>/<:action>.<:format>');
        $router->route('mvc_item', '<:controller>/<:action>/<#id>.<:format>');
        $router->route('blog_post', '<:dir>/<#year>/<#month>/<:slug>');
        
        $url = $router->url(array('controller' => 'blog', 'action' => 'show', 'id' => 55, 'format' => 'json'), 'mvc_item');
        
        $this->assertEquals("blog/show/55.json", $url);
    }
    
    public function testUrlBlogPost()
    {
        $router = $this->router;
        $router->route('mvc', '<:controller>/<:action>.<:format>');
        $router->route('mvc_item', '<:controller>/<:action>/<#id>.<:format>');
        $router->route('blog_post', '<:dir>/<#year>/<#month>/<:slug>');
        
        $url = $router->url(array('dir' => 'blog', 'year' => 2009, 'month' => '10', 'slug' => 'blog-post-title'), 'blog_post');
        
        $this->assertEquals("blog/2009/10/blog-post-title", $url);
    }
    
    public function testUrlBlogPostDefaults()
    {
        $router = $this->router;
        $router->route('blog_post_x', '<:dir>/<#year>/<#month>/<:slug>')
                ->defaults(array('dir' => 'blog'));
        
        // Do not supply 'dir', expect the defined default 'dir' => 'blog' in the route definition to fill it in
        $url = $router->url(array('year' => 2009, 'month' => '10', 'slug' => 'blog-post-title'), 'blog_post_x');
        
        $this->assertEquals("blog/2009/10/blog-post-title", $url);
    }
    
    public function testUrlBlogPostException()
    {
        $router = $this->router;
        $router->route('blog_post', '<:dir>/<#year>/<#month>/<:slug>');
        
        try {
            // Do not supply 'dir' or 'slug', expect exception to be raised
            $url = $router->url(array('year' => 2009, 'month' => '10'), 'blog_post');
        } catch(Exception $e) {
            return;
        }
        
        $this->fail("Expected exception, none raised.");
    }
    
    public function testUrlRemoveEscapeCharacters()
    {
        // Route with escape character before the dot '.'
        $this->router->route('index_action', '<:action>\.<:format>')
                ->defaults(array('format' => 'html'));
        
        // Use default format
        $url = $this->router->url(array('action' => 'new'), 'index_action');
        $this->assertEquals("new.html", $url);
        
        // Use custom format
        $url = $this->router->url(array('action' => 'new', 'format' => 'xml'), 'index_action');
        $this->assertEquals("new.xml", $url);
    }
    
    public function testUrlOptionalParamsNotInUrlWhenValueNotSet()
    {
        // Route with escape character before the dot '.'
        $this->router->route('test', '<:controller>(.<:format>)')
                ->defaults(array('format' => 'html'));
        
        // Use default format (URL should not have '.html', because it is not set and it is default)
        $url = $this->router->url(array('controller' => 'events'), 'test');
        $this->assertEquals("events", $url);
        
        // Use default format (URL SHOULD have '.html', because it is set)
        $url = $this->router->url(array('controller' => 'events', 'format' => 'html'), 'test');
        $this->assertEquals("events.html", $url);
        
        // Use custom format (URL SHOULD have '.xml' because it IS set and it IS NOT default)
        $url = $this->router->url(array('controller' => 'events', 'format' => 'xml'), 'test');
        $this->assertEquals("events.xml", $url);
    }
    
    
    /**
     * Static route - no matched parameters
     */
    public function testUrlStatic()
    {
        $this->router->route('login', '/user/login');
        
        // Get static URL with no parameters
        $url = $this->router->url('login');
        $this->assertEquals("user/login", $url);
    }
    
    
    public function testUrlPlusSignIsNotEncoded()
    {
        $router = $this->router;
        $router->route('match', '<:match>');
        
        $url = $router->url(array('match' => 'blog post'), 'match');
        
        $this->assertEquals("blog+post", $url);
    }
}