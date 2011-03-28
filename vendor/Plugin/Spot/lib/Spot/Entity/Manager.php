<?php
namespace Spot\Entity;
use Spot;

/**
 * Entity Manager for storing information about entities
 *
 * @package Spot
 * @link http://spot.os.ly
 */
class Manager
{
    // Field and relation info
    protected static $_properties = array();
    protected static $_fields = array();
    protected static $_fieldsDefined = array();
    protected static $_fieldDefaultValues = array();
    protected static $_relations = array();
    protected static $_primaryKeyField = array();
    
    // Connection and datasource info
    protected static $_connection = array();
    protected static $_datasource = array();
    protected static $_datasourceOptions = array();
    
    
    /**
     * Get formatted fields with all neccesary array keys and values.
     * Merges defaults with defined field values to ensure all options exist for each field.
     *
     * @param string $entityName Name of the entity class
     * @return array Defined fields plus all defaults for full array of all possible options
     */
    public function fields($entityName)
    {
        if(!is_string($entityName)) {
            throw new \Spot\Exception(__METHOD__ . " only accepts a string. Given (" . gettype($entityName) . ")");
        }
        
        if(!is_subclass_of($entityName, '\Spot\Entity')) {
            throw new \Spot\Exception($entityName . " must be subclass of '\Spot\Entity'.");
        }
        
        if(isset(self::$_fields[$entityName])) {
            $returnFields = self::$_fields[$entityName];
        } else {
            // Datasource info
            $entityDatasource = null;
            $entityDatasource = $entityName::datasource();
            if(null === $entityDatasource || !is_string($entityDatasource)) {
                echo "\n\n" . $entityName . "::datasource() = " . var_export($entityName::datasource(), true) . "\n\n";
                throw new \InvalidArgumentException("Entity must have a datasource defined. Please define a protected property named '_datasource' on your '" . $entityName . "' entity class.");
            }
            self::$_datasource[$entityName] = $entityDatasource;

            // Datasource Options
            $entityDatasourceOptions = $entityName::datasourceOptions();
            self::$_datasourceOptions[$entityName] = $entityDatasourceOptions;
            
            // Connection info
            $entityConnection = $entityName::connection();
            // If no adapter specified, Spot will use default one from config object (or first one set if default is not explicitly set)
            self::$_connection[$entityName] = ($entityConnection) ? $entityConnection : false;
            
            // Default settings for all fields
            $fieldDefaults = array(
                'type' => 'string',
                'default' => null,
                'length' => null,
                'required' => false,
                'null' => true,
                'unsigned' => false,
                'fulltext' => false,
    
                'primary' => false,
                'index' => false,
                'unique' => false,
                'serial' => false,
    
                'relation' => false
                );
    
            // Type default overrides for specific field types
            $fieldTypeDefaults = array(
                'string' => array(
                    'length' => 255
                    ),
                'float' => array(
                    'length' => array(10,2)
                    ),
                'int' => array(
                    'length' => 10,
                    'unsigned' => true
                    )
                );
    
            // Get entity fields from entity class
            $entityFields = false;
            $entityFields = $entityName::fields();
            if(!is_array($entityFields) || count($entityFields) < 1) {
                throw new \InvalidArgumentException($entityName . " Must have at least one field defined.");
            }
                
            $returnFields = array();
            self::$_fieldDefaultValues[$entityName] = array();
            foreach($entityFields as $fieldName => $fieldOpts) {
                // Store field definition exactly how it is defined before modifying it below
                if($fieldOpts['type'] != 'relation') {
                    self::$_fieldsDefined[$entityName][$fieldName] = $fieldOpts;
                }
                
                // Format field will full set of default options
                if(isset($fieldInfo['type']) && isset($fieldTypeDefaults[$fieldOpts['type']])) {
                    // Include type defaults
                    $fieldOpts = array_merge($fieldDefaults, $fieldTypeDefaults[$fieldOpts['type']], $fieldOpts);
                } else {
                    // Merge with defaults
                    $fieldOpts = array_merge($fieldDefaults, $fieldOpts);
                }
    
                // Store primary key
                if(true === $fieldOpts['primary']) {
                    self::$_primaryKeyField[$entityName] = $fieldName;
                }
                // Store default value
                if(null !== $fieldOpts['default']) {
                    self::$_fieldDefaultValues[$entityName][$fieldName] = $fieldOpts['default'];
                } else {
                    self::$_fieldDefaultValues[$entityName][$fieldName] = null;
                }
    
                $returnFields[$fieldName] = $fieldOpts;
            }
            self::$_fields[$entityName] = $returnFields;
            
            // Relations
            $entityRelations = array();
            $entityRelations = $entityName::relations();
            if(!is_array($entityRelations)) {
                throw new \InvalidArgumentException($entityName . " Relation definitons must be formatted as an array.");
            }
            foreach($entityRelations as $relationAlias => $relationOpts) {
                self::$_relations[$entityName][$relationAlias] = $relationOpts;
            }
        }
        return $returnFields;
    }
    
    
    /**
     * Get field information exactly how it is defined in the class
     *
     * @param string $entityName Name of the entity class
     * @return array Array of field key => value pairs
     */
    public function fieldsDefined($entityName)
    {
        if(!isset(self::$_fieldsDefined[$entityName])) {
            $this->fields($entityName);
        }
        return self::$_fieldsDefined[$entityName];
    }
    
    
    /**
     * Get field default values as defined in class field definitons
     *
     * @param string $entityName Name of the entity class
     * @return array Array of field key => value pairs
     */
    public function fieldDefaultValues($entityName)
    {
        if(!isset(self::$_fieldDefaultValues[$entityName])) {
            $this->fields($entityName);
        }
        return self::$_fieldDefaultValues[$entityName];
    }
    
    
    /**
     * Get defined relations
     *
     * @param string $entityName Name of the entity class
     */
    public function relations($entityName)
    {
        $this->fields($entityName);
        if(!isset(self::$_relations[$entityName])) {
            return array();
        }
        return self::$_relations[$entityName];
    }
    
    
    /**
     * Get value of primary key for given row result
     *
     * @param string $entityName Name of the entity class
     */
    public function primaryKeyField($entityName)
    {
        if(!isset(self::$_primaryKeyField[$entityName])) {
            $this->fields($entityName);
        }
        return self::$_primaryKeyField[$entityName];
    }
    
    
    /**
     * Check if field exists in defined fields
     *
     * @param string $entityName Name of the entity class
     * @param string $field Field name to check for existence
     */
    public function fieldExists($entityName, $field)
    {
        return array_key_exists($field, $this->fields($entityName));
    }
    
    
    /**
     * Return field type
     *
     * @param string $entityName Name of the entity class
     * @param string $field Field name
     * @return mixed Field type string or boolean false
     */
    public function fieldType($entityName, $field)
    {
        $fields = $this->fields($entityName);
        return $this->fieldExists($entityName, $field) ? $fields[$field]['type'] : false;
    }
    
    
    /**
     * Get defined connection to use for entity
     *
     * @param string $entityName Name of the entity class
     */
    public function connection($entityName)
    {
        $this->fields($entityName);
        if(!isset(self::$_connection[$entityName])) {
            return false;
        }
        return self::$_connection[$entityName];
    }
    
    
    /**
     * Get name of datasource for given entity class
     *
     * @param string $entityName Name of the entity class
     * @return string
     */
    public function datasource($entityName)
    {
        if(!isset(self::$_datasource[$entityName])) {
            $this->fields($entityName);
        }
        return self::$_datasource[$entityName];
    }


    /**
     * Get datasource options for given entity class
     *
     * @param array Options to pass
     * @return string
     */
    public function datasourceOptions($entityName)
    {
        if(!isset(self::$_datasourceOptions[$entityName])) {
            $this->fields($entityName);
        }
        return self::$_datasourceOptions[$entityName];
    }
}