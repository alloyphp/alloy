<?php
namespace Alloy\View\Generic;
use Alloy\View\Template;
use Alloy\View\Exception;

/**
 * Generic Treeview
 * 
 * @package Alloy
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @link http://alloyframework.com/
 */
class Treeview extends Template
{
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
        
        $this->set('itemData', $data);
        return $this;
    }
    
    
    /**
     * Set callback to use for display when there is no data to show
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
     * Item callback
     *
     * @param string $callback Closure for displaying single item in the tree
     * @throws \Alloy\View\Exception
     */
    public function item($callback)
    {
        // Check callback
        if(!is_callable($callback)) {
            throw new Exception("Template item must be defined by using a closure or callback");
        }

        $this->set('itemCallback', $callback);
        return $this;
    }


    /**
     * Item children callback
     *
     * @param string $callback Closure for displaying single item in the tree
     * @throws \Alloy\View\Exception
     */
    public function itemChildren($callback)
    {
        // Check callback
        if(!is_callable($callback)) {
            throw new Exception("Item children must be defined by using a closure or callback");
        }

        $this->set('itemChildrenCallback', $callback);
        return $this;
    }
    
    
    /**
     * Return template content
     */
    public function content($parsePHP = true)
    {
        // Set template vars
        
        return parent::content($parsePHP);
    }
}