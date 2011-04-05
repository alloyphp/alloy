<?php
require_once dirname(dirname(__DIR__)) . '/app/init.php';


/**
 * @package Alloy
 * @link http://alloyframework.org
 */
class Alloy_Tests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Alloy Framework Tests');
    
        // Traverse the "Test" directory and add the files as tests
        $path = __DIR__ . '/Test/';
        $dirIterator = new RecursiveDirectoryIterator($path);
        $Iterator = new RecursiveIteratorIterator($dirIterator);
        $tests = new RegexIterator($Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
        
        foreach($tests as $file) {
            // Require file by full path
            $filename = current($file);
            require $filename;
            
            // Class file name by naming standards
            $fileClassName = substr(str_replace(DIRECTORY_SEPARATOR, '_', substr($filename, strlen($path))), 0, -4);
            $suite->addTestSuite('Test_'.$fileClassName);
        }
        return $suite;
    }
}