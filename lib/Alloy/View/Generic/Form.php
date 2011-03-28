<?php
namespace Alloy\View\Generic;

/**
 * Generic Form View
 * 
 * @package Alloy
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @link http://alloyframework.com/
 */
class Form extends \Alloy\View\Template
{
    protected $_fields = array();
    protected $_fieldValues = array();
    protected $_submitButtonText = 'Save';
    
    
    /**
     * Setup form object
     */
    public function init()
    {
        // Use local path by default
        $this->path(__DIR__ . '/templates/');

        // Default enctype
        $this->set('enctype', 'appliaction/x-www-form-urlencoded');
    }
    
    
    /**
     * Action param of form
     *
     * @param string $action URL form will submit to
     */
    public function action($action = '')
    {
        $this->set('action', $action);
        return $this;
    }
    
    
    /**
     * HTTP Method param of form
     *
     * @param string $action Method used to submit form to server
     */
    public function method($method = 'POST')
    {
        $this->set('method', strtoupper($method));
        return $this;
    }


    /**
     * Encoding type of form
     *
     * @param string $enctype Encoding type to use
     */
    public function enctype($enctype = null)
    {
        $this->set('enctype', $enctype);
        return $this;
    }


    /**
     * Encoding type of form
     *
     * @param string $enctype Encoding type to use
     */
    public function type($type = null)
    {
        if('file' == $type || 'upload' == $type) {
            $this->set('enctype', 'multipart/form-data');
        } else {
            $this->set('enctype', 'appliaction/x-www-form-urlencoded');
        }
        return $this;
    }
    
    
    /**
     * Field setter/getter
     */
    public function fields(array $fields = array())
    {
        if(count($fields) > 0 ) {
            $this->_fields = $fields;
            return $this;
        }
        return $this->_fields;
    }
    
    
    /**
     * Get params set on field
     *
     * @param string $field Name of the field to return data for
     */
    public function field($field)
    {
        if(isset($this->_fields[$field])) {
            return $this->_fields[$field];
        } else {
            return false;
        }
    }
    
    
    /**
     * Value by field name
     */
    public function data($field = null, $value = null)
    {
        // Return data array
        if(null === $field) {
            return $this->_fieldValues;
        }
        
        // Set data for single field
        if(null !== $value) {
            $this->_fieldValues[$field] = $value;
            return $this;
        
        // Set data from array for many fields
        } elseif(is_array($field)) {
            foreach($field as $fieldx => $val) {
                $this->_fieldValues[$fieldx] = $val;
            }
            return $this;
        }
        
        // Return data for given field
        return isset($this->_fieldValues[$field]) ? $this->_fieldValues[$field] : null;
    }
    
    
    /**
     * Remove fields by name
     *
     * @param mixed $fieldName String or array of field names
     */
    public function removeFields($fieldName)
    {
        $fields = (array) $fieldName;

        // Fields
        foreach($fields as $field) {
            if(isset($this->_fields[$field])) {
                unset($this->_fields[$field]);
            }
        }

        // Data
        foreach($fields as $field) {
            if(isset($this->_fieldValues[$field])) {
                unset($this->_fieldValues[$field]);
            }
        }
        return $this;
    }
    
    
    /**
     * Set the text for the submit button
     *
     * @param string $text Text to display on the button
     */
    public function submit($text = null)
    {
        if(null === $text) {
            return $this->_submitButtonText;
        } else {
            $this->_submitButtonText = $text;
        }
        return $this;
    }
    
    
    /**
     * Return template content
     */
    public function content($parsePHP = true)
    {
        // Set template vars
        $this->set('fields', $this->fields());
        
        return parent::content($parsePHP);
    }
}