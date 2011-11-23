<?php

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// bootstrap include_path and constants
require realpath(dirname(__FILE__) . '/../library/library.php');

/** Zend_Application */
require_once 'Zend/Application.php';
require_once 'Centurion/Application.php';

require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance()
    ->registerNamespace('Centurion_')
    ->setDefaultAutoloader(create_function('$class',
        "include str_replace('_', '/', \$class) . '.php';"
    ));
$classFileIncCache = realpath(APPLICATION_PATH . '/../data/cache').'/pluginLoaderCache.tmp';
if (file_exists($classFileIncCache)) {
    $fp = fopen($classFileIncCache, 'r');
    flock($fp, LOCK_SH);
    $data = file_get_contents($classFileIncCache);
    flock($fp, LOCK_UN);
    fclose($fp);
    $data = @unserialize($data);

    if ($data !== false) {
        Centurion_Loader_PluginLoader::setStaticCachePlugin($data);
    }
}

Centurion_Loader_PluginLoader::setIncludeFileCache($classFileIncCache);

// Create application, bootstrap, and run
$application = new Centurion_Application(
    APPLICATION_ENV,
    Centurion_Config_Directory::loadConfig(APPLICATION_PATH . '/configs/', APPLICATION_ENV, true)
);

$application->bootstrap();

if (!defined('RUN_CLI_MODE') || RUN_CLI_MODE === false) {
    $application->bootstrap()->run();
}