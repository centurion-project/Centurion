<?php

if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Translation_Test_Models_AllTests::main');
}

require_once dirname(__FILE__) . '/../../../../../../tests/TestHelper.php';

class Translation_Test_Models_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Translation Models');
        $suite->addTestSuite('Translation_Test_Models_LanguageTest');
        $suite->addTestSuite('Translation_Test_Models_TagTest');
        $suite->addTestSuite('Translation_Test_Models_TagUidTest');
        $suite->addTestSuite('Translation_Test_Models_TranslationTest');
        $suite->addTestSuite('Translation_Test_Models_UidTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Translation_Test_Models_AllTests::main') {
    Translation_Test_Models_AllTests::main();
}
