<?php
require_once dirname(__FILE__) . '/../../../../TestHelper.php';

class Centurion_Db_Table_SelectTest extends PHPUnit_Framework_TestCase
{
    public function testNormalizeCondition()
    {
        $select = Centurion_Db::getSingleton('user/profile')->select(true);
        
        $return = $select->normalizeCondition('`auth_user`.`id` = `user_profile`.`user_id`');
        $this->assertEquals('`auth_user`.`id` = `user_profile`.`user_id`', $return);
        
        $return = $select->normalizeCondition('`user_profile`.`user_id` = `auth_user`.`id`');
        
        $this->assertEquals('`auth_user`.`id` = `user_profile`.`user_id`', $return);
        
        $return = $select->normalizeCondition('`user_profile`.`user_id` > `auth_user`.`id`');
        $this->assertEquals('`auth_user`.`id` < `user_profile`.`user_id`', $return);
        
        $return = $select->normalizeCondition('`auth_user`.`id` = `user_id`');
        $this->assertEquals('`auth_user`.`id` = `user_profile`.`user_id`', $return);
        
        $return = $select->normalizeCondition('`auth_user` . id = user_id');
        $this->assertEquals('`auth_user`.`id` = `user_profile`.`user_id`', $return);
        
    }
    
    public function testIsConditionEqualsFunction()
    {
        $select = new Asset_Db_Table_Select(Centurion_Db::getSingleton('user/profile'));
        
        $full = '`auth_user`.`id` = `user_profile`.`user_id`';
        
        $this->assertTrue($select->isConditionEquals($full, $full));
        $this->assertTrue($select->isConditionEquals($full, '`user_profile`.`user_id` = `auth_user`.`id`'));
        $this->assertTrue($select->isConditionEquals('`auth_user`.`id` = `user_id`', '`user_profile`.`user_id` = `auth_user`.`id`'));
        $this->assertTrue($select->isConditionEquals($full, '`user_id` = `auth_user`.`id`'));
        $this->assertTrue($select->isConditionEquals($full, 'auth_user.id = user_profile.user_id'));
        $this->assertFalse($select->isConditionEquals($full, 'id` = user_profile.`user_id`'), 'Id should be prefixed by current table. Should fail');
        
    }
    
    public function testIsAlreadyJoinFunction()
    {
        $select = new Asset_Db_Table_Select(Centurion_Db::getSingleton('user/profile'));
        $select->addRelated('user__id');

        $this->assertTrue($select->isAlreadyJoined('auth_user'));
        
        $this->assertTrue($select->isAlreadyJoined('auth_user', '`auth_user`.`id` = `user_profile`.`user_id`'));
        $this->assertTrue($select->isAlreadyJoined('auth_user', 'auth_user.id = user_profile.user_id'));
        $this->assertTrue($select->isAlreadyJoined('auth_user', 'auth_user.`id` = `user_id`'), 'Fail IsAlreadyJoin when no prefix to column in join cond');
        $this->assertTrue($select->isAlreadyJoined('auth_user', 'auth_user.id = user_profile.user_id'));
    }
    
    public function testGetRelatedJoin()
    {
        $select = Centurion_Db::getSingleton('user/profile')->select(true);
        $select->addRelated('user__id');

        $string = $select->__toString();

        $this->assertContains('INNER JOIN', $string, 'Query should contain INNER JOIN after addRelated() call');
    }

    /**
     * @todo: Test the same thing for many dependant
     */
    public function tesAddRelatedForReferenceMap()
    {
        $select = Centurion_Db::getSingleton('user/profile')->select(true);

        $select->addRelated('user__id');
        $string = $select->__toString();

        $select->addRelated('user__id');
        
        $this->assertEquals($string, $select->__toString(), 'Relation already exists, queries should be equal');

        $select->addRelated('user__email');
        $this->assertEquals($string, $select->__toString(), 'Relation already exists with id field, queries should be equal');
        
        
        //TODO: check that the request is good
        //We want to test that in the second join inner the condition use the same alias as zend will generate.
        $select->addRelated('user__parent_user__id');
        $this->assertNotEquals($string, $select->__toString());
    }
    
    public function testAddRelatedForManyUniq()
    {
        $select = Centurion_Db::getSingleton('auth/user')->select(true);
        $sqlColumns1 = $select->addRelated('groups__id');
        $sqlColumns2 = $select->addRelated('groups__id');

        $this->assertEquals($sqlColumns1, $sqlColumns2);

    }

    public static function getUserForTest($withProfile = false)
    {
        $data = array(
            'username' => 'user for function Centurion_Db_Table_SelectTest::getUserForTest',
        );

        list($userRow, ) = Centurion_Db::getSingleton('auth/user')->getOrCreate($data);

        if ($withProfile) {
            $data = array(
                'user_id' => $userRow->id,
            );
            Centurion_Db::getSingleton('user/profile')->getOrCreate($data);
        }

        return $userRow;
    }

    /**
     * TODO should add user before try to get it.
     * @
     */
    public function testFilter()
    {
        $profileTable = Centurion_Db::getSingleton('user/profile');

        $userRow = self::getUserForTest(true);

        $select = $profileTable->select(true);
    
        $select->filter(array('user__id' => $userRow->id));
        //Two join on same table
        $select->filter(array('user__username' => $userRow->username));
        //In case someone do filter twice
        $select->filter(array('user__username' => $userRow->username));

        $adminRow = $select->fetchAll();
        
        $this->assertCount(1, $adminRow);
    }
    
    public function testMany()
    {
        $select = new Asset_Db_Table_Select(Centurion_Db::getSingleton('auth/user'));
        $select->addRelated('belongs__user_id');
    
        $this->assertTrue($select->isAlreadyJoined('auth_belong'));
        $this->assertTrue($select->isAlreadyJoined('auth_belong', '`auth_belong`.`user_id` = `auth_user`.`id`'));

        $select->filter(array('groups__id' => 1));

        $select->filter(array('groups__name' => 'test1'));

        $this->assertNotContains('auth_group_2', $select->__toString());
        $this->assertCount(3, $select->getPart(Zend_Db_Select::FROM));
    }
    
    public function testDependant()
    {
        $select = Centurion_Db::getSingleton('auth/user')->select(true);
        
        $select->joinInner('user_profile', 'user_profile.id = 1', false);
        
        $select->filter(array('!profiles__id__isnull' => null));
        
        //TODO: test that is good
        $this->markTestIncomplete();
    }
    
    public function testJoinToSameTableWithDifferCondition()
    {
        $select = Centurion_Db::getSingleton('auth/user')->select(true);


        $userRow = self::getUserForTest();

        //TODO: create data
        $select->addRelated('left|groups__left|users__id');
    
        $select->filter(array('left|user_parent__username__isnull' => ''));
        $select->filter(array('username' => $userRow->username));

        $select->limit(2);
        $rowSet = $select->fetchAll();
        $this->assertEquals(1, $rowSet->count());
        $this->assertEquals($userRow->username, $rowSet[0]->username);
    }

    /**
     * @covers Centurion_Db_Table_Select::not
     */
    public function testNotFunctionWithSimpleTable()
    {
        $simpleTable = new Asset_Model_DbTable_Simple();

        $test1Row = $simpleTable->insert(array('title' => 'test1', 'retrieve' => true));
        $test2Row = $simpleTable->insert(array('title' => 'test2', 'retrieve' => true));
        $test3Row = $simpleTable->insert(array('title' => 'test3', 'retrieve' => true));

        $resultRowSet = $simpleTable->select(true)->not($test2Row)->order('id asc')->fetchAll();

        $this->assertCount(2, $resultRowSet);
        $this->assertEquals($test1Row->pk, $resultRowSet[0]->pk);
        $this->assertEquals($test3Row->pk, $resultRowSet[1]->pk);
    }

    /**
     * @covers Centurion_Db_Table_Select::not
     */
    public function testNotFunctionWithMultiplePkTable()
    {
        $simpleTable = new Asset_Model_DbTable_MultiplePk();

        $test1Row = $simpleTable->insert(array('title' => 'test1', 'retrieve' => true));
        $test2Row = $simpleTable->insert(array('title' => 'test2', 'retrieve' => true));
        $test3Row = $simpleTable->insert(array('title' => 'test3', 'retrieve' => true));

        $resultRowSet = $simpleTable->select(true)->not($test2Row)->order('id asc')->fetchAll();

        $this->assertCount(2, $resultRowSet);
        $this->assertEquals($test1Row->pk, $resultRowSet[0]->pk);
        $this->assertEquals($test3Row->pk, $resultRowSet[1]->pk);
    }

    /**
     * @covers Centurion_Db_Table_Select::count
     */
    public function testCountFunction()
    {
        $simpleTable = new Asset_Model_DbTable_MultiplePk();

        $simpleTable->insert(array('title' => 'test1'));
        $simpleTable->insert(array('title' => 'test2'));
        $test3Row = $simpleTable->insert(array('title' => 'test3', 'retrieve' => true));
        $simpleTable->insert(array('title' => '4'));

        $this->assertEquals(4, $simpleTable->count());

        $select = $simpleTable->select(true)->where(new Zend_Db_Expr('title like (\'%test%\')'));

        $this->assertEquals(3, $select->count());
        $select->not($test3Row);

        $this->assertEquals(2, $select->count());
    }
}

