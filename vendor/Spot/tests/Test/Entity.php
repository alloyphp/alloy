<?php
require_once dirname(dirname(__FILE__)) . '/init.php';
/**
 * @package Spot
 * @link http://spot.os.ly
 */
class Test_Entity extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;
    
    public function testEntitySetDataProperties()
    {
        $mapper = test_spot_mapper();
        $post = new Entity_Post();
        
        // Set data
        $post->title = "My Awesome Post";
        $post->body = "<p>Body</p>";
        
        $data = $post->data();
        ksort($data);
        
        $testData = array(
            'id' => null,
            'title' => 'My Awesome Post',
            'body' => '<p>Body</p>',
            'status' => 0,
            'date_created' => null
            );
        ksort($testData);
        
        $this->assertEquals($testData, $data);
    }
    
    public function testEntitySetDataConstruct()
    {
        $mapper = test_spot_mapper();
        $post = new Entity_Post(array(
            'title' => 'My Awesome Post',
            'body' => '<p>Body</p>'
        ));
        
        $data = $post->data();
        ksort($data);
        
        $testData = array(
            'id' => null,
            'title' => 'My Awesome Post',
            'body' => '<p>Body</p>',
            'status' => 0,
            'date_created' => null
            );
        ksort($testData);
        
        $this->assertEquals($testData, $data);
    }
}