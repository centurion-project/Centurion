<?php

require_once dirname(__FILE__) . '/../../../../../tests/TestHelper.php';

class Centurion_Model_DbTable_UserPermissionTest extends Centurion_Model_DbTable_AbstractModelTestCase
{
    protected $_data = array(
        'user_id'           =>  1,
        'permission_id'     =>  1
    );
    
    public function setUp()
    {
        $this->_model = Centurion_Db::getSingleton('auth/user_permission');
        parent::setUp();
    }
}