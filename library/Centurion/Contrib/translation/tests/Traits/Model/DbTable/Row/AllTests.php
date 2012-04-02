<?php
if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Translation_Test_AllTests::main');
}
require dirname(__FILE__) . '/../../../../../../../../../tests/TestHelper.php';

/**
 * @class Translation_Test_Traits_Model_DbTable_Row_AllTests
 * @package Tests
 * @subpackage Translation
 * @author Richard Déloge, rd@octaveoctave.com
 *
 * To run all tests on row of the trait translation
 */
class Translation_Test_Traits_Model_DbTable_Row_AllTests extends PHPUnit_Framework_TestSuite
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Translation Traits Db Table Row Suite');
        $suite->addTestSuite('Translation_Test_Traits_Model_DbTable_Row_LocalizedTest');
        $suite->addTestSuite('Translation_Test_Traits_Model_DbTable_Row_NonExistantLocalizedTest');
        $suite->addTestSuite('Translation_Test_Traits_Model_DbTable_Row_OriginalTest');
        
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Translation_Test_AllTests::main') {
    Translation_Test_AllTests::main();
}
