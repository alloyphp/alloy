<?php 
/**
 * Alloy Event tests
 */
class Test_Events extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $kernel = \Kernel();
        $this->kernel = $kernel;
        
        // Get events container and reset for each test
        $this->events = $kernel->events();
        $this->events->reset();
    }
    
    
    public function testInstance()
    {
        $this->assertTrue($this->events instanceof \Alloy\Events);
    }
    
    
    public function testBindSingle()
    {
        $this->events->bind('test_event', 'foo', function() {
            echo 'bar';
        });
        
        ob_start();
        $numberOfTriggeredEvents = $this->events->trigger('test_event');
        $this->assertEquals(ob_get_clean(), 'bar');
        $this->assertEquals(1, $numberOfTriggeredEvents);
    }
    
    
    public function testBindMultiple()
    {
        $this->events->bind('test_event', 'foo', function() {
            echo 'bar';
        });
        $this->events->bind('test_event', 'bar', function() {
            echo 'baz';
        });
        
        ob_start();
        $numberOfTriggeredEvents = $this->events->trigger('test_event');
        $this->assertEquals(ob_get_clean(), 'barbaz');
        $this->assertEquals(2, $numberOfTriggeredEvents);
    }
    
    
    public function testUnbindHookname()
    {
        $this->events->bind('test_event', 'foo', function() {
            echo 'bar';
        });
        $this->events->bind('test_event', 'bar', function() {
            echo 'baz';
        });
        $this->events->unbind('test_event', 'foo');
        
        ob_start();
        $this->events->trigger('test_event');
        $this->assertEquals(ob_get_clean(), 'baz');
    }
    
    
    public function testUnbindEventname()
    {
        $this->events->bind('test_event', 'foo', function() {
            echo 'bar';
        });
        $this->events->bind('test_event', 'bar', function() {
            echo 'baz';
        });
        $this->events->unbind('test_event');
        
        ob_start();
        $numberOfTriggeredEvents = $this->events->trigger('test_event');
        $this->assertEquals(ob_get_clean(), '');
        $this->assertEquals(0, $numberOfTriggeredEvents);
    }
    
    
    public function testTriggerEventDoesNotExistDoesNotCauseError()
    {
        ob_start();
        $numberOfTriggeredEvents = $this->events->trigger('nonexistant_event_name');
        $this->assertEquals(ob_get_clean(), '');
        $this->assertEquals(0, $numberOfTriggeredEvents);
    }
    
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBindInvalidCallback()
    {
        $this->events->bind(__FUNCTION__, 'test_invalid', '1234567890');
    }
    
    
    public function testNamespace()
    {
        $ev = $this->kernel->events('custom');
        $this->assertEquals($ev->ns(), 'custom');
    }
}