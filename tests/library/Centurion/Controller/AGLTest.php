<?php

require_once dirname(__FILE__) . '/../../../TestHelper.php';

class Centurion_Controller_AGLTest extends PHPUnit_Framework_TestCase
{

    protected function _getNewAglController()
    {
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $aglController = new Centurion_Controller_AGL($request, $response);

        return $aglController;
    }

    public function testSelect()
    {
        $aglController = $this->_getNewAglController();

        $table = new Asset_Model_DbTable_Simple();
        $aglController->setModel($table);

        $select = $aglController->getSelectFiltred();

        $this->assertCount(0, $select);
    }
}
