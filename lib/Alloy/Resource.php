<?php
namespace Alloy;

/**
 * Resource wrapper object
 * 
 * @package Alloy Framework
 * @link http://alloyframework.com/
 */
class Resource
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
     * Convert resource to native array type so we can have our way with it
     *
     * @return array
     * @throws Alloy_Exception
     */
    public function toArray()
    {
        if(is_array($this->_resource)) {
            return $this->_resource;
    
        // String
        } elseif(is_string($this->_resource)) {
            // JSON decode
            if($json = json_decode($this->_resource)) {
                return $json;
            // Unserialize
            } elseif($data = unserialize($this->_resource)) {
                return $data;
            }
        
        // Object
        } elseif(is_object($this->_resource)) {
            // Call 'toArray' on object
            if(method_exists($this->_resource, 'toArray')) {
                return $this->_resource->toArray();
            }
        }
        throw new Alloy_Exception("Resource (" . gettype($this->_resource) . ") could not be converted to array");
    }
    
    /**
     * Return string content of resource
     *
     * @return string
     */
    public function __toString()
    {
        // Return unchanged if string
        if(is_string($this->_resource)) {
            return $this->_resource;
        }
        // Else return JSON encoded data
        return json_encode($this->toArray());
    }
}