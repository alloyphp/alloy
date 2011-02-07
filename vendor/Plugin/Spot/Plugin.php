<?php
namespace Plugin\Spot;
use Alloy;

/**
 * Spot ORM Plugin
 * Adds helper methods to the Kernel for using and interacting with Spot
 */
class Plugin
{
    protected $kernel;
    protected $spotConfig;
    protected $spotMapper = array();


    /**
     * Initialize plguin
     */
    public function __construct(Alloy\Kernel $kernel)
    {
        $this->kernel = $kernel;

        // Let autoloader know where to find Spot library files
        $kernel->loader()->registerNamespace('Spot', $kernel->config('path.vendor'));

        // Make methods globally avaialble with Kernel
        $kernel->addMethod('mapper', array($this, 'mapper'));
        $kernel->addMethod('spotConfig', array($this, 'spotConfig'));
    }


    /**
     * Get mapper object to work with
     * Ensures only one instance of a mapper gets loaded
     *
     * @param string $mapperName (Optional) Custom mapper class to load in case of custom requirements or queries
     */
    public function mapper($mapperName = '\Spot\Mapper')
    {
        $kernel = $this->kernel;

        if(!isset($kernel->spotMapper[$mapperName])) {
            // Create new mapper, passing in config
            $cfg = $this->spotConfig();
            $kernel->spotMapper[$mapperName] = new $mapperName($cfg);
        }
        return $kernel->spotMapper[$mapperName];
    }
    
    
    /**
     * Get instance of database connection
     */
    public function spotConfig()
    {
        $kernel = $this->kernel;

        if(!$this->spotConfig) {
            $dbCfg = $kernel->config('database');
            if($dbCfg) {
                // New config
                $this->spotConfig = new \Spot\Config();
                foreach($dbCfg as $name => $options) {
                        $this->spotConfig->addConnection($name, $options);
                }
            } else {
                throw new \Exception("Unable to load configuration for Spot - Database configuration settings do not exist.");
            }
        }
        return $this->spotConfig;
    }
}