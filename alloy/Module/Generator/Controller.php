<?php
namespace Module\Generator;

use Stackbox;
use Alloy, Alloy\Request;

/**
 * Generator/scaffold module
 */
class Controller extends Alloy\Module\ControllerAbstract
{
    /**
     * Ensure generator can only be invoked from command line
     */
    public function init($action = null)
    {
        if(!\Kernel()->request()->isCli()) {
            throw new Alloy\Exception\FileNotFound("Requested file or page not found. Please check the URL and try again.");
        }
    }


    /**
     * Scaffold module
     */
    public function scaffoldAction(Request $request)
    {
        $kernel = \Kernel();

        /**
         * Variables we expect in CLI request:
         *
         * $name string Name of module
         */
        $name = preg_replace('/[^a-zA-Z0-9_ ]/', '', $kernel->formatUnderscoreWord($request->name));
        $name_table = preg_replace('/\s+/', '_', strtolower($name));

        // URL-usable name
        $name_url = preg_replace('/\s+/', '_', $name);

        // Directory path
        $name_dir = preg_replace('/\s+/', '/', $name);

        // Valid PHP namespace
        $namespace = preg_replace('/\s+/', '\\', $name);

        // TODO: Make this dynamic and generated (allow user field definitions)
        $fields = array(
            'name' => array('type' => 'string', 'required' => true)
        );
        $field_string = "";
        foreach($fields as $fieldName => $fieldInfo) {
            // Flattens field definitions for writing in Entity.php file
            // str_replace calls to remove some of the prettyprinting and odd formats var_export does by default
            $field_string .= "'" . $fieldName . "' => " . str_replace(
                array("array (", "\n", ",)"),
                array("array(",  "",   ")"),
                var_export($fieldInfo, true)
            ) . ",\n";
        }

        // Set tag variables
        $generatorTagNames = compact('name', 'name_table', 'name_sanitized', 'name_url', 'name_dir', 'namespace', 'fields', 'field_string');

        echo PHP_EOL;

        // File paths
        $scaffoldPath = __DIR__ . '/scaffold/';
        $modulePath = $kernel->config('app.path.root') .'/Module/' . $name_dir . '/';
        
        // Output
        echo 'Generator Module Info' . PHP_EOL;
        echo '-----------------------------------------------------------------------' . PHP_EOL;
        echo 'Name               = ' . $name . PHP_EOL;
        echo 'Namespace          = ' . $namespace . PHP_EOL;
        echo 'Datasource (Table) = ' . $name_table . PHP_EOL;
        echo 'Path               = ' . $modulePath . PHP_EOL;
        echo '-----------------------------------------------------------------------' . PHP_EOL;
        echo PHP_EOL;

        // Variables (in 'generator' namespace):
        // * name
        // * name_table
        // * $fields
        //
        // Other variables
        // * $fields (from Entity::fields())

        // Build tag replecement set for scaffold-generated files
        $generator_tag_start = '{$generator.';
        $generator_tag_end = '}';
        $generatorTags = array();
        foreach($generatorTagNames as $tag => $val) {
            if(is_array($val)) {
                $val = var_export($val, true);
            }
            $generatorTags[$generator_tag_start . $tag . $generator_tag_end] = $val;
        }

        // Copy files and replace tokens
        $scaffoldFiles = array(
            'Controller.php',
            'Entity.php',
            'views/indexAction.html.php',
            'views/newAction.html.php',
            'views/editAction.html.php',
            'views/deleteAction.html.php',
            'views/viewAction.html.php'
        );
        foreach($scaffoldFiles as $sFile) {
            $tmpl = file_get_contents($scaffoldPath . $sFile);

            // Replace template tags
            $tmpl = str_replace(array_keys($generatorTags), array_values($generatorTags), $tmpl);

            // Ensure destination directory exists
            $sfDir = dirname($modulePath . $sFile);
            if(!is_dir($sfDir)) {
                mkdir($sfDir, 0755, true);
            }

            // Write file to destination module directory
            $result = file_put_contents($modulePath . $sFile, $tmpl);
            if($result) {
                echo "+ Generated '" . $sFile . "'";
            } else {
                echo "[ERROR] Unable to generate '" . $sFile . "'";
            }
            echo PHP_EOL;
        }

        echo PHP_EOL;
    }
}