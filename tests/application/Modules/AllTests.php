<?php
if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Modules_AllTests::main');
}

require_once dirname(__FILE__) . '/../../TestHelper.php';

class Modules_AllTests extends PHPUnit_Framework_TestSuite
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    protected static function _formatModuleName($name)
    {
        $name = strtolower($name);
        $name = str_replace(array('-', '.'), ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        return $name;
    }

    public static function suite ()
    {
        global $application;

        $suite = new PHPUnit_Framework_TestSuite('Modules Suite');
        $bootstrap = $application->getBootstrap();

        $moduleRessource = $bootstrap->getResource('modules');
        $front = $bootstrap->getResource('FrontController');
        $modules = $front->getControllerDirectory();

        foreach ($modules as $key => $val) {
            $className = self::_formatModuleName($key) . '_Test_AllTests';
            if (class_exists($className, true)) {
                $suiteFunctionName = array($className, 'suite');
                $suite->addTest(call_user_func($suiteFunctionName));
            }
        }
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Modules_AllTests::main') {
    Modules_AllTests::main();
}
