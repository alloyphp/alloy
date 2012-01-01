<?php
/**
 * @package Spot
 * @link http://spot.os.ly
 */
class Test_CRUD extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    public static function setupBeforeClass()
    {
        $mapper = test_spot_mapper();
        $mapper->migrate('Entity_Post');
    }
    public static function tearDownAfterClass()
    {
        $mapper = test_spot_mapper();
        $mapper->truncateDatasource('Entity_Post');
    }

    public function testSampleNewsInsert()
    {
        $mapper = test_spot_mapper();
        $post = $mapper->get('Entity_Post');
        $post->title = "Test Post";
        $post->body = "<p>This is a really awesome super-duper post.</p><p>It's really quite lovely.</p>";
        $post->date_created = $mapper->connection('Entity_Post')->date();
        $result = $mapper->insert($post); // returns an id

        $this->assertTrue($result !== false);
    }

    public function testSampleNewsInsertWithEmptyNonRequiredFields()
    {
        $mapper = test_spot_mapper();
        $post = $mapper->get('Entity_Post');
        $post->title = "Test Post With Empty Values";
        $post->body = "<p>Test post here.</p>";
        $post->date_created = null;
        try {
            $result = $mapper->insert($post); // returns an id
        } catch(Exception $e) {
            $result = false;
        }

        $this->assertTrue($result !== false);
    }

    public function testSelect()
    {
        $mapper = test_spot_mapper();
        $post = $mapper->first('Entity_Post', array('title' => "Test Post"));

        $this->assertTrue($post instanceof Entity_Post);
    }

    public function testSampleNewsUpdate()
    {
        $mapper = test_spot_mapper();
        $post = $mapper->first('Entity_Post', array('title' => "Test Post"));
        $this->assertTrue($post instanceof Entity_Post);

        $post->title = "Test Post Modified";
        $result = $mapper->update($post); // returns boolean
        
        // TESTING
        //var_dump(\Spot\Log::lastQuery());
        //exit();
        
        $postu = $mapper->first('Entity_Post', array('title' => "Test Post Modified"));
        $this->assertTrue($postu instanceof Entity_Post);
    }

    public function testSampleNewsDelete()
    {
        $mapper = test_spot_mapper();
        $post = $mapper->first('Entity_Post', array('title' => "Test Post Modified"));
        $result = $mapper->delete($post);

        $this->assertTrue((boolean) $result);
    }
}