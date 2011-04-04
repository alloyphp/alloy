<?php
namespace Alloy;
use Alloy\Module;

/**
 * Resource wrapper object
 * 
 * @package Alloy Framework
 * @link http://alloyframework.com/
 */
class Resource extends Module\Response
{
    protected $_resource;
    protected $_errors = array();
    protected $_location;
    protected $_status = 200;
    
    
    /**
     * Object constructor
     *
     * @param mixed $resource Array or Object that has a 'toArray' method
     */
    public function __construct($resource = array())
    {
        $this->_resource = $resource;
    }
    

    /**
     * URL location to be returned with resource
     *
     * @param string $url URL of the resource to be returned in headers
     */
    public function created($url, $status = 201)
    {
        $this->_location = $url;
        $this->status($status);
        return $this;
    }


    /**
     * URL location to be returned with resource
     *
     * @param string $url URL of the resource to be returned in headers
     */
    public function location($url = null)
    {
        if(null === $url) {
            return $this->_location;
        }

        $this->_location = $url;
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
        $r = false;

        // Already array
        if(is_array($this->_resource)) {
            $r = $this->_resource;
    
        // String
        } elseif(is_string($this->_resource)) {
            // JSON decode
            if($json = json_decode($this->_resource)) {
                $r = $json;
            // Unserialize
            } elseif($data = unserialize($this->_resource)) {
                $r = $data;
            }
        
        // Object
        } elseif(is_object($this->_resource)) {
            // Call 'toArray' on object
            if(method_exists($this->_resource, 'toArray')) {
                $r = $this->_resource->toArray();
            }
        }

        // Ensure we do have an array
        if(!is_array($r)) {
            throw new Exception("Resource (" . gettype($this->_resource) . ") could not be converted to array");
        }

        // Put results in names key
        if(!isset($r['results'])) {
            $r = array('results' => $r);
        }

        // Set/add errors, if any
        if($errors = $this->errors()) {
            $r['errors'] = $errors;
        }

        return $r;
    }

    
    /**
     * Return string content of resource
     *
     * @return string
     */
    public function content($content = null)
    {
        if(null !== $content) {
            $this->_resource = $content;
            return $this;
        }

        // Return unchanged if string
        if(is_string($this->_resource)) {
            return $this->_resource;
        }

        try {
            // Else return JSON encoded data
            return json_encode($this->toArray());
        } catch(\Exception $e) {
            return $e->getMessage();
        }
    }
}