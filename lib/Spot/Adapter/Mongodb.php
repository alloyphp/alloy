<?php
namespace Spot\Adapter;

/**
 * MongoDB Adapter
 *
 * @package Spot
 * @link http://spot.os.ly
 */
class Mongodb extends AdapterAbstract implements AdapterInterface
{
	// Format for date columns, formatted for PHP's date() function
	protected $_format_date = 'Y-m-d';
	protected $_format_time = 'H:i:s';
	protected $_format_datetime = 'Y-m-d H:i:s';
	
	// Current mongo database we're working with
	protected $_mongoDatabase;
	
	
	/**
	 * Get database connection
	 * 
	 * @return object Mongo
	 */
	public function connection()
	{
		if(!$this->_connection) {
	    	if($this->_dsn instanceof \Mongo) {
				$this->_connection = $this->_dsn;
			} else {
				// Establish connection
				try {
					$this->_connection = new \Mongo($this->_dsn, $this->_options);
				} catch(Exception $e) {
					throw new \Spot\Exception($e->getMessage());
				}
			}
		}
		return $this->_connection;
	}
	
	
	/**
	 * Get database format
	 *
	 * @return string Date format for PHP's date() function
	 */
	public function dateFormat()
	{
		return $this->format_date;
	}
	
	
	/**
	 * Get database time format
	 *
	 * @return string Time format for PHP's date() function
	 */
	public function timeFormat()
	{
		return $this->format_time;
	}
	
	
	/**
	 * Get database format
	 *
	 * @return string DateTime format for PHP's date() function
	 */
	public function dateTimeFormat()
	{
		return $this->format_datetime;
	}
	
	
	/**
	 * Get date
	 *
	 * @return object MongoDate
	 */
	public function date($format = null)
	{
		// MongoDB only supports timestamps for now, not direct DateTime objects
		$format = parent::date($format)->format('U');
		return new \MongoDate($format);
	}
	
	
	/**
	 * Get database time format
	 *
	 * @return object MongoDate 
	 */
	public function time($format = null)
	{
		// MongoDB only supports timestamps for now, not direct DateTime objects
		$format = parent::time($format)->format('U');
		return new \MongoDate($format);
	}
	
	
	/**
	 * Get datetime
	 *
	 * @return object MongoDate
	 */
	public function dateTime($format = null)
	{
		// MongoDB only supports timestamps for now, not direct DateTime objects
		$format = parent::dateTime($format)->format('U');
		return new \MongoDate($format);
	}
	
	
	/**
	 * Escape/quote direct user input
	 *
	 * @param string $string
	 */
	public function escape($string)
	{
		return $string; // Don't think Mongo needs escaping, it's not SQL, and the JSON encoding takes care of properly escaping values...
	}
	
	
	/**
	 * Migrate structure changes to database
	 * 
	 * @param String $datasource Datasource name
	 * @param Array $fields Fields and their attributes as defined in the mapper
	 */
	public function migrate($datasource, array $fields)
	{
        return true; // For now...
		$collection = $this->mongoCollection($datasource);
        
        // Ensure fields are indexed
		foreach($fields as $field => $opts) {
			if($opts['primary'] !== false || $opts['index'] !== false || $opts['unique'] !== false) {
				$indexOpts = array('safe' => true);
				if($opts['unique'] !== false) {
					$indexOpts['unique'] = true;
				}
				$collection->ensureIndex($field, $indexOpts);
			}
		}
		
		return true; // Whoo! NoSQL doesn't have set schema! No column/field nonsense!
	}
	
	
	/**
	 * Create new entity record with set properties
	 */
	public function create($datasource, array $data, array $options = array())
	{
		// @link http://us3.php.net/manual/en/mongocollection.insert.php
		$saved = $this->mongoCollection($datasource)->insert($data, array('safe' => true));
		return ($saved) ? (string) $data['_id'] : false;
	}
	
	
	/**
	 * Build a Mongo query
	 *
	 * Finding: @link http://us.php.net/manual/en/mongocollection.find.php
	 * Fields: @link http://us.php.net/manual/en/mongocursor.fields.php
	 * Cursor: @link http://us.php.net/manual/en/class.mongocursor.php
	 * Sorting: @link http://us.php.net/manual/en/mongocursor.sort.php
	 */
	public function read(\Spot\Query $query, array $options = array())
	{
		// Get MongoCursor first - it's required for other options
		$criteria = $this->queryConditions($query);
		$cursor = $this->mongoCollection($query->datasource)->find($criteria);
		
		// Organize 'order' options for sorting
		$order = array();
		if($query->order) {
			foreach($query->order as $oField => $oSort) {
				// MongoDB sorting: ASC: 1,  DESC: 2
				$order[$oField] = ($oSort == 'DESC') ? 2 : 1;
			}
		}
		$cursor->sort($order);
		
		// @todo GROUP BY - Not supported YET (!)
		// @link http://www.mongodb.org/display/DOCS/Aggregation#Aggregation-Group
		if($query->group) {
			throw new \Spot\Exception("Grouping for the Mongo adapter has not currently been implemented. Would you like to contribute? :)");
		}
		
		// LIMIT & OFFSET (Skip)
		if($query->limit) {
			$cursor->limit($query->limit);
		}
		if($query->offset) {
			$cursor->skip($query->offset);
		}
		
		// Add query to log
		Spot_Log::addQuery($this, $criteria);

		// Return collection
		return $this->toCollection($query, $cursor);
	}
	
	/**
	 * Update entity
	 */
	public function update($datasource, array $data, array $where = array(), array $options = array())
	{
		// @todo Check on the _id field to ensure it is set - Mongo can only 'update' existing records or you get an exception
		
		$criteria = $this->queryConditions($where);
		// Update multiple entries by default, the same way RDBMS do
		$mongoQuery = $this->mongoCollection($datasource)
			->update($criteria, array('$set' => $data), array('safe' => true, 'multiple' => true));
		
		return $mongoQuery;
	}
	
	
	/**
	 * Delete entities matching given conditions
	 *
	 * @param string $datasource Name of data source
	 * @param array $conditions Array of conditions in column => value pairs
	 */
	public function delete($datasource, array $where, array $options = array())
	{
		// Get MongoCursor first - it's required for other options
		$criteria = $this->queryConditions($where);
		$mongoQuery = $this->mongoCollection($datasource)->remove($criteria);
		return $mongoQuery;
	}
	
	
	/**
	 * Returns query conditions in a way that is formatted for Mongo to use
	 *
	 * @param mixed Spot_Query object or associative array
	 */
	public function queryConditions($query)
	{
		$conditions = $query;
		if(is_object($query) && $query instanceof \Spot\Query) {
			$conditions = $query->conditions;
		}
		
		if(count($conditions) == 0) { return array(); }
		
		$opts = array();
		$loopOnce = false;
		foreach($conditions as $condition) {
			if(is_array($condition) && isset($condition['conditions'])) {
				$subConditions = $condition['conditions'];
			} else {
				$subConditions = $conditions;
				$loopOnce = true;
			}
			foreach($subConditions as $field => $value) {
				// Handle binding depending on type
				if(is_object($value)) {
					if($value instanceof \DateTime) {
						// @todo Need to take into account column type for date formatting
						$fieldType = $query->mapper()->fieldType($field);
						$dateTimeFormat = ($fieldType == 'date' ? $this->dateFormat() : ($fieldType == 'time' ? $this->timeFormat() : $this->dateTimeFormat()));
						$value = (string) $value->format($dateTimeFormat);
					} else {
						// Attempt cast of object to string (calls object's __toString method)
						// Will cause E_FATAL if object cannot be cast to string
						if(!($value instanceof MongoId)) {
							$value = (string) $value;
						}
					}
				}
				
				// Column name with comparison operator
				$colData = explode(' ', $field);
				$operator = isset($colData[1]) ? $colData[1] : '=';
				if(count($colData) > 2) {
					$operator = array_pop($colData);
					$colData = array( implode(' ', $colData), $operator);
				}
				$col = $colData[0];

                // Automatic conversion for Mongo's '_id' field from mappers with 'id' set
                if('id' == $col && isset($this->options['mapper']['translate_id']) && true === $this->options['mapper']['translate_id']) {
                    $col = '_id';
                }

				// @todo MERGE these array values on the column so they don't overwrite each other
				switch($operator) {
					case '<':
					case ':lt':
						$value = array('$lt' => $value);
					break;
					case '<=':
					case ':lte':
						$value = array('$lte' => $value);
					break;
					case '>':
					case ':gt':
						$value = array('$gt' => $value);
					break;
					case '>=':
					case ':gte':
						$value = array('$gte' => $value);
					break;
					// ALL (Custom to Mongo, but can be adapted to SQL with some clever WHERE clauses)
					case ':all':
						$value = array('$all' => $value);
					break;
					// Not equal
					case '<>':
					case '!=':
					case ':ne':
					case ':not':
						if(is_array($value)) {
							$value = array('$nin' => $value); // NOT IN
						} else {
							$value = array('$ne' => $value);
						}
					break;
					// Equals
					case '=':
					case ':eq':
					default:
						if(is_array($value)) {
							$value = array('$in' => $value); // IN
						}
					break;
				}
				
				// Add value to set options
				$opts[$col] = $value;
			}
			if($loopOnce) { break; }
		}
		return $opts;
	}
	
	
	/**
	 * Truncate a collection
	 * Should delete all rows and reset serial/auto_increment keys to 0
	 *
	 * @param string $datasource Collection name
	 */
	public function truncateDatasource($datasource)
	{
		return $this->mongoCollection($datasource)->remove(array());
	}
	
	
	/**
	 * Drop a collection
	 * Destructive and dangerous - drops entire table and all data
	 *
	 * @param string $datasource Collection name
	 */
	public function dropDatasource($datasource)
	{
		return $this->mongoCollection($datasource)->drop();
	}
	
	
	/**
	 * Create a database
 	 * Will throw errors if user does not have proper permissions
 	 *
 	 * @param string $database Database name
	 */
	public function createDatabase($database)
	{
		return false;
	}
	
	
	/**
	 * Drop a database
	 * Destructive and dangerous - drops entire database and all data
	 * Will throw errors if user does not have proper permissions
	 *
	 * @param string $database Database name
	 */
	public function dropDatabase($database)
	{
		return false;
	}
	
	
	/**
	 * Return result set for current query
	 */
	public function toCollection(\Spot\Query $query, \MongoCursor $cursor)
	{
		$mapper = $query->mapper();
		$entityClass = $query->entityName();
		if($cursor instanceof MongoCursor) {
			// Set timeout
			if(isset($this->options['cursor']['timeout']) && is_int($this->options['cursor']['timeout'])) {
				$cursor->timeout($this->options['cursor']['timeout']);
			}
			
			return $mapper->collection($entityClass, $cursor);
			
		} else {
			$mapper->addError(__METHOD__ . " - Unable to execute query - not a valid MongoCursor");
			return array();
		}
	}
	
	
	/**
	 * Returns current Mongo database to use
	 */
	public function mongoDatabase()
	{
		if(!$this->_mongoDatabase) {
			if(empty($this->_dsnParts['database'])) {
				throw new \Spot\Exception("Mongo must have a database to connect to. No database name was specified.");
			}
			$this->_mongoDatabase = $this->connection()->selectDB($this->_dsnParts['database']);
		}
		return $this->_mongoDatabase;
	}
	
	
	/**
	 * Returns a Mongo collection to use
     * The magic of Mongo will create the collection as needed if it does not exist
	 */
	public function mongoCollection($collectionName)
	{
        return $this->mongoDatabase()->$collectionName;
	}
}
