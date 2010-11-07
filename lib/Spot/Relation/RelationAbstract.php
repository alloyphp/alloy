<?php
namespace Spot\Relation;

/**
 * Abstract class for relations
 * 
 * @package Spot
 * @link http://spot.os.ly
 */
abstract class RelationAbstract
{
	protected $_mapper;
    protected $_entityName;
	protected $_foreignKeys;
	protected $_conditions;
	protected $_relationData;
	protected $_collection;
	protected $_relationRowCount;
	
	
	/**
	 * Constructor function
	 *
	 * @param object $mapper Spot_Mapper_Abstract object to query on for relationship data
	 * @param array $resultsIdentities Array of key values for given result set primary key
	 */
	public function __construct(\Spot\Mapper $mapper, $entityName, array $relationData)
	{
        $this->_mapper = $mapper;
        $this->_entityName = $entityName;
		$this->_conditions = isset($relationData['where']) ? $relationData['where'] : array();
		$this->_relationData = $relationData;
	}
	
	
    /**
	 * Get related entity name
	 */
	public function entityName()
	{
		return $this->_entityName;
	}
    
    
	/**
	 * Get mapper instance
	 */
	public function mapper()
	{
		return $this->_mapper;
	}
	
	
	/**
	 * Get foreign key relations
	 *
	 * @return array
	 */
	public function conditions()
	{
		return $this->_conditions;
	}
	
	
	/**
	 * Get sorting for relations
	 *
	 * @return array
	 */
	public function relationOrder()
	{
		$sorting = isset($this->_relationData['order']) ? $this->_relationData['order'] : array();
		return $sorting;
	}
	
	
	/**
	 * Called automatically when attribute is printed
	 */
	public function __toString()
	{
		// Load related records for current row
		$success = $this->findAllRelation();
		return ($success) ? "1" : "0";
	}
	
	
	
	/**
	 * Load query object with current relation data
	 *
	 * @return Spot_Query
	 */
	abstract protected function toQuery();
	
	
	/**
	 * Fetch and cache returned query object from internal toQuery() method
	 */
	public function execute()
	{
		if(!$this->_collection) {
			$this->_collection = $this->toQuery();
		}
		return $this->_collection;
	}
}
