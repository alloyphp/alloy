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
    protected $_actions = array();
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
            throw new \Alloy\View\Exception("Data provided must be defined by using either an array or Traversable object - given (" . gettype($data) . ").");
        }
        
        $this->_data = $data;
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
     * Set heading/title
     *
     * @param string $name Heading title
     */
    public function heading($heading)
    {
        $this->set('heading', $heading);
        return $this;
    }
    
    
    /**
     * Actions setter/getter
     */
    public function actions(array $actions = array())
    {
        if(count($actions) > 0 ) {
            $this->_actions = $actions;
            return $this;
        }
        return $this->_actions;
    }
    
    
    /**
     * Add new action to datagrid
     *
     * @param string $name Action title text that will be displayed to the user
     * @param string $callback Closure for displaying content when there is no data to display
     */
    public function action($name, $callback)
    {
        // Check callback
        if(!is_callable($callback)) {
            throw new \Alloy\View\Exception("Action must be defined by using a closure or callback");
        }
        
        // Set column
        $this->_actions[$name] = array(
            'title' => $name,
            'callback' => $callback
        );
        
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
     * @param string $callback Closure for displaying content when there is no data to display
     * @throws \Alloy\View\Exception
     */
    public function column($name, $callback)
    {
        // Check callback
        if(!is_callable($callback)) {
            throw new \Alloy\View\Exception("Column must be defined by using a closure or callback");
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
        $this->set('actions', $this->actions());
        $this->set('columns', $this->columns());
        
        return parent::content($parsePHP);
    }
}