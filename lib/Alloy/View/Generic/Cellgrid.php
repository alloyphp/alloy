<?php
namespace Alloy\View\Generic;
use Alloy\View\Template;
use Alloy\View\Exception;

/**
 * Generic Cellgrid View
 * 
 * @package Alloy
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @link http://alloyframework.com/
 */
class Cellgrid extends Template
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
    public function data($data)
    {
        // Check data
        if(!is_array($data) && !($data instanceof \Traversable)) {
            throw new Exception("Data provided must be defined by using either an array or Traversable object - given (" . gettype($data) . ").");
        }
        
        $this->set('cellData', $data);
        return $this;
    }
    
    
    /**
     * Set callback to use for display when there is no data to show in table
     *
     * @param string $callback Closure for displaying content when there is no data to display
     * @throws \Alloy\View\Exception
     */
    public function noData($callback)
    {
        // Check callback
        if(!is_callable($callback)) {
            throw new \Alloy\View\Exception("noData content must be defined by using a closure or valid callback");
        }
        
        // Pass callback to template
        $this->set('noDataCallback', $callback);
        
        return $this;
    }
    
    
    /**
     * Number of columns setter/getter
     *
     * @param integer $columns
     */
    public function columns($columns = null)
    {
        if(null !== $columns) {
            $this->_columns = $columns;
            return $this;
        }
        return $this->_columns;
    }
    
    
    /**
     * Cell callback
     *
     * @param string $callback Closure for displaying cell content
     * @throws \Alloy\View\Exception
     */
    public function cell($callback)
    {
        // Check callback
        if(!is_callable($callback)) {
            throw new Exception("Cell must be defined by using a closure or callback");
        }

        $this->set('cellCallback', $callback);
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