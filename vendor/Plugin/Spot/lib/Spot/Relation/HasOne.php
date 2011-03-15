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
		return $this->mapper()->all($this->entityName(), $this->conditions())->order($this->relationOrder())->first();
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
}
