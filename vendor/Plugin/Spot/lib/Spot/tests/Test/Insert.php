<?php
/**
 * @package Spot
 * @link http://spot.os.ly
 */
class Test_Insert extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = false;
	
	public static function setupBeforeClass()
	{
		$mapper = test_spot_mapper();
		$mapper->migrate('Entity_Post');
	}
	
	public function testInsertPostEntity()
	{
		$post = new Entity_Post();
		$mapper = test_spot_mapper();
		$post->title = "Test Post";
		$post->body = "<p>This is a really awesome super-duper post.</p><p>It's really quite lovely.</p>";
		$post->date_created = $mapper->connection('Entity_Post')->dateTime();
		
		$result = $mapper->insert($post); // returns inserted id
		
		$this->assertTrue($result !== false);
	}
	
	public function testInsertPostArray()
	{
		$mapper = test_spot_mapper();
		$post = array(
			'title' => "Test Post",
			'body' => "<p>This is a really awesome super-duper post.</p><p>It's really quite lovely.</p>",
			'date_created' => $mapper->connection('Entity_Post')->dateTime()
			);
		
		$result = $mapper->insert('Entity_Post', $post); // returns inserted id
		
		$this->assertTrue($result !== false);
	}
}