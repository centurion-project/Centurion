<?php

require_once dirname(__FILE__) . '/../../../../TestHelper.php';

class Centurion_Db_Table_SelectTest extends PHPUnit_Framework_TestCase
{
    public function testFilters()
    {
        $userTable = Centurion_Db::getSingleton('auth/user');
        $groupTable = Centurion_Db::getSingleton('auth/group');
        $groupPermissionTable = Centurion_Db::getSingleton('auth/user_permission');
        
        $groupTable->fetchAll(array('name=?' => 'test'))->delete();
        $userTable->fetchAll(array('username=?' => 'test'))->delete();
        
        $userRow = $userTable->insert(array('username'          =>  'test',
                                            'password'          =>  'test',
                                            'user_parent_id'    =>   null,
                                            'can_be_deleted'    =>   1,
                                            'retrieve'          =>  true));

        
        $this->assertFalse($userRow === null);

        $groupRow = $groupTable->insert(array('name'     =>  'test',
                                              'retrieve' =>  true));
        $this->assertFalse($groupRow === null);

        $filteredGroupRow = $groupTable->get(array('name' => 'test'));
        $filteredUserRow = $userTable->get(array('username' => 'test'));

        $this->assertFalse($filteredGroupRow instanceof Zend_Db_Table_Row);
        $this->assertFalse($filteredUserRow instanceof Zend_Db_Table_Row);

        $this->assertEquals($groupRow->toArray(), $filteredGroupRow->toArray());

        $this->assertEquals($userRow->toArray(), $filteredUserRow->toArray());

        $userRow->groups->add($groupRow);

        $this->assertFalse($groupRow->has('users', $userRow) === null);

        $userRowset = $userTable->filter(array('groups__name' => 'test'));

        $this->assertFalse(!count($userRowset));

        $this->assertEquals($userRow->toArray(), $userRowset->current()->toArray());

        $userRowset = $userTable->filter(array('groups__name__exact' => 'test'));

        $this->assertFalse(!count($userRowset));

        $this->assertEquals($userRow->toArray(), $userRowset->current()->toArray());
    }
    
    public function testMultiJointure()
    {
        $permissionTable = Centurion_Db::getSingleton('auth/permission');
        $permissionRow = $permissionTable->insert(array('name' => 'test', Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true));
        
        $userOriginalRow = Centurion_Db::getSingleton('auth/user')->insert(array('username' => 'testOriginal', Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true));
        $userRow = Centurion_Db::getSingleton('auth/user')->insert(array('username' => 'test', 'user_parent_id' => $userOriginalRow->id, Centurion_Db_Table_Abstract::RETRIEVE_ROW_ON_INSERT => true));
        
        Centurion_Db::getSingleton('auth/user_permission')->insert(array('permission_id' => $permissionRow->id, 'user_id' => $userRow->id));
        
        $select = $permissionTable->select(true)->filter(array('users__parent_user__id' => $userOriginalRow->id));
        
        $this->assertEquals($select->count(), 1, sprintf("Except 1, found %d\n", $select->count()));
        
    }
}