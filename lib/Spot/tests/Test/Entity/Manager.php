<?php
/**
 * @package Spot
 * @link http://spot.os.ly
 */
class Test_Entity_Manager extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = false;

	public function testEntityFields()
	{
		$mapper = test_spot_mapper();
		$post = new Entity_Post();
		
		$fields = $mapper->fields('Entity_Post');
		$sortedFields = array_keys($fields);
		//ksort($sortedFields);
		
		// Assert $fields are correct
		$testFields = array('id', 'title', 'body', 'status', 'date_created');
		//ksort($testFields);
		$this->assertEquals($sortedFields, $testFields);
	}
	
	public function testEntityRelations()
	{
		$mapper = test_spot_mapper();
		$post = new Entity_Post();
		
		$relations = $mapper->relations('Entity_Post');
		$sortedRelations = array_keys($relations);
		sort($sortedRelations);
		
		// Assert $relations are correct
		$testRelations = array('comments');
		sort($testRelations);
		$this->assertEquals($sortedRelations, $testRelations);
	}
}