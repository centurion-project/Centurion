<?php

class Centurion_Db_Table_SelectTest extends PHPUnit_Framework_TestCase
{
    public function testFilters()
    {
        $userTable = Centurion_Db::getSingleton('auth/user');
        $groupTable = Centurion_Db::getSingleton('auth/group');
        $groupPermissionTable = Centurion_Db::getSingleton('auth/user_permission');

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
}