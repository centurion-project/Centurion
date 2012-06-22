<?php
if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Centurion_AllTests::main');
}

require_once dirname(__FILE__) . '/../../TestHelper.php';

class Centurion_AllTests extends PHPUnit_Framework_TestSuite
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Centurion Suite');
        $suite->addTest(Centurion_Cache_AllTests::suite());
        $suite->addTest(Centurion_Contrib_AllTests::suite());
        $suite->addTest(Centurion_File_AllTests::suite());
        $suite->addTest(Centurion_Model_DbTable_AllTests::suite());
        $suite->addTest(Centurion_Db_Table_AllTests::suite());
        $suite->addTest(Centurion_Signal_AllTests::suite());
        $suite->addTest(Centurion_Image_AllTests::suite());
        $suite->addTestSuite('Centurion_VideopianTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Centurion_AllTests::main') {
    Centurion_AllTests::main();
}