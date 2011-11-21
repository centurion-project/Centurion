<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Centurion_Loader_AllTests::main');
}

require_once dirname(__FILE__) . '/../../../TestHelper.php';

class Centurion_Loader_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Centurion Suite : Loader');
        $suite->addTestSuite('Centurion_Loader_PluginLoaderTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Centurion_Loader_AllTests::main') {
    Centurion_Image_AllTests::main();
}