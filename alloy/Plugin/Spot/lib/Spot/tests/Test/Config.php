<?php
/**
 * @package Spot
 * @link http://spot.os.ly
 */
class Test_Config extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = false;

	public function testAddConnectionWithDSNString()
	{
		$cfg = new \Spot\Config();
		$adapter = $cfg->addConnection('test_mysql', 'mysql://test:password@localhost/test');
		$this->assertInstanceOf('\Spot\Adapter\Mysql', $adapter);
	}

  public function testConfigCanSerialize()
  {
    $cfg = new \Spot\Config();
    $adapter = $cfg->addConnection('test_mysql', 'mysql://test:password@localhost/test');

    $this->assertInternalType('string', serialize($cfg));
  }

  public function testConfigCanUnserialize()
  {
    $cfg = new \Spot\Config();
    $adapter = $cfg->addConnection('test_mysql', 'mysql://test:password@localhost/test');

    $this->assertInstanceOf('\Spot\Config', unserialize(serialize($cfg)));
  }
}