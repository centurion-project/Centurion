<?php

require_once dirname(__FILE__) . '/../../../../../tests/TestHelper.php';

class Centurion_Model_DbTable_UserTest extends Centurion_Model_DbTable_AbstractModelTestCase
{
    protected $_data = array(
        'username'          =>  'admintesting',
        'password'          =>  'admincenturion',
        'user_parent_id'    =>   null,
        'can_be_deleted'    =>   1
    );
    
    public function setUp()
    {
        $this->_model = Centurion_Db::getSingleton('auth/user');
        parent::setUp();
    }
}