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
     * @return Spot_Query
     */
    protected function toQuery()
    {
        return $this->mapper()->all($this->entityName(), $this->conditions())->order($this->relationOrder());
    }
    
    
    /**
     * Find first entity in the set
     *
     * @return Spot_Entity
     */
    public function first()
    {
        return $this->all()->first();
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
     * @return Spot_Entity_Collection
     */
    public function getIterator()
    {
        // Load related records for current row
        $data = $this->execute();
        return $data ? $data : array();
    }
    
    
    /**
     * Passthrough for method chaining on the Query object
     */
    public function __call($func, $args)
    {
        return call_user_func_array(array($this->execute(), $func), $args);
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
