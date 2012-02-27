<?php

require_once dirname(__FILE__) . '/../../../../../tests/TestHelper.php';

class Centurion_Model_DbTable_PermissionTest extends Centurion_Model_DbTable_AbstractModelTestCase
{
    protected $_data = array(
        'name'          =>  'My permission name',
        'description'   =>  'My permission description'
    );
    
    public function setUp()
    {
        $this->_model = Centurion_Db::getSingleton('auth/permission');
        parent::setUp();
    }
}