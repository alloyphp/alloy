<?php
namespace Alloy\View\Generic;

/**
 * Generic Dategrid View
 * 
 * @package Alloy
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @link http://alloyframework.com/
 */
class Datagrid extends \Alloy\View\Template
{
    protected $_columns = array();
    
    
    /**
     * Setup form object
     */
    public function init()
    {
        // Use local path by default
        $this->path(__DIR__ . '/templates/');
    }
    
    
    /**
     * Data to use
     *
     * @param mixed $data Array or Iterator Traversable object
     */
    public function data(\Traversable $data)
    {
        $this->_data = $data;
        return $this;
    }
    
    
    /**
     * Column setter/getter
     */
    public function columns(array $columns = array())
    {
        if(count($columns) > 0 ) {
            $this->_columns = $columns;
            return $this;
        }
        return $this->_columns;
    }
    
    
    /**
     * Define new column
     *
     * @param string $name Name of the field to return data for
     * @throws \Alloy\View\Exception
     */
    public function column($name, $callback)
    {
        // Check callback
        if(!is_callable($callback)) {
            throw new \Alloy\View\Exception("Column must be dfeined by using a closure or callback");
        }
        
        // Set column
        $this->_columns[$name] = array(
            'title' => $name,
            'callback' => $callback
        );
        
        return $this;
    }
    
    
    /**
     * Return template content
     */
    public function content($parsePHP = true)
    {
        // Set template vars
        $this->set('data', $this->_data);
        $this->set('columns', $this->columns());
        
        return parent::content($parsePHP);
    }
}