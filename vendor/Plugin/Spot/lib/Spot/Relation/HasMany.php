<?php
namespace Spot\Relation;

/**
 * DataMapper class for 'has many' relations
 *
 * @package Spot
 * @link http://spot.os.ly
 */
class HasMany extends RelationAbstract implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * Load query object with current relation data
     *
     * @return \Spot\Query
     */
    protected function toQuery()
    {
        return $this->mapper()->all($this->entityName(), $this->conditions())->order($this->relationOrder());
    }
    
    
    /**
     * Find first entity in the set
     *
     * @return \Spot\Entity
     */
    public function first()
    {
        return $this->toQuery()->first();
    }
    
    
    /**
     * SPL Countable function
     * Called automatically when attribute is used in a 'count()' function call
     *
     * @return int
     */
    public function count()
    {
        $results = $this->execute();
        return $results ? count($results) : 0;
    }
    
    
    /**
     * SPL IteratorAggregate function
     * Called automatically when attribute is used in a 'foreach' loop
     *
     * @return \Spot\Entity\Collection
     */
    public function getIterator()
    {
        // Load related records for current row
        $data = $this->execute();
        return $data ? $data : array();
    }
    
    
    // SPL - ArrayAccess functions
    // ----------------------------------------------
    public function offsetExists($key) {
        $this->execute();
        return isset($this->_collection[$key]);
    }
    
    public function offsetGet($key) {
        $this->execute();
        return $this->_collection[$key];
    }
    
    public function offsetSet($key, $value) {
        $this->execute();
    
        if($key === null) {
            return $this->_collection[] = $value;
        } else {
            return $this->_collection[$key] = $value;
        }
    }
    
    public function offsetUnset($key) {
        $this->execute();
        unset($this->_collection[$key]);
    }
    // ----------------------------------------------
}
