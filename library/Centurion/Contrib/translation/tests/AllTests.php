<?php
if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Translation_Test_AllTests::main');
}
require dirname(__FILE__) . '/../../../../../tests/TestHelper.php';

class Translation_Test_AllTests extends PHPUnit_Framework_TestSuite
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Translation Suite');
        $suite->addTest(Translation_Test_Models_AllTests::suite());
        $suite->addTest(Translation_Test_Controllers_AllTests::suite());
        $suite->addTestSuite('Translation_Test_TranslateTest');
        
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Translation_Test_AllTests::main') {
    Translation_Test_AllTests::main();
}
