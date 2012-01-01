<?php
/**
 * @package Spot
 * @link http://spot.os.ly
 */
class Test_Relations extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = false;
    
    public static function setupBeforeClass()
    {
        $mapper = test_spot_mapper();
        $mapper->migrate('Entity_Post');
        $mapper->migrate('Entity_Post_Comment');
    }
    public static function tearDownAfterClass()
    {
        $mapper = test_spot_mapper();
        $mapper->truncateDatasource('Entity_Post');
        $mapper->truncateDatasource('Entity_Post_Comment');
    }
    
	public function testBlogPostInsert()
	{
        $mapper = test_spot_mapper();
		$post = $mapper->get('Entity_Post');
		$post->title = "My Awesome Blog Post";
		$post->body = "<p>This is a really awesome super-duper post.</p><p>It's testing the relationship functions.</p>";
		$post->date_created = $mapper->connection('Entity_Post')->dateTime();
		$postId = $mapper->insert($post);
        
		$this->assertTrue($postId !== false);
        
		// Test selcting it to ensure it exists
		$postx = $mapper->get('Entity_Post', $postId);
		$this->assertTrue($postx instanceof Entity_Post);

		return $postId;
	}

	/**
	 * @depends testBlogPostInsert
	 */
	public function testBlogCommentsRelationInsertByObject($postId)
	{
        $mapper = test_spot_mapper();
		$post = $mapper->get('Entity_Post', $postId);

		// Array will usually come from POST/JSON data or other source
		$commentSaved = false;
		$comment = $mapper->get('Entity_Post_Comment');
		$mapper->data($comment, array(
				'post_id' => $postId,
				'name' => 'Testy McTester',
				'email' => 'test@test.com',
				'body' => 'This is a test comment. Yay!',
				'date_created' => new \DateTime()
			));
		try {
			$commentSaved = $mapper->save($comment);
			if(!$commentSaved) {
				print_r($comment->errors());
				$this->fail("Comment NOT saved");
			}
		} catch(Exception $e) {
			echo __FUNCTION__ . ": " . $e->getMessage() . "\n";
			/*
			echo $e->getTraceAsString();
			$commentMapper->debug();
			exit();
			*/
		}
		$this->assertTrue(false !== $commentSaved);
	}

	/**
	 * @depends testBlogPostInsert
	 */
	public function testBlogCommentsCanIterateEntity($postId)
	{
        $mapper = test_spot_mapper();
		$post = $mapper->get('Entity_Post', $postId);

		foreach($post->comments as $comment) {
			$this->assertTrue($comment instanceOf Entity_Post_Comment);
		}
	}

	public function testHasManyRelationCountZero()
	{
        $mapper = test_spot_mapper();
		$post = $mapper->get('Entity_Post');
		$post->title = "No Comments";
        $post->body = "<p>Comments relation test</p>";
		$mapper->save($post);
        
		$this->assertSame(0, count($post->comments));
	}

	public function testBlogCommentsIterateEmptySet()
	{
        $mapper = test_spot_mapper();
		$post = $mapper->get('Entity_Post');
		$post->title = "No Comments";
        $post->body = "<p>Comments relation test</p>";
		$mapper->save($post);

		// Testing that we can iterate over an empty set
		foreach($post->comments as $comment) {
			$this->assertTrue($comment instanceOf Entity_Post_Comment);
		}
	}

	/**
	 * @depends testBlogPostInsert
	 */
	public function testBlogCommentsRelationCountOne($postId)
	{
        $mapper = test_spot_mapper();
		$post = $mapper->get('Entity_Post', $postId);
		$this->assertTrue(count($post->comments) == 1);
	}

	/**
	 * @depends testBlogPostInsert
	 */
	public function testBlogCommentsRelationReturnsRelationObject($postId)
	{
        $mapper = test_spot_mapper();
		$post = $mapper->get('Entity_Post', $postId);
		$this->assertTrue($post->comments instanceof \Spot\Relation\RelationAbstract);
	}

	/**
	 * @depends testBlogPostInsert
	 */
	public function testBlogCommentsRelationCanBeModified($postId)
	{
        $mapper = test_spot_mapper();
		$post = $mapper->get('Entity_Post', $postId);
        $this->assertTrue($post->comments instanceof \Spot\Relation\HasMany);
		$sortedComments = $post->comments->order(array('date_created' => 'DESC'));
		$this->assertTrue($sortedComments instanceof \Spot\Query);
	}
}