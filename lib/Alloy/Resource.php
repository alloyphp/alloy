<?php
/**
 * Resource wrapper object
 * 
 * @package Alloy Framework
 * @link http://alloyframework.com/
 */
class Alloy_Resource
{
	protected $_resource;
	protected $_location;
	protected $_status = 200;
	
	
	/**
	 * Object constructor
	 *
	 * @param mixed $resource Array or Object that has a 'toArray' method
	 */
	public function __construct($resource)
	{
		$this->_resource = $resource;
	}
	
	
	/**
	 * URL location to be returned with resource
	 *
	 * @param string $url URL of the resource to be returned in headers
	 */
	public function location($url)
	{
		$this->_location = $url;
		$this->status(201);
		return $this;
	}
	
	
	/**
	 * HTTP Status code
	 *
	 * @param int $statusCode HTTP status code to return
	 */
	public function status($statusCode = 200)
	{
		$this->_status = $statusCode;
		return $this;
	}
	
	
	/**
	 * Return string content of resource
	 */
	public function __toString()
	{
		return json_encode($this->_resource);
	}
}