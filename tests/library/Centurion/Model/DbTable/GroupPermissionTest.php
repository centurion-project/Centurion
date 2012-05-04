<?php

require_once dirname(__FILE__) . '/../../../../../tests/TestHelper.php';

class Centurion_Model_DbTable_GroupPermissionTest extends Centurion_Model_DbTable_AbstractModelTestCase
{
    protected $_data = array(
        'group_id'          =>  1,
        'permission_id'     =>  1
    );
    
    public function setUp()
    {
        $this->_model = Centurion_Db::getSingleton('auth/group_permission');
        parent::setUp();    
    }
}