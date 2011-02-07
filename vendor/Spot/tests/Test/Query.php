<?php
/**
 * @package Spot
 * @link http://spot.os.ly
 */
class Test_Query extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = false;
	
	/**
	 * Prepare the data
	 */
	public static function setUpBeforeClass()
	{
		$mapper = test_spot_mapper();
		
		$mapper->migrate('Entity_Post');
		$mapper->truncateDatasource('Entity_Post');
		
		$mapper->migrate('Entity_Post_Comment');
		$mapper->truncateDatasource('Entity_Post_Comment');

		// Insert blog dummy data
		for( $i = 1; $i <= 10; $i++ ) {
			$mapper->insert('Entity_Post', array(
				'title' => ($i % 2 ? 'odd' : 'even' ). '_title',
				'body' => '<p>' . $i  . '_body</p>',
				'status' => $i ,
				'date_created' => $mapper->connection('Entity_Post')->dateTime()
			));
		}
	}
	
	public function testQueryInstance()
	{
		$mapper = test_spot_mapper();
        $posts = $mapper->all('Entity_Post', array('title' => 'even_title'));
        $this->assertTrue($posts instanceof \Spot\Query);
	}
	
	public function testQueryCollectionInstance()
	{
		$mapper = test_spot_mapper();
        $posts = $mapper->all('Entity_Post', array('title' => 'even_title'));
        $this->assertTrue($posts instanceof \Spot\Query);
		$this->assertTrue($posts->execute() instanceof \Spot\Entity\Collection);
	}
	
	public function testOperatorNone()
	{
		$mapper = test_spot_mapper();
		$post = $mapper->first('Entity_Post', array('status' => 2));
		$this->assertEquals(2, $post->status);
	}

	// Equals
	public function testOperatorEq()
	{
		$mapper = test_spot_mapper();
		$post = $mapper->first('Entity_Post', array('status =' => 2));
		$this->assertEquals(2, $post->status);
		$post = $mapper->first('Entity_Post', array('status :eq' => 2));
		$this->assertEquals(2, $post->status);
	}
	
	// Less than
	public function testOperatorLt()
	{
		$mapper = test_spot_mapper();
		$this->assertEquals(4, $mapper->all('Entity_Post', array('status <' => 5))->count());
		$this->assertEquals(4, $mapper->all('Entity_Post', array('status :lt' => 5))->count());
	}

	// Greater than
	public function testOperatorGt()
	{
		$mapper = test_spot_mapper();
		$this->assertFalse($mapper->first('Entity_Post', array('status >' => 10)));
		$this->assertFalse($mapper->first('Entity_Post', array('status :gt' => 10)));
	}

	// Greater than or equal to
	public function testOperatorGte()
	{
		$mapper = test_spot_mapper();
		$this->assertEquals(6, $mapper->all('Entity_Post', array('status >=' => 5))->count());
		$this->assertEquals(6, $mapper->all('Entity_Post', array('status :gte' => 5))->count());
	}
	
	// Use same column name more than once
	public function testFieldMultipleUsage()
	{
		$mapper = test_spot_mapper();
		$countResult = $mapper->all('Entity_Post', array('status' => 1))->orWhere(array('status' => 2))->count();
		$this->assertEquals(2, $countResult);
	}
	
	public function testArrayDefaultIn()
	{
		$mapper = test_spot_mapper();
		$post = $mapper->first('Entity_Post', array('status' => array(2)));
		$this->assertEquals(2, $post->status);
	}

	public function testArrayInSingle()
	{
		$mapper = test_spot_mapper();

		// Numeric
		$post = $mapper->first('Entity_Post', array('status :in' => array(2)));
		$this->assertEquals(2, $post->status);

		// Alpha
		$post = $mapper->first('Entity_Post', array('status :in' => array('a')));
		$this->assertFalse($post);
	}

	public function testArrayNotInSingle()
	{
		$mapper = test_spot_mapper();
		$post = $mapper->first('Entity_Post', array('status !=' => array(2)));
		$this->assertFalse($post->status == 2);
		$post = $mapper->first('Entity_Post', array('status :not' => array(2)));
		$this->assertFalse($post->status == 2);
	}

	public function testArrayMultiple()
	{
		$mapper = test_spot_mapper();
		$posts = $mapper->all('Entity_Post', array('status' => array(3,4,5)));
		$this->assertEquals(3, $posts->count());
		$posts = $mapper->all('Entity_Post', array('status :in' => array(3,4,5)));
		$this->assertEquals(3, $posts->count());
	}

	public function testArrayNotInMultiple()
	{
		$mapper = test_spot_mapper();
		$posts = $mapper->all('Entity_Post', array('status !=' => array(3,4,5)));
		$this->assertEquals(7, $posts->count());
		$posts = $mapper->all('Entity_Post', array('status :not' => array(3,4,5)));
		$this->assertEquals(7, $posts->count());
	}
}