<?php

class Centurion_Loader_PluginLoaderTest extends PHPUnit_Framework_TestCase
{
    protected static $_classFileIncCache = null;
    
    protected function setUp()
    {
        self::$_classFileIncCache = realpath(APPLICATION_PATH . '/../data/cache').'/pluginLoaderCache.tmp';

        if (file_exists(self::$_classFileIncCache)) {
            unlink(self::$_classFileIncCache);
        }
        
        Centurion_Loader_PluginLoader::setIncludeFileCache(self::$_classFileIncCache);
    }
    
    public function testCleanCache()
    {
        $this->assertFileNotExists(self::$_classFileIncCache);
        
        Centurion_Loader_PluginLoader::shutdown();
        $this->assertFileExists(self::$_classFileIncCache);
        
        Centurion_Loader_PluginLoader::clean();
        $this->assertFileNotExists(self::$_classFileIncCache);
        
        Centurion_Loader_PluginLoader::shutdown();
        $this->assertFileExists(self::$_classFileIncCache);
    }
    
    //TODO: test serialize, unserialize
}
