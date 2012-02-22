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
        $select = Centurion_Db::getSingleton('user/profile')->select(true);
        
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
        $select = Centurion_Db::getSingleton('user/profile')->select(true);
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
        $select = Centurion_Db::getSingleton('user/profile')->select(true);
        
    }
    
    /**
     * TODO should add user before try to get it.
     * @
     */
    public function testFilter()
    {
        $select = Centurion_Db::getSingleton('user/profile')->select(true);
    
        $select->filter(array('user__id' => 1));
        $select->filter(array('user__username' => 'admin'));
        
        $adminRow = $select->fetchAll();
        
        $this->assertNotNull($adminRow);
    }
    
    public function testMany()
    {
        $select = Centurion_Db::getSingleton('auth/user')->select(true);
        $select->addRelated('groups__id');
    
        $this->assertTrue($select->isAlreadyJoined('auth_belong'));
        $this->assertTrue($select->isAlreadyJoined('auth_belong', '`auth_belong`.`user_id` = `auth_user`.`id`'));
    
        $select->addRelated('groups__id');
        $select->addRelated('groups__users__id');
    }
    
    public function testDependant()
    {
        $select = Centurion_Db::getSingleton('auth/user')->select(true);
        
        $select->joinInner('user_profile', 'user_profile.id = 1', false);
        
        $select->filter(array('!profiles__id__isnull' => null));
        
        //TODO: test that is good
    }
    
    public function testJoinToSameTableWithDifferCondition()
    {
        $select = Centurion_Db::getSingleton('auth/user')->select(true);
    
        $select->addRelated('groups__users__id');
    
        $select->filter(array('user_parent__username' => 'admin'));
    
        echo $select;
        die();
        $select->limit(2);
    
        $rowSet = $select->fetchAll();
    
        $this->assertEquals(1, $rowSet->count());
    }
}

