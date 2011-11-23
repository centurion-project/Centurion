<?php

if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Translation_Test_Controllers_AllTests::main');
}

require_once dirname(__FILE__) . '/../../../../../../tests/TestHelper.php';

class Translation_Test_Controllers_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Translation controllers');
        //$suite->addTestSuite('Quizz_Test_Controllers_QuestionControllerTest');
        //$suite->addTestSuite('Quizz_Test_Controllers_ItemControllerTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Translation_Test_Controllers_AllTests::main') {
    Translation_Test_Controllers_AllTests::main();
}
