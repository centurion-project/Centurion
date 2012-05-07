<?php
if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Centurion_Cache_AllTests::main');
}

require_once dirname(__FILE__) . '/../../../TestHelper.php';
 
class Centurion_Cache_AllTests extends PHPUnit_Framework_TestSuite
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Centurion cache');
        $suite->addTestSuite('Centurion_Cache_CacheTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Centurion_Cache_AllTests::main') {
    Centurion_Cache_AllTests::main();
}
