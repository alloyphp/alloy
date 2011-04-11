<?php
namespace Plugin\Spot;
use Spot, Alloy;

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
        $kernel->loader()->registerNamespace('Spot', __DIR__ . '/lib');

        // Make methods globally avaialble with Kernel
        $kernel->addMethod('mapper', array($this, 'mapper'));
        $kernel->addMethod('spotConfig', array($this, 'spotConfig'));
        $kernel->addMethod('spotForm', array($this, 'spotForm'));

        // Debug Spot queries
        $kernel->events()->bind('response_sent', 'spot_query_log', array($this, 'debugQueryLog'));

        // Add 'autoinstall' method as callback for 'dispatch_exception' filter when exceptions are encountered
        $kernel->events()->addFilter('dispatch_exception', 'spot_autoinstall_on_exception', array($this, 'autoinstallOnException'));
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
            $dbCfg = $kernel->config('app.database');
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


    /**
     * Return view object for the add/edit form
     *
     * @param \Spot\Entity $entity Entity object to build form from and set data with
     */
    public function spotForm(Spot\Entity $entity)
    {
        $entityClass = get_class($entity);
        $view = new \Alloy\View\Generic\Form('form');
        $view->action('')
            ->method('post')
            ->fields($entityClass::fields()) // love me some late static binding
            ->data($entity->data())
            ->removeFields(array('id', 'date_created', 'date_modified'));
        return $view;
    }


    /**
     * Debug Spot queries by dumping query log
     */
    public function debugQueryLog()
    {
        if($this->kernel->config('app.debug')) {
            // Executed queries
            echo "<hr />";
            echo "<h1>Executed Queries (" . \Spot\Log::queryCount() . ")</h1>";
            echo "<pre>";
            print_r(\Spot\Log::queries());
            echo "</pre>";
        }
    }


    /**
     * Autoinstall missing tables on exception
     */
    public function autoinstallOnException($content)
    {
        $kernel = \Kernel();

        // Database error
        if($content instanceof \PDOException
          || $content instanceof \Spot\Exception_Datasource_Missing) {
            if($content instanceof \Spot\Exception_Datasource_Missing
              ||'42S02' == $content->getCode()
              || false !== stripos($content->getMessage(), 'Base table or view not found')) {
                // Table not found - auto-install module to cause Entity migrations
                $ld = $kernel->lastDispatch();
                $content = $kernel->dispatch($ld['module'], 'install');
            }
        }

        return $content;
    }
}