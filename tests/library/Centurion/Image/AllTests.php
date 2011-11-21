<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Centurion_AllTests::main');
}

require_once dirname(__FILE__) . '/../../../TestHelper.php';

class Centurion_Image_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Centurion Suite : Image');
        $suite->addTest(Centurion_Image_Adapter_AllTests::suite());
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Centurion_Image_AllTests::main') {
    Centurion_Image_AllTests::main();
}