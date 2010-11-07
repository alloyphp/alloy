<?php
namespace Spot\Relation;
/**
 * DataMapper class for 'has one' relations
 * 
 * @package Spot
 * @link http://spot.os.ly
 */
class HasOne extends RelationAbstract
{
	/**
	 * Load query object with current relation data
     * 
     * @return Spot_Query
	 */
	protected function toQuery()
	{
		return $this->mapper()->first($this->entityName(), $this->conditions())->order($this->relationOrder());
	}
	
	
	/**
	 * isset() functionality passthrough to entity
	 */
	public function __isset($key)
	{
		$entity = $this->execute();
		if($entity) {
			return isset($entity->$key);
		} else {
			return false;
		}
	}
	
	
	/**
	 * Getter passthrough to entity
	 */
	public function __get($var)
	{
		$entity = $this->execute();
		if($entity) {
			return $entity->$var;
		} else {
			return null;
		}
	}
	
	
	/**
	 * Setter passthrough to entity
	 */
	public function __set($var, $value)
	{
		$entity = $this->execute();
		if($entity) {
			$entity->$var = $value;
		}
	}
	
	
	/**
	 * Passthrough for method calls on entity
	 */
	public function __call($func, $args)
	{
		$entity = $this->execute();
		if($entity) {
			return call_user_func_array(array($entity, $func), $args);
		} else {
			return false;
		}
	}
}
