<?php
namespace Alloy;
use Alloy\Kernel;

/**
 * PluginAbstract
 *
 * Abstract base class for plugins
 *
 * @package Alloy
 * @link http://alloyframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
abstract class PluginAbstract
{
    protected $kernel;
    protected $config = array();
    
    
    /**
     * Construct to set Kernel instance and config array passed
     */
    public function __construct(Kernel $kernel, array $config = array())
    {
        $this->kernel = $kernel;
        $this->config = $config;

        // Initialize plugin code
        $this->init();
    }
    
    
    /**
     * Initialization work for plugin
     */
    abstract public function init();
}
