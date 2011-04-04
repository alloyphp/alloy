<?php
namespace Spot;

/**
 * Query Object - Used to build adapter-independent queries PHP-style
 * 
 * @package Spot
 * @author Vance Lucas <vance@vancelucas.com>
 * @link http://spot.os.ly
 */
class Query implements \Countable, \IteratorAggregate
{
    protected $_mapper;
    protected $_entityName;
    
    // Storage for query properties
    public $fields = array();
    public $datasource;
    public $conditions = array();
    public $search = array();
    public $order = array();
    public $group = array();
    public $limit;
    public $offset;
    
    
    /**
     *	Constructor Method
     *
     *	@param Spot_Mapper
     *	@param string $entityName Name of the entity to query on/for
     */
    public function __construct(\Spot\Mapper $mapper, $entityName)
    {
        $this->_mapper = $mapper;
        $this->_entityName = $entityName;
    }
    
    
    /**
     * Get current adapter object
     */
    public function mapper()
    {
        return $this->_mapper;
    }
    
    
    /**
     * Get current entity name query is to be performed on
     */
    public function entityName()
    {
        return $this->_entityName;
    }
    
    
    /**
     * Called from mapper's select() function
     * 
     * @param mixed $fields (optional)
     * @param string $source Data source name
     * @return string
     */
    public function select($fields = "*", $datasource = null)
    {
        $this->fields = (is_string($fields) ? explode(',', $fields) : $fields);
        if(null !== $datasource) {
            $this->from($datasource);
        }
        return $this;
    }
    
    
    /**
     * From
     *
     * @param string $datasource Name of the data source to perform a query on
     * @todo Support multiple sources/joins
     */
    public function from($datasource = null)
    {
        $this->datasource = $datasource;
        return $this;
    }
    
    
    /**
     * Find records with given conditions
     * If all parameters are empty, find all records
     *
     * @param array $conditions Array of conditions in column => value pairs
     */
    public function all(array $conditions = array())
    {
        return $this->where($conditions);
    }
    
    
    /**
     * WHERE conditions
     * 
     * @param array $conditions Array of conditions for this clause
     * @param string $type Keyword that will separate each condition - "AND", "OR"
     * @param string $setType Keyword that will separate the whole set of conditions - "AND", "OR"
     */
    public function where(array $conditions = array(), $type = "AND", $setType = "AND")
    {
        // Don't add WHERE clause if array is empty (easy way to support dynamic request options that modify current query)
        if($conditions) {
            $where = array();
            $where['conditions'] = $conditions;
            $where['type'] = $type;
            $where['setType'] = $setType;
            
            $this->conditions[] = $where;
        }
        return $this;
    }
    public function orWhere(array $conditions = array(), $type = "AND")
    {
        return $this->where($conditions, $type, "OR");
    }
    public function andWhere(array $conditions = array(), $type = "AND")
    {
        return $this->where($conditions, $type, "AND");
    }


    /**
     * Search criteria (FULLTEXT, LIKE, or REGEX, depending on storage engine and driver)
     *
     * @param mixed $fields Single string field or array of field names to use for searching
     * @param string $query Search keywords or query
     * @param array $options Array of options to pass to db engine
     * @return $this
     */
    public function search($fields, $query, array $options = array())
    {
        $fields = (array) $fields;
        $entityDatasourceOptions = $this->mapper()->entityManager()->datasourceOptions($this->entityName());
        $fieldString = '`' . implode('`, `', $fields) . '`';
        $fieldTypes = $this->mapper()->fields($this->entityName());

        // See if we can use FULLTEXT search
        $whereType = ':like';
        $connection = $this->mapper()->connection($this->entityName());
        // Only on MySQL
        if($connection instanceof \Spot\Adapter\Mysql) {
            // Only for MyISAM engine
            if(isset($entityDatasourceOptions['engine'])) {
                $engine = $entityDatasourceOptions['engine'];
                if('myisam' == strtolower($engine)) {
                    $whereType = ':fulltext';
                    // Only if ALL included columns allow fulltext according to entity definition
                    if(in_array($fields, array_keys($this->mapper()->fields($this->entityName())))) {
                        // FULLTEXT
                        $whereType = ':fulltext';
                    }
                }
            }
        }

        // @todo Normal queries can't search mutliple fields, so make them separate searches instead of stringing them together

        // Resolve search criteria
        return $this->where(array($fieldString . ' ' . $whereType => $query));
    }
    
    
    /**
     * ORDER BY columns
     *
     * @param array $fields Array of field names to use for sorting
     * @return $this
     */
    public function order($fields = array())
    {
        $orderBy = array();
        $defaultSort = "ASC";
        if(is_array($fields)) {
            foreach($fields as $field => $sort) {
                // Numeric index - field as array entry, not key/value pair
                if(is_numeric($field)) {
                    $field = $sort;
                    $sort = $defaultSort;
                }
                
                $this->order[$field] = strtoupper($sort);
            }
        } else {
            $this->order[$fields] = $defaultSort;
        }
        return $this;
    }
    
    
    /**
     * GROUP BY clause
     *
     * @param array $fields Array of field names to use for grouping
     * @return $this
     */
    public function group(array $fields = array())
    {
        foreach($fields as $field) {
            $this->group[] = $field;
        }
        return $this;
    }
    
    
    /**
     * Limit executed query to specified amount of records
     * Implemented at adapter-level for databases that support it
     * 
     * @param int $limit Number of records to return
     * @param int $offset Record to start at for limited result set
     */
    public function limit($limit = 20, $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }
    
    
    /**
     * Offset executed query to skip specified amount of records
     * Implemented at adapter-level for databases that support it
     * 
     * @param int $offset Record to start at for limited result set
     */
    public function offset($offset = 0)
    {
        $this->offset = $offset;
        return $this;
    }
    
    
    /**
     * Return array of parameters in key => value format
     *
     * @return array Parameters in key => value format
     */
    public function params()
    {
        $params = array();
        $ci = 0;
        foreach($this->conditions as $i => $data) {
            if(isset($data['conditions']) && is_array($data['conditions'])) {
                foreach($data['conditions'] as $field => $value) {
                    // Column name with comparison operator
                    $colData = explode(' ', $field);
                    $operator = '=';
                    if (count($colData) > 2) {
                        $operator = array_pop($colData);
                        $colData = array(implode(' ', $colData), $operator);
                    }
                    $field = $colData[0];
                    $params[$field . $ci] = $value;
                    $ci++;
                }
            }
        }
        return $params;
    }
    
    
    
    
    
    // ===================================================================
    
    /**
     * SPL Countable function
     * Called automatically when attribute is used in a 'count()' function call
     *
     * @return int
     */
    public function count()
    {
        // Execute query and return count
        $result = $this->execute();
        return ($result !== false) ? count($result) : 0;
    }
    
    
    /**
     * SPL IteratorAggregate function
     * Called automatically when attribute is used in a 'foreach' loop
     *
     * @return Spot_Query_Set
     */
    public function getIterator()
    {
        // Execute query and return result set for iteration
        $result = $this->execute();
        return ($result !== false) ? $result : array();
    }
    
    
    /**
     * Convenience function passthrough for Collection
     *
     * @return array 
     */
    public function toArray($keyColumn = null, $valueColumn = null)
    {
        $result = $this->execute();
        return ($result !== false) ? $result->toArray($keyColumn, $valueColumn) : array();
    }
    
    
    /**
     * Return the first entity matched by the query
     *
     * @return mixed Spot_Entity on success, boolean false on failure
     */
    public function first()
    {
        $result = $this->limit(1)->execute();
        return ($result !== false) ? $result->first() : false;
    }
    
    
    /**
     * Execute and return query as a collection
     * 
     * @return mixed Collection object on success, boolean false on failure
     */
    public function execute()
    {
        return $this->mapper()->connection($this->entityName())->read($this);
    }
}