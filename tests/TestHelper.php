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
    get_include_path(),
)));

require realpath(dirname(__FILE__) . '/../library/library.php');
require realpath(dirname(__FILE__) . '/../public/index.php');

$autoloader = Zend_Loader_Autoloader::getInstance()
    ->pushAutoloader(create_function('$class',
        "return @include str_replace('_', '/', \$class) . '.php';"
    ), '');

$application->bootstrap('db')
            ->bootstrap('FrontController')
            ->bootstrap('modules')
            ->getBootstrap()->getResource('FrontController')
                            ->setParam('bootstrap', $application->getBootstrap());

try {
    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
    $db->beginTransaction();

    $query = '';
    foreach (new SplFileObject(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'schema.sql') as $line) {
        $query .= $line;
        if (substr(rtrim($query), -1) == ';') {
            $db->query($query);
            $query = '';
        }
    }

    $db->commit();

} catch(Exception $e) {
    $db->rollback();
    exit($e->getMessage());
}