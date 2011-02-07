<?php
require_once dirname(__FILE__) . '/init.php';
/**
 * @package Spot
 * @link http://spot.os.ly
 */
class Spot_Tests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Spot Tests');

		// Traverse the "Test" directory and add the files as tests
		$path = dirname(__FILE__) . '/Test/';
		$dirIterator = new RecursiveDirectoryIterator($path);
		$Iterator = new RecursiveIteratorIterator($dirIterator);
		$tests = new RegexIterator($Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
		
		foreach($tests as $file) {
			$filename = current($file);
			require $filename;
			
			// Class file name by naming standards
			$fileClassName = substr(str_replace(DIRECTORY_SEPARATOR, '_', substr($filename, strlen($path))), 0, -4);
			$suite->addTestSuite('Test_'.$fileClassName);
		}
        return $suite;
    }
}