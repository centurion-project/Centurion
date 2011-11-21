<?php
if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Selenium_AllTests::main');
}

require_once dirname(__FILE__) . '/../../TestHelper.php';

class Selenium_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Selenium Suite');
        $suite->addTestSuite('Selenium_BackOffice');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Selenium_AllTests::main') {
    Selenium_AllTests::main();
}