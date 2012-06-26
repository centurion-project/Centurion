<?php
if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Translation_Test_AllTests::main');
}
require dirname(__FILE__) . '/../../../../../../../../tests/TestHelper.php';

/**
 * @class Translation_Test_Traits_Model_DbTable_AllTests
 * @package Tests
 * @subpackage Translation
 * @author Richard Déloge, rd@octaveoctave.com
 *
 * To run all tests of the trait translation on rows and select
 */
class Translation_Test_Traits_Model_DbTable_AllTests extends PHPUnit_Framework_TestSuite
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Translation Traots Model DbTable Suite');
        $suite->addTest(Translation_Test_Traits_Model_DbTable_Row_AllTests::suite());
        $suite->addTestSuite('Translation_Test_Traits_Model_DbTable_SelectTest');
        
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Translation_Test_AllTests::main') {
    Translation_Test_AllTests::main();
}
