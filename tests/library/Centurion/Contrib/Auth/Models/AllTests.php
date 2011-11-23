<?php
if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Centurion_Contrib_Auth_Models_AllTests::main');
}

require_once dirname(__FILE__) . '/../../../../../TestHelper.php';
 
class Centurion_Contrib_Auth_Models_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        $suite = new PHPUnit_Framework_TestSuite('Centurion contrib auth models');
        //$suite->addTestSuite('Centurion_Contrib_Auth_Models_UserTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Centurion_Contrib_Auth_Models_AllTests::main') {
    Centurion_Contrib_Auth_AllTests::main();
}