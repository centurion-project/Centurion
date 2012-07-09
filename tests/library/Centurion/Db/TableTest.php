<?php

require_once dirname(__FILE__) . '/../../../TestHelper.php';

class Centurion_Db_TableTest extends PHPUnit_Framework_TestCase
{
    public function testGenRefRuleName()
    {
        $table = new Centurion_Db_Table();
        $uniqName = $table->testGenRefRuleName('name');
        $this->assertEquals('name', $uniqName);
        
        //This had cause infinite loop because no name was given
        $table->setDependentTables(array('Centurion_Db_Table'));
        $uniqName = $table->testGenRefRuleName('name');
        
        $table->setReferences(
            array('name' => 
                    array(
                      'columns'       => 'test_id',
                      'refColumns'    => 'id',
                      'refTableClass' => 'Centurion_Db_Table',
                    )));
        
        //We must have something else than name
        $uniqName = $table->testGenRefRuleName('name');
        $this->assertNotEquals('name', $uniqName);
    }

    /**
     * @covers Centurion_Db_Table_Abstract::__call
     */
    public function testMagicFunctionFindby()
    {
        $simpleTable = new Asset_Model_DbTable_Simple();
        $simpleTable->delete(array(new Zend_Db_Expr('1')));

        $test1Row = $simpleTable->insert(array('title' => 'test', 'retrieve' => true));
        $simpleTable->insert(array('title' => 'test'));

        $resultRowSet = $simpleTable->findByTitle('test');
        $this->assertCount(2, $resultRowSet);

        $resultRow = $simpleTable->findOneById($test1Row->id);

        $this->assertEquals($test1Row->pk, $resultRow->pk);

        $resultRow = $simpleTable->findOneByIdAndTitle($test1Row->id, 'test');
        $this->assertEquals($test1Row->pk, $resultRow->pk);
    }

    /**
     * @covers Centurion_Db_Table_Abstract::__call
     */
    public function testWrongCall()
    {
        $simpleTable = new Asset_Model_DbTable_Simple();
        $this->setExpectedException('Centurion_Db_Table_Exception');
        //The function findOneBy (called by __call) except at least 1 parameter
        $simpleTable->findOneById();
    }
}
