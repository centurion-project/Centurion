<?php
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

defined('RUN_CLI_MODE')
    || define('RUN_CLI_MODE', true);

defined('PHPUNIT')
    || define('PHPUNIT', true);

ini_set('memory_limit', '2048M');

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__FILE__) . '/application'),
    realpath(dirname(__FILE__) . '/library'),
    realpath(dirname(__FILE__) . '/support'),
    get_include_path(),
)));

require realpath(dirname(__FILE__) . '/../library/library.php');
//TODO: we should not include this. If a test need application, we need to make to propose a factory
require realpath(dirname(__FILE__) . '/../public/index.php');

$autoloader = Zend_Loader_Autoloader::getInstance()
    ->pushAutoloader(create_function('$class',
        "return @include str_replace('_', '/', \$class) . '.php';"
    ), '');

/*
$application->bootstrap('db')
            ->bootstrap('FrontController')
            ->bootstrap('modules')
            ->getBootstrap()->getResource('FrontController')
                            ->setParam('bootstrap', $application->getBootstrap());
*/
