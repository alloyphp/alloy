<?php
use \Spot;
/**
 * @package Spot
 * @link http://spot.os.ly
 */
class Test_Entity_Collection extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = false;
	protected $collection;

	/**
	 * Setup/fixtures for each test
	 */
	public function setUp()
	{
		// New mapper instance
		$this->collection = new \Spot\Entity\Collection();

	}
	public function tearDown() {}

	/**
	* New collection has size 0
	Collection with one element has size 1
	Collection with two elements has size 2
	Two collection, size 1 and size 2 merged have size 3
	collection from array with 0 elements has size 0
	collection from array with 2 elements has size 2
	toString of collection with 2 elements

	@todo The following Collection tests
	collection with two elements, first is first
	after iterating through collection, first is first
	arrayAccess[0] of empty collection
	arrayAccess[1] of collection with 2 element is second element
	*/


	public function testNewCollectionHasSizeZero()
	{
		$this->assertEquals(0, count($this->collection));
	}

	public function testCollectionWithOneElementSize1()
	{
		$entity = new Entity_Post();
		$this->collection->add($entity);
		$this->assertEquals(1, count($this->collection));
	}

	public function testCollectionWithTwoElementsSize2()
	{
		$this->collection->add(new Entity_Post());
		$this->collection->add(new Entity_Post());
		$this->assertEquals(2, count($this->collection));
	}

	public function testMergedCollectionHasCorrectSize()
	{
		$collection2 = new \Spot\Entity\Collection();
		$collection2->add(new Entity_Post(array('key'=>'value')));
		$collection2->add(new Entity_Post(array('key'=>'value1')));

		$this->collection->add(new Entity_Post(array('key'=>'value2')));

		$this->assertEquals(1, count($this->collection));
		$this->assertEquals(2, count($collection2));

		$this->collection->merge($collection2);
		$this->assertEquals(3, count($this->collection));
	}

	public function testMergeCollectionReturnsCollection()
	{
		$collection2 = new \Spot\Entity\Collection();
		$collection2->add(new Entity_Post(array('key'=>'value')));
		$this->assertTrue($this->collection->merge($collection2) instanceOf \Spot\Entity\Collection);
	}

	public function testMergeIsUnique()
	{
		$collection2 = new \Spot\Entity\Collection();
		$this->collection->add(new Entity_Post(array('foo' => 'bar')));

		$collection2->add(new Entity_Post(array('foo' => 'bar')));
		$collection2->add(new Entity_Post());

		$collection2->merge($this->collection);
		$this->assertEquals(2, count($collection2));
	}

	public function testMergeIsNotUnique()
	{
		$collection2 = new \Spot\Entity\Collection();
		$this->collection->add(new Entity_Post(array('foo' => 'bar')));

		$collection2->add(new Entity_Post(array('foo' => 'bar')));
		$collection2->add(new Entity_Post());

		$collection2->merge($this->collection, false);
		$this->assertEquals(3, count($collection2));
	}

	public function testFromArraySize0()
	{
		$this->collection = new \Spot\Entity\Collection(array());
		$this->assertEquals(0, count($this->collection));
	}
	public function testFromArraySize2()
	{
		$arr = array(
			new Entity_Post(),
			new Entity_Post()
		);
		$this->collection = new \Spot\Entity\Collection($arr);
		$this->assertEquals(2, count($this->collection));
	}

	public function testEmptyCollectionCanIterate()
	{
		foreach($this->collection as $entity)
		{
			$this->assertTrue($entity instanceOf Entity_Post);
		}
	}

	public function testToString()
	{
		$this->assertEquals("Spot\\Entity\\Collection[0]", (string) $this->collection);
		$this->collection->add(new Entity_Post());

		$this->assertEquals("Spot\\Entity\\Collection[1]", (string) $this->collection);
	}
}