<?php
namespace Spot\Adapter;

/**
 * Adapter Interface
 * 
 * @package Spot
 * @link http://spot.os.ly
 */
interface AdapterInterface
{
    /**
    * @param mixed $dsn DSN string or pre-existing raw connection object to be used instead of instantiating a new one (like PDO or Mongo, etc)
    * @param array $options
    * @return void
    */
    public function __construct($dsn, array $options = array());
	
	
	/**
	 * Get database connection
	 */
	public function connection();
	
	
	/**
	 * Get database DATE format for PHP date() function
	 */
	public function dateFormat();
	
	
	/**
	 * Get database TIME format for PHP date() function
	 */
	public function timeFormat();
	
	
	/**
	 * Get database full DATETIME for PHP date() function
	 */
	public function dateTimeFormat();
	
	
	/**
	 * Get date in format that adapter understands for queries
	 */
	public function date($format = null);
	
	
	/**
	 * Get time in format that adapter understands for queries
	 */
	public function time($format = null);
	
	
	/**
	 * Get datetime in format that adapter understands for queries
	 */
	public function dateTime($format = null);
	
	
	/**
	 * Escape/quote direct user input
	 *
	 * @param string $string
	 */
	public function escape($string);
	
	
	/**
	 * Insert entity
	 */
	public function create($source, array $data, array $options = array());
	
	
	/**
	 * Read from data source using given query object
	 */
	public function read(\Spot\Query $query, array $options = array());
	
	
	/**
	 * Update entity
	 */
	public function update($source, array $data, array $where = array(), array $options = array());
	
	
	/**
	 * Delete entity
	 */
	public function delete($source, array $where, array $options = array());
	
	
	/**
	 * Truncate data source (table for SQL)
	 * Should delete all rows and reset serial/auto_increment keys to 0
	 */
	public function truncateDatasource($source);
	
	/**
	 * Drop/delete data source (table for SQL)
	 * Destructive and dangerous - drops entire data source and all data
	 */
	public function dropDatasource($source);
	
	
	/**
	 * Create a database
 	 * Will throw errors if user does not have proper permissions
	 */
	public function createDatabase($database);
	
	
	/**
	 * Drop an entire database
	 * Destructive and dangerous - drops entire table and all data
	 * Will throw errors if user does not have proper permissions
	 */
	public function dropDatabase($database);
}
