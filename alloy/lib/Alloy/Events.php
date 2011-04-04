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
    protected $_filters = array();
    
    
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
     * Get all defined event callbacks
     *
     * @param string $name Optional event name
     * @return array
     */
    public function binds($name = null)
    {
        if(null !== $name) {
            if(isset($this->_binds[$name])) {
                return $this->_binds[$name];
            }
            return array();
        }
        return $this->_binds;
    }


    /**
     * Filter: Add filter callback to be triggered on event name
     * 
     * @param string $name Name of the event
     * @param string $hookName Name of the bound filter callback that is being added (custom for each callback)
     * @param callback $callback Callback to execute when named event is triggered
     * @throws \InvalidArgumentException
     */
    public function addFilter($eventName, $hookName, $callback)
    {
        if(!is_callable($callback)) {
            throw new \InvalidArgumentException("3rd parameter must be valid callback. Given (" . gettype($callback) . ")");
        }
        
        $this->_filters[$eventName][$hookName] = $callback;
        return $this;
    }


    /**
     * Filter: Remove filter callback by name
     *
     * @param string $name Name of the filter
     * @param string $hookName Name of the filter callback that is being removed (custom for each callback)
     * @param return boolean Result if unbind was successful
     */
    public function removeFilter($name, $hookName = null)
    {
        if(null === $hookName) {
            // Unbind all hooks on given filter name
            if(isset($this->_filters[$name])) {
                $this->_filters[$name] = array();
                return true;
            }
        } else {
            // Unbind only specific hook by name
            if(isset($this->_filters[$name][$hookName])) {
                unset($this->_filters[$name][$hookName]);
                return true;
            }
        }
        return false;
    }


    /**
     * Filter: Trigger a named filter and execute filter callbacks that have been hooked onto it
     * 
     * @param string $name Name of the filter
     * @param mixed $value Value to pass to the filter callbacks
     * @param return mixed Return value from executed filter callbacks
     */
    public function filter($name, $value)
    {
        if(isset($this->_filters[$name])) {
            foreach($this->_filters[$name] as $hookName => $callback) {
                try {
                    $value = call_user_func($callback, $value);
                } catch(\Exception $e) {
                    $value = $e;
                }
            }
        }
        return $value;
    }
    

    /**
     * Get all defined event callbacks
     *
     * @param string $name Optional event name
     * @return array
     */
    public function filters($name = null)
    {
        if(null !== $name) {
            if(isset($this->_filters[$name])) {
                return $this->_filters[$name];
            }
            return array();
        }
        return $this->_filters;
    }

    
    /**
     * Clear all existing binds to start over
     */
    public function reset()
    {
        $this->_binds = array();
        $this->_filters = array();
    }
}