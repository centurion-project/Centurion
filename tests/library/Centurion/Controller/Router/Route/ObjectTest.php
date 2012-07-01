<?php

require_once dirname(__FILE__) . '/../../../../../TestHelper.php';

class Centurion_Controller_Router_Route_ObjectTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Centurion_Controller_Router_Route_Object::assemble
     * 
     * The function assemble of Centurion_Controller_Router_Route_Object should get unknow parameter in the data array 
     * from the object that is pass to the function. 
     */
    public function testAssemble()
    {
        $simpleTable = new Asset_Model_DbTable_Simple();
        $row = $simpleTable->createRow();
        
        $row->id = 1;
        $row->title = 'test';
        
        $pattern = '/:id/:title';
        $route = new Centurion_Controller_Router_Route_Object($pattern);
        
        $this->assertEquals('1/test', $route->assemble(array('object' => $row)));
        
        try {
            $route->assemble(array());
            $this->fail('It should send an exception when no object given');
        } catch (Zend_Controller_Router_Exception $e) {
            
        }
    }
}
