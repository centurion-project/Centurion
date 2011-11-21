<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Centurion_Model_DbTable_AllTests::main');
}

require_once dirname(__FILE__) . '/../../../../TestHelper.php';

class Centurion_Model_DbTable_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Centurion Suite: Tables');
        $suite->addTestSuite('Centurion_Model_DbTable_PermissionTest');
        $suite->addTestSuite('Centurion_Model_DbTable_UserTest');
        $suite->addTestSuite('Centurion_Model_DbTable_UserPermissionTest');
        $suite->addTestSuite('Centurion_Model_DbTable_GroupTest');
        $suite->addTestSuite('Centurion_Model_DbTable_GroupPermissionTest');
        $suite->addTestSuite('Centurion_Model_DbTable_BelongTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Centurion_Model_DbTable_AllTests::main') {
    Centurion_Model_DbTable_AllTests::main();
}