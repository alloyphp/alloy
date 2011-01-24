<?php
namespace Alloy;

/**
 * Events
 *
 * Stores events with callbacks to be executed when triggered
 *
 * @package Alloy
 * @link http://alloyframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Events
{
    protected $_ns;
    protected $_binds = array();
    
    
    /**
     * Create new event container object and set namespace events apply to
     */
    public function __construct($ns)
    {
        $this->_ns = $ns;
    }
    
    
    /**
     * Get event container's namespace
     *
     * @return string Event namespace
     */
    public function ns()
    {
        return $this->_ns;
    }
    
    
    /**
     * Event: Trigger a named event and execute callbacks that have been hooked onto it
     * 
     * @param string $name Name of the event
     * @param array $params Parameters to pass to bound event callbacks
     * @param return integer Number of bound events triggered
     */
    public function trigger($name, array $params = array())
    {
        $i = 0;
        if(isset($this->_binds[$name])) {
            foreach($this->_binds[$name] as $hookName => $callback) {
                $i++;
                call_user_func_array($callback, $params);
            }
        }
        return $i;
    }
    
    
    /**
     * Event: Add callback to be triggered on event name
     * 
     * @param string $name Name of the event
     * @param string $hookName Name of the bound callback that is being added (custom for each callback)
     * @param callback $callback Callback to execute when named event is triggered
     * @throws \InvalidArgumentException
     */
    public function bind($eventName, $hookName, $callback)
    {
        if(!is_callable($callback)) {
            throw new \InvalidArgumentException("3rd parameter must be valid callback. Given (" . gettype($callback) . ")");
        }
        
        $this->_binds[$eventName][$hookName] = $callback;
    }


    /**
     * Event: Remove callback by name
     *
     * @param string $name Name of the event
     * @param string $hookName Name of the bound callback that is being removed (custom for each callback)
     * @param return boolean Result if unbind was successful
     */
    public function unbind($eventName, $hookName = null)
    {
        if(null === $hookName) {
            // Unbind all hooks on given event name
            if(isset($this->_binds[$eventName])) {
                $this->_binds[$eventName] = array();
                return true;
            }
        } else {
            // Unbind only specific hook by name
            if(isset($this->_binds[$eventName][$hookName])) {
                unset($this->_binds[$eventName][$hookName]);
                return true;
            }
        }
        return false;
    }
    
    
    /**
     * Get set routes
     */
    public function binds()
    {
        return $this->_binds;
    }
    
    
    /**
     * Clear all existing binds to start over
     */
    public function reset()
    {
        $this->_binds = array();
    }
}