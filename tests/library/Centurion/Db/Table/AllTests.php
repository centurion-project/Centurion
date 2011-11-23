<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Centurion_Db_Table_AllTests::main');
}

require_once dirname(__FILE__) . '/../../../../TestHelper.php';

class Centurion_Db_Table_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Centurion Suite: Tables');
        $suite->addTestSuite('Centurion_Db_Table_SelectTest');
        $suite->addTestSuite('Centurion_Db_Table_ModelTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Centurion_Model_DbTable_AllTests::main') {
    Centurion_Db_Table_AllTests::main();
}