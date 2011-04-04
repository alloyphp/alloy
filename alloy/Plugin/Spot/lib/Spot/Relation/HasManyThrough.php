<?php
namespace Spot\Relation;

/**
 * DataMapper class for 'has many' relations
 *
 * @package Spot
 * @link http://spot.os.ly
 */
class HasManyThrough extends RelationAbstract implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * Load query object with current relation data
     *
     * @return \Spot\Query
     */
    protected function toQuery()
    {
        // "Through" Entity
        $throughEntity = isset($this->_relationData['throughEntity']) ? $this->_relationData['throughEntity'] : null;
        if(null === $throughEntity) {
            throw new \InvalidArgumentException("Relation description key 'throughEntity' not set.");
        }

        // "Through" WHERE conditions
        $throughWhere = isset($this->_relationData['throughWhere']) ? $this->_relationData['throughWhere'] : array();
        if(!$throughWhere || !is_array($throughWhere)) {
            throw new \InvalidArgumentException("Relation description key 'throughWhere' not set or is not a valid array.");
        }
        $throughWhereResolved = $this->resolveEntityConditions($this->sourceEntity(), $throughWhere);

        // Get IDs of "Through" entites
        $throughEntities = $this->mapper()
            ->all($throughEntity, $throughWhereResolved);

        $twe = false;
        if(count($throughEntities) > 0) {
            // Resolve "where" conditions with current entity object and all returned "Through" entities
            //$tw = $this->resolveEntityConditions($this->sourceEntity(), $this->conditions());
            $tw = array();  
            $twe = array();
            foreach($throughEntities as $tEntity) {
                $twe = array_merge_recursive($twe, $this->resolveEntityConditions($tEntity, $this->conditions(), ':throughEntity.'));
            }
            // Remove dupes from values if present (array of IDs or other identical values from array_merge_recursive)
            foreach($twe as $k => $v) {
                if(is_array($v)) {
                    $twe[$k] = array_unique($v);
                }
            }
        }

        //var_dump($tw, $twe);
        //exit();

        if(false !== $twe) {
            // Get actual entites user wants
            $entities = $this->mapper()
                ->all($this->entityName(), $twe)
                ->order($this->relationOrder());
            return $entities;
        }

        return false;
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
        return $data ? $data : new \Spot\Entity\Collection();
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
