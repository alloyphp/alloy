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
    protected static $_level = 0;


    /**
     * Setup form object
     */
    public function init()
    {
        // Use local path by default
        $this->path(__DIR__ . '/templates/');

        // Default settings
        $this->set('levelMin', 0);
        $this->set('levelMax', 0);

        // Default item template callbacks
        $this->set('beforeItemSetCallback', function() { return "<ul class=\"app_treeview\">\n"; });
        $this->set('afterItemSetCallback', function() { return "</ul>\n"; });
        $this->set('beforeItemCallback', function() { return "<li class=\"app_treeview_item\">\n"; });
        $this->set('afterItemCallback', function() { return "</li>\n"; });
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
     * Before item callback
     *
     * @param string $callback Closure for display HTML
     * @throws \Alloy\View\Exception
     */
    public function beforeItem($callback)
    {
        // Check callback
        if(!is_callable($callback)) {
            throw new Exception("Template item must be defined by using a closure or callback");
        }

        $this->set('beforeItemCallback', $callback);
        return $this;
    }


    /**
     * After item callback
     *
     * @param string $callback Closure for display HTML
     * @throws \Alloy\View\Exception
     */
    public function afterItem($callback)
    {
        // Check callback
        if(!is_callable($callback)) {
            throw new Exception("Template item must be defined by using a closure or callback");
        }

        $this->set('afterItemCallback', $callback);
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
     * Before item set callback
     *
     * @param string $callback Closure for display HTML
     * @throws \Alloy\View\Exception
     */
    public function beforeItemSet($callback)
    {
        // Check callback
        if(!is_callable($callback)) {
            throw new Exception("Template item must be defined by using a closure or callback");
        }

        $this->set('beforeItemSetCallback', $callback);
        return $this;
    }


    /**
     * After item set callback
     *
     * @param string $callback Closure for display HTML
     * @throws \Alloy\View\Exception
     */
    public function afterItemSet($callback)
    {
        // Check callback
        if(!is_callable($callback)) {
            throw new Exception("Template item must be defined by using a closure or callback");
        }

        $this->set('afterItemSetCallback', $callback);
        return $this;
    }


    /**
     * Filter callback
     *
     * @param string $callback Closure for determining if an item in the tree should be displayed
     * @throws \Alloy\View\Exception
     */
    public function filter($callback)
    {
        // Check callback
        if(!is_callable($callback)) {
            throw new Exception("Filter must be defined by using a closure or callback");
        }

        $this->set('itemFilter', $callback);
        return $this;
    }


    /**
     * Get currnet level 
     *
     * @param int $level Current level
     */
    public function level()  
    {
        return self::$_level;  
    }


    /**
     * Set minimum level at which to begin item display
     *
     * @param int $level Level at which to beign item display
     */
    public function levelMin($level = null)
    {
        $this->set('levelMin', $level);
        return $this;
    }


    /**
     * Set maximum level to display
     *
     * @param int $level Level at which to stop item display
     */
    public function levelMax($level = null)
    {
        $this->set('levelMax', $level);
        return $this;
    }
    
    
    /**
     * Return template content
     */
    public function content($parsePHP = true)
    {
        // Set static level tracker
        //self::$_level = 0;

        // Set template vars
        $content = parent::content($parsePHP);

        // Reset static level tracker
        self::$_level = 0;

        return $content;
    }
}