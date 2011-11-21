<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Centurion_Signal_AllTests::main');
}

require_once dirname(__FILE__) . '/../../../TestHelper.php';

class Centurion_Signal_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Centurion Suite: Signals');
        $suite->addTestSuite('Centurion_Signal_PreInitTest');
        
        return $suite;
    }
}
if (PHPUnit_MAIN_METHOD == 'Centurion_Signal_AllTests::main') {
    Centurion_Signal_AllTests::main();
}