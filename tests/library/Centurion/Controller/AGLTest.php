<?php

require_once dirname(__FILE__) . '/../../../TestHelper.php';

class Centurion_Controller_AGLTest extends PHPUnit_Framework_TestCase
{

    protected function _getNewAglController($request = null)
    {
        if (null === $request) {
            $request = new Zend_Controller_Request_Http();
        }
        $response = new Zend_Controller_Response_HttpTestCase();

        $aglController = new Centurion_Controller_AGL($request, $response);

        return $aglController;
    }

    /**
     * @covers Centurion_Controller_AGL::getSelectFiltred
     */
    public function testSelect()
    {
        $aglController = $this->_getNewAglController();

        $table = new Asset_Model_DbTable_Simple();
        $aglController->setModel($table);

        $select = $aglController->getSelectFiltred();

        $this->assertCount(0, $select);
    }

    /**
     * @covers Centurion_Controller_AGL::getModel
     * @covers Centurion_Controller_AGL::setModel
     */
    public function testError()
    {
        $aglController = $this->_getNewAglController();
        try {
            $aglController->getModel();
            $this->fail('Function getModel should raise an exception when no model given.');
        } catch (Centurion_Controller_Action_Exception $e) {
            //Should pass here
        }
        
        $aglController->setModel('asset/simple');
        $this->assertInstanceOf('Asset_Model_DbTable_Simple', $aglController->getModel());
    }
    
    /**
     * @covers Centurion_Controller_AGL::getSelect
     */
    public function testGetSelect()
    {
        $aglController = $this->_getNewAglController();
        $aglController->setModel('asset/simple');
        
        $this->assertInstanceOf('Centurion_Db_Table_Select', $aglController->getSelect());
    }
}
