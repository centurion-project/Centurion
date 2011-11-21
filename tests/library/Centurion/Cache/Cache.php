<?php

class Centurion_Cache_Cache extends PHPUnit_Framework_TestCase
{
    protected $_cacheManager = null;
    
    /**
     * 
     * Enter description here ...
     * @return Centurion_Cache_Manager
     */
    public function getCacheManager()
    {
        if (null === $this->_cacheManager) {
            global $application;
            $this->_cacheManager = $application->getBootstrap()->getResource('cachemanager');
        }
        return $this->_cacheManager;
    }
    
    protected function setUp()
    {
        $this->getCacheManager()->cleanCache(Zend_Cache::CLEANING_MODE_ALL);
    }
    
    protected function tearDown()
    {
        $this->getCacheManager()->cleanCache(Zend_Cache::CLEANING_MODE_ALL);
    }
    
    public function testFilters()
    {
        $cacheCore = $this->getCacheManager()->getCache('core');
        
        $str = sha1(uniqid());
        $id = sha1(uniqid());
        $tag = sha1(uniqid());
        
        $cacheCore->save($str, $id);
        $this->assertEquals($str, $cacheCore->load($id));
        
        //Test remove by Id
        $cacheCore->remove($id);
        $this->assertFalse($cacheCore->load($id));
        
        $cacheCore->save($str, $id, array($tag));
        $this->assertEquals($str, $cacheCore->load($id));
        
        //Test remove by tag
        $cacheCore->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array($tag));
        $this->assertFalse($cacheCore->load($id));
        
        //Test timeout
        $cacheCore->save($str, $id, array(), 1);
        $this->assertEquals($str, $cacheCore->load($id));
        
        sleep(2);
        $cacheCore->clean(Zend_Cache::CLEANING_MODE_OLD);
        $this->assertFalse($cacheCore->load($id));        
        
    }
    
    public function testCleanCacheSignal()
    {
        $cacheCore = $this->getCacheManager()->getCache('core');
        
        $str = sha1(uniqid());
        $id = sha1(uniqid());
        $cacheCore->save($str, $id);
        
        $this->assertEquals($str, $cacheCore->load($id));
        Centurion_Signal::factory('clean_cache')->send($this);
        $this->assertFalse($cacheCore->load($id));
        //TODO: finish
    }
        
    public function testCacheStatic()
    {
        //TODO: implement test testCacheStatic();
    }
    
}
