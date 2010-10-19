<?php
namespace Alloy\View\Helper;

/**
 * Cache Helper
 * Caches and retrieves output via closure callback
 *
 * !!! NOT A FUNCTIONAL CACHE YET -- JUST A SYNTAX PROTOTYPE !!!
 * 
 * @package Alloy
 * @license http://www.opensource.org/licenses/bsd-license.php
 * @link http://alloyframework.com/
 */
class Cache extends HelperAbstract
{
    protected $_cache = array();
    
    /**
     * Get cached item if available
     */
    public function get($name)
    {
        if(isset($this->_cache[$name])) {
            return $this->_cache[$name];
        }
        return false;
    }
    
    
    /**
     * Set cached item and return it
     */
    public function set($name, $closure)
    {
        $this->_cache[$name] = $closure;
        return $closure;
    }
}