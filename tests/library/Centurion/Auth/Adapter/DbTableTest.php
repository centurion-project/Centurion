<?php

require_once dirname(__FILE__) . '/../../../../../tests/TestHelper.php';


/**
 * @covers Centurion_Auth_Adapter_DbTable
 * @covers Auth_Form_Validator_Login
 */
class Centurion_Auth_Adapter_DbTableTest extends PHPUnit_Framework_TestCase
{
    public function testAuthenticate()
    {
        //$adapter = new Centurion_Auth_Adapter_DbTable($dbAdapter, 'auth_user', 'password');

        Centurion_Auth::getInstance()->setStorage(new Zend_Auth_Storage_NonPersistent());
        
        $validator = new Auth_Form_Validator_Login(array(
                                                        'dbAdapter'         =>  Zend_Db_Table_Abstract::getDefaultAdapter(),
                                                        'tableName'         =>  'auth_user',
                                                        'loginColumn'       =>  'username',
                                                        'passwordColumn'    =>  'password',
                                                        'authAdapter'       =>  'Centurion_Auth_Adapter_DbTable',
                                                        'checkColumn'       =>  'is_active = 1',
                                                   ));

        $this->assertTrue($validator->isValid('admincenturion', array('login' => 'admin')));
        $this->assertFalse($validator->isValid('admin', array('login' => 'admin')));
    }
}
