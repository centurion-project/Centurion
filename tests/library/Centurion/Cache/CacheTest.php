<?php

require_once dirname(__FILE__) . '/../../../TestHelper.php';

class Centurion_Cache_CacheTest extends PHPUnit_Framework_TestCase
{
    /**
     * 
     * Enter description here ...
     * @return Centurion_Cache_Manager
     */
    public function getCacheManager()
    {
        global $application;
        return $application->getBootstrap()->getResource('cachemanager');
    }
    
    protected function setUp()
    {
        $cacheManager = $this->getCacheManager();
        if (is_null($cacheManager)) {
            $this->fail('Could not find cache manager in test Centurion_Cache_CacheTest');
        }
        
        //TODO: fix this, we don't have to force signal to be connected, it must already been connected. PB came frome Centurion_Test_PHPUnit_ControllerTestCase::tearDown() with Centurion_Signal::unregister(); command
        $this->getCacheManager()->connectSignal();
        $this->getCacheManager()->cleanCache(Zend_Cache::CLEANING_MODE_ALL);
    }
    
    protected function tearDown()
    {
        $cacheManager = $this->getCacheManager();
        if (is_null($cacheManager)) {
            $this->fail();
        }

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
    
    public function testCacheObject()
    {
        
    }
    
    public function testCacheWithRelation()
    {
        $userTable = Centurion_Db::getSingleton('auth/user');
        $userPermissionTable = Centurion_Db::getSingleton('auth/userPermission');
        $cacheCore = $this->getCacheManager()->getCache('core');

        $userTable->getOrCreate(array('username' => 'userrow for testCacheWithRelation'));

        $str = sha1(uniqid());
        $id = sha1(uniqid());
        
        $tags = array();
        $adminUser = $userTable->fetchRow(array('username=?' => 'userrow for testCacheWithRelation'));
        if (null == $adminUser) {
            //TODO: We should add row that we need
            $this->fail('Admin not found in db');
        }
        $tags[] = $adminUser->getCacheTag('user_permissions');
        
        $cacheCore->save($str, $id, $tags);
        $this->assertEquals($str, $cacheCore->load($id));
        
        $permission = Centurion_Db::getSingleton('auth/permission')->createRow(array('permission' => sha1(uniqid())));
        $permission->save();
        
        $row = $userPermissionTable->createRow(array('user_id' => $adminUser->id, 'permission_id' => $permission->id));
        $row->save();
        
        $this->assertFalse($cacheCore->load($id));
        
        $cacheCore->save($str, $id, $tags);
        $this->assertEquals($str, $cacheCore->load($id));
        
        $row->delete();
        $this->assertFalse($cacheCore->load($id));
    }
}
