<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Centurion_File_AllTests::main');
}

require_once dirname(__FILE__) . '/../../../TestHelper.php';

class Centurion_File_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Centurion file system');
        $suite->addTestSuite('Centurion_File_SystemTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Centurion_File_AllTests::main') {
    Centurion_File_System_AllTests::main();
}
