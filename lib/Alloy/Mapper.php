<?php
/**
 * Base Mapper that module mappers will extend
 * 
 * Dependencies:
 *	- phpDataMapper
 */
abstract class Alloy_Mapper extends phpDataMapper_Base
{
	protected $_entityClass = 'Alloy_Mapper_Entity';
	protected static $_migrateDone = array();

	/**
	 * Custom constructor for auto-migrations
	 */
	public function __construct(phpDataMapper_Adapter_Interface $adapter, $adapterRead = null)
	{
			// Parent constructor
			parent::__construct($adapter, $adapterRead);
			
			// Auto-migrate when in 'development' mode
			if(cx()->config('cx.mode.development') === true) {
					if(!isset(self::$_migrateDone[get_class($this)])) {
							$this->migrate();
							self::$_migrateDone[get_class($this)] = true;
					}
			}
	}
}