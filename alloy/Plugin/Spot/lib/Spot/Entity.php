<?php
namespace Spot;

/**
* Entity object
*
* @package Spot
* @link http://spot.os.ly
*/
abstract class Entity
{
    protected static $_datasource;
    protected static $_datasourceOptions = array();
    protected static $_connection;
    
    // Entity data storage
    protected $_data = array();
    protected $_dataModified = array();
    
    // Entity error messages (may be present after save attempt)
    protected $_errors = array();

    
    /**
     * Constructor - allows setting of object properties with array on construct
     */
    public function __construct(array $data = array())
    {
        $this->initFields();
        
        // Set given data
        if($data) {
            $this->data($data, false);
        }
    }
    
    
    /**
     * Set all field values to their defualts or null
     */
    protected function initFields()
    {
        $fields = static::fields();
        foreach($fields as $field => $opts) {
            if(!isset($this->_data[$field])) {
                $this->_data[$field] = isset($opts['default']) ? $opts['default'] : null;
            }
        }
    }
    
    
    /**
     * Datasource getter/setter
     */
    public static function datasource($ds = null)
    {
        if(null !== $ds) {
            static::$_datasource = $ds;
            return $this;
        }
        return static::$_datasource;
    }


    /**
     * Datasource options getter/setter
     */
    public static function datasourceOptions($dsOpts = null)
    {
        if(null !== $dsOpts) {
            static::$_datasourceOptions = $dsOpts;
            return $this;
        }
        return static::$_datasourceOptions;
    }
    
    
    /**
     * Named connection getter/setter
     */
    public static function connection($connection = null)
    {
        if(null !== $connection) {
            static::$_connection = $connection;
            return $this;
        }
        return static::$_connection;
    }
    
    
    /**
     * Return defined fields of the entity
     */
    public static function fields()
    {
        return array();
    }
    
    
    /**
     * Return defined fields of the entity
     */
    public static function relations()
    {
        return array();
    }
    
    
    /**
     * Gets and sets data on the current entity
     */
    public function data($data = null, $modified = true)
    {
        // GET
        if(null === $data || !$data) {
            return array_merge($this->_data, $this->_dataModified);
        }
        
        // SET
        if(is_object($data) || is_array($data)) {
            foreach($data as $k => $v) {
                if(true === $modified) {
                    $this->_dataModified[$k] = $v;
                } else {
                    $this->_data[$k] = $v;
                }
            }
            return $this;
        } else {
            throw new \InvalidArgumentException(__METHOD__ . " Expected array or object input - " . gettype($data) . " given");
        }
    }

    
    /**
     * Return array of field data with data from the field names listed removed
     *
     * @param array List of field names to exclude in data list returned
     */
    public function dataExcept(array $except)
    {
        return array_diff_key($this->data(), array_flip($except));
    }


    /**
     * Gets data that has been modified since object construct
     */
    public function dataModified()
    {
        return $this->_dataModified;
    }


    /**
     * Alias of self::data()
     */
    public function toArray()
    {
        return $this->data();
    }


    /**
     * Check if any errors exist
     *
     * @param string $field OPTIONAL field name
     * @return boolean
     */
    public function hasErrors($field = null)
    {
        if(null !== $field) {
            return isset($this->_errors[$field]) ? count($this->_errors[$field]) > 0 : false;
        }
        return count($this->_errors) > 0;
    }
    
    
    /**
     * Error message getter/setter
     * 
     * @param $field string|array String return errors with field key, array sets errors
     * @return self|array|boolean Setter return self, getter returns array or boolean if key given and not found
     */
    public function errors($msgs = null)
    {
        // Return errors for given field
        if(is_string($msgs)) {
            return isset($this->_errors[$msgs]) ? $this->_errors[$msgs] : array();
    
        // Set error messages from given array
        } elseif(is_array($msgs)) {
            $this->_errors = $msgs;
        }
        return $this->_errors;
    }
    
    
    /**
     * Add an error to error messages array
     *
     * @param string $field Field name that error message relates to
     * @param mixed $msg Error message text - String or array of messages
     */
    public function error($field, $msg)
    {
        if(is_array($msg)) {
            // Add array of error messages about field
            foreach($msg as $msgx) {
                $this->_errors[$field][] = $msgx;
            }
        } else {
            // Add to error array
            $this->_errors[$field][] = $msg;
        }
    }
    
    
    /**
     * Enable isset() for object properties
     */
    public function __isset($key)
    {
        return isset($this->_data[$key]) || isset($this->_dataModified[$key]);
    }
    
    
    /**
     * Getter for field properties
     */
    public function __get($field)
    {
        if(isset($this->_dataModified[$field])) {
            return $this->_dataModified[$field];
        } elseif(isset($this->_data[$field])) {
            return $this->_data[$field];
        }
        return null;
    }
    
    
    /**
     * Setter for field properties
     */
    public function __set($field, $value)
    {
        $this->_dataModified[$field] = $value;
    }
    
    
    /**
     * String representation of the class
     */
    public function __toString()
    {
        return __CLASS__;
    }
}