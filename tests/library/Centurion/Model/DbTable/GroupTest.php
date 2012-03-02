<?php

require_once dirname(__FILE__) . '/../../../../../tests/TestHelper.php';

class Centurion_Model_DbTable_GroupTest extends Centurion_Model_DbTable_AbstractModelTestCase
{
    protected $_data = array(
        'name'          =>  'My group name',
        'description'   =>  'My group description'
    );
    
    public function setUp()
    {
        $this->_model = Centurion_Db::getSingleton('auth/group');
        parent::setUp();    
    }
}