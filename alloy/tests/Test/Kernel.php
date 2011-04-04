<?php 
/**
 * Alloy Kernel generic tests
 */
class Test_Kernel extends \PHPUnit_Framework_TestCase
{
    // Test AppKernel instance
    public function setUp()
    {
        $kernel = \Kernel();
        $this->kernel = $kernel;
    }
    
    // Test AppKernel instance
    public function testInstance()
    {
        $this->assertTrue($this->kernel instanceof \Alloy\Kernel);
    }
    
    public function testConfig()
    {
        $cfg = array(
            'debug' => true,
            'foo' => array('bar' => 'baz')
            );
        $this->kernel->config($cfg);
        
        $this->assertTrue($this->kernel->config('debug'));
        $this->assertEquals('baz', $this->kernel->config('foo.bar'));
    }
    
    public function testConfigUpdate()
    {
        // Set initial config
        $cfg = array(
            'debug' => true,
            'foo' => array('bar' => 'baz')
            );
        $this->kernel->config($cfg);
        
        // Update with new config parts
        $cfg = array(
            'debug' => false,
            'foo' => array('bar' => 'baz too')
            );
        $this->kernel->config($cfg);
        
        $this->assertFalse($this->kernel->config('debug'));
        $this->assertEquals('baz too', $this->kernel->config('foo.bar'));
    }
    
    public function testTeachFunction()
    {
        $this->kernel->addMethod('bark', 'barkSample');
        $result = $this->kernel->bark('Arf!');
        $this->assertEquals('Arf!', $result);
    }
    
    public function testTeachClassMethod()
    {
        $callSample = new callSample();
        $this->kernel->addMethod('bark2', array($callSample, 'bark'));
        $result = $this->kernel->bark2('Arf2!');
        $this->assertEquals('Arf2!', $result);
    }
}


// Test callback function
function barkSample($word)
{
    return ($word) ? $word : 'Woof!';
}

// Test callback class
class callSample
{
    public function bark($word)
    {
        return ($word) ? $word : 'Woof!';
    }
}

