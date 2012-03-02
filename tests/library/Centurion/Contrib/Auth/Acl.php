<?php

require_once dirname(__FILE__) . '/../../../../../tests/TestHelper.php';

class Centurion_Contrib_Auth_Acl extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        global $application;
        $application->getBootstrap()->getResource('cachemanager')->getCache('core')->remove('Centurion_Acl');
        Zend_Registry::set('Centurion_Acl', null);
        
        $this->_userTable = Centurion_Db::getSingleton('auth/user');
        $this->_groupTable = Centurion_Db::getSingleton('auth/group');
        $this->_groupPermissionTable = Centurion_Db::getSingleton('auth/group_permission');
        $this->_userPermissionTable = Centurion_Db::getSingleton('auth/user_permission');
        $this->_belongTable = Centurion_Db::getSingleton('auth/belong');
        $this->_permissionTable = Centurion_Db::getSingleton('auth/permission');
        
        $this->_belongTable->deleteRow(array('1'));
        $this->_groupPermissionTable->deleteRow(array('1'));
        $this->_userTable->deleteRow(array('1'));
        $this->_groupTable->deleteRow(array('1'));
        $this->_userPermissionTable->deleteRow(array('1'));
        $this->_permissionTable->deleteRow(array('1'));
    }
    
    protected function tearDown()
    {
        $this->_belongTable->deleteRow(array('1'));
        $this->_groupPermissionTable->deleteRow(array('1'));
        $this->_userTable->deleteRow(array('1'));
        $this->_groupTable->deleteRow(array('1'));
        $this->_userPermissionTable->deleteRow(array('1'));
        $this->_permissionTable->deleteRow(array('1'));
    }
    
    public function testFilters()
    {
        global $application;
        
        // Get the auth bootstrap
        $modules = $application->getBootstrap()->getResource('modules');
        $authBootstrap = $modules['auth'];
        
        $this->assertType('Auth_Bootstrap', $authBootstrap, 'Unable to get bootstrap Auth_Bootstrap (or type name have changed)');
        
        $userTable = Centurion_Db::getSingleton('auth/user');
        $groupTable = Centurion_Db::getSingleton('auth/group');
        $groupPermissionTable = Centurion_Db::getSingleton('auth/group_permission');
        $userPermissionTable = Centurion_Db::getSingleton('auth/user_permission');
        $belongTable = Centurion_Db::getSingleton('auth/belong');
        $permissionTable = Centurion_Db::getSingleton('auth/permission');
        
        $this->assertEquals($this->_userTable->select(true)->count(), 0, 'Db is not clean');
        $this->assertEquals($this->_groupTable->select(true)->count(), 0, 'Db is not clean');
        $this->assertEquals($this->_groupPermissionTable->select(true)->count(), 0, 'Db is not clean');
        $this->assertEquals($this->_userPermissionTable->select(true)->count(), 0, 'Db is not clean');
        $this->assertEquals($this->_belongTable->select(true)->count(), 0, 'Db is not clean');
        $this->assertEquals($this->_permissionTable->select(true)->count(), 0, 'Db is not clean');
        
        $users = array(
            array(Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true, 'username' => 'user1'),
            array(Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true, 'username' => 'user2'),
            array(Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true, 'username' => 'user3'),
            array(Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true, 'username' => 'user4'),
        );
        
        foreach ($users as $key => $data) {
            $userRows[$key] = $this->_userTable->insert($data);
            $this->assertNotNull($userRows[$key], 'Unable to insert in Db (user)');
        }
        
        $groups = array(
            array(Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true, 'name' => 'group1'),
            array(Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true, 'name' => 'group2'),
            array(Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true, 'name' => 'group3'),
            array(Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true, 'name' => 'group4'),
        );
        
        foreach ($groups as $key => $data) {
            $groupRows[$key] = $this->_groupTable->insert($data);
            $this->assertNotNull($groupRows[$key], 'Unable to insert in Db (group)');
        }
        
        $matriceBelong = array(
            array(0, 1, 0, 1),
            array(0, 0, 1, 1),
            array(0, 0, 0, 0),
            array(1, 1, 1, 1),
        );
        
        foreach ($matriceBelong as $groupKey => $matrice) {
            foreach ($matrice as $userKey => $bool) {
                if ($bool) {
                    $belongRow = $this->_belongTable->insert(array(Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true, 'user_id' => $userRows[$userKey]->id, 'group_id' => $groupRows[$groupKey]->id));
                    $this->assertEquals($belongRow->user_id, $userRows[$userKey]->id);
                    $this->assertEquals($belongRow->group_id, $groupRows[$groupKey]->id);
                }
            }
        }
        
        $permissions = array(
            array(Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true, 'name' => 'permission1'),
            array(Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true, 'name' => 'permission2'),
            array(Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true, 'name' => 'permission3'),
            array(Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true, 'name' => 'permission4'),
        );
        
        foreach ($permissions as $key => $data) {
            $permissionRows[$key] = $this->_permissionTable->insert($data);
            $this->assertNotNull($permissionRows[$key], 'Unable to insert in Db (permission)');
        }
        
        $matricePermission = array(
            array(0, 1, 0, 1),
            array(0, 0, 1, 1),
            array(0, 0, 0, 0),
            array(1, 1, 1, 1),
        );
        
        foreach ($matricePermission as $groupKey => $matrice) {
            foreach ($matrice as $permissionKey => $bool) {
                if ($bool) {
                    $groupPermissionRow = $this->_groupPermissionTable->insert(array(Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true, 'permission_id' => $permissionRows[$permissionKey]->id, 'group_id' => $groupRows[$groupKey]->id));
                    $this->assertEquals($groupPermissionRow->permission_id, $permissionRows[$permissionKey]->id);
                    $this->assertEquals($groupPermissionRow->group_id, $groupRows[$groupKey]->id);
                }
            }
        }
        
        $authBootstrap->testInitAcl();
        
        $acl = Zend_Registry::get('Centurion_Acl');
        
        foreach ($matricePermission as $groupKey => $matrice) {
            foreach ($matrice as $permissionKey => $bool) {
                $this->assertEquals((boolean) $bool, $groupRows[$groupKey]->isAllowed($permissionRows[$permissionKey]->name));
            }
        }
    }
}
