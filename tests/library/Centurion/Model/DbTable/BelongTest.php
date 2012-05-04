<?php

require_once dirname(__FILE__) . '/../../../../../tests/TestHelper.php';

class Centurion_Model_DbTable_BelongTest extends Centurion_Model_DbTable_AbstractModelTestCase
{
    protected $_data = array(
        'user_id'       =>  1,
        'group_id'      =>  1
    );
    
    public function setUp()
    {
        $this->_model = Centurion_Db::getSingleton('auth/belong');
        parent::setUp();    
    }
}