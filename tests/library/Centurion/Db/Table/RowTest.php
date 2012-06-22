<?php
require_once dirname(__FILE__) . '/../../../../TestHelper.php';

/**
 * @TODO: test _getFirstOrLastSelectByField
 */
class Centurion_Db_Table_RowTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $groupTable = Centurion_Db::getSingleton('auth/group');

        $data = array(
            array('10', 'test_1'),
            array('11', 'test_2'),
            array('12', 'test_2'),
            array('13', 'test_3'),
        );

        foreach ($data as $data) {
            $groupTable->getOrCreate(array('name' => $data[1], 'id' => $data[0]));
        }

    }

    public function tearDown()
    {
        $groupTable = Centurion_Db::getSingleton('auth/group');
        $select = $groupTable->select()->where('name like(\'test_%\')');
        $select->fetchAll()->delete();
    }

    public function testSimplePrevious()
    {
        $groupTable = Centurion_Db::getSingleton('auth/group');

        $select = $groupTable->select()->order('id');
        $groupRowSet = $select->fetchAll();

        $count = count($groupRowSet);

        $previousId = null;

        foreach ($groupRowSet as $key => $groupRow) {
            $previousRow = $groupRow->getPreviousBy('id');

            if (null !== $previousId) {
                //We always should have a previous
                $this->assertNotNull($previousRow);
                //We test that it's the same as previous loop
                $this->assertEquals($previousId, $previousRow->id);
            } else {
                //For the first item, we should have not previous
                $this->assertNull($previousRow);
            }

            $this->assertEquals($key, $groupRow->getPreviousCountBy('id'));

            $previousId = $groupRow->id;
        }
    }

    public function testSimpleNext()
    {
        $groupTable = Centurion_Db::getSingleton('auth/group');

        $select = $groupTable->select()->order('id');
        $groupRowSet = $select->fetchAll();

        $count = count($groupRowSet);

        $nextId = null;

        //We iterate in reverse order
        for ($i = $count-1 ; $i > 0 ; $i--) {
            $groupRow = $groupRowSet[$i];

            $nextRow = $groupRow->getNextBy('id');
            if (null !== $nextId) {
                //We always should have a next
                $this->assertNotNull($nextRow);
                //We test that it's the same as previous loop
                $this->assertEquals($nextId, $nextRow->id);
            } else {
                //For the first item, we should have not next
                $this->assertNull($nextRow);
            }

            $this->assertEquals($count - $i - 1, $groupRow->getNextCountBy('id'));

            $nextId = $groupRow->id;
        }
    }

    /**
     * Test with 2 order both asc
     */
    public function testComplexSameOrder()
    {
        $groupTable = Centurion_Db::getSingleton('auth/group');

        //Two order both asc
        $select = $groupTable->select()->where('name like(\'test_%\')')->order('name asc')->order('id asc');

        //We get the first one
        $groupRow = $select->fetchRow();
        $data = array();

        while (null !== $groupRow) {
            $data[] = $groupRow->id;
            //We iterate by using getNextBy on each $group;
            $groupRow = $groupRow->getNextBy('name', null, $select);
        }

        $expected = array(
            '10', '11', '12', '13'
        );

        $this->assertEquals($expected, $data);
    }

    /**
     * Test with 2 order one asc the other desc
     */
    public function testComplexDifferentOrder()
    {
        $groupTable = Centurion_Db::getSingleton('auth/group');

        //Two order, first asc, the second one desc
        $select = $groupTable->select()->where('name like(\'test_%\')')->order('name asc')->order('id desc');

        //We get the first one
        $groupRow = $select->fetchRow();
        $data = array();
        while (null !== $groupRow) {
            $data[] = $groupRow->id;
            //We iterate by using getNextBy on each $group;
            $groupRow = $groupRow->getNextBy('name', null, $select);
        }

        $expected = array(
            '10', '12', '11', '13'
        );

        $this->assertEquals($expected, $data);
    }

    /**
     * @covers Centurion_Db_Table_Row_Abstract::isNew
     */
    public function testFunctionIsNew()
    {
        $table = new Asset_Model_DbTable_Simple();

        $row = $table->createRow();

        $this->assertTrue($row->isNew());

        $row->title = 'test';
        $row->save();

        $this->assertFalse($row->isNew());
    }

    /**
     * @covers Centurion_Db_Table_Row_Abstract::__get
     */
    public function testUnExistantColumnsDirectly()
    {
        $table = new Asset_Model_DbTable_Simple();
        $row = $table->createRow();

        //This columns exists. No exception should be throw
        $row->title;

        $this->setExpectedException('Zend_Db_Table_Row_Exception');

        //This columns does not exist. An exception should be throw
        $row->label;
    }

    /**
     * @covers Centurion_Db_Table_Row_Abstract::columnsExists
     */
    public function testUnExistantColumnsWithColumnsExistsFunction()
    {
        $table = new Asset_Model_DbTable_Simple();
        $row = $table->createRow();

        //This columns exists
        $this->assertTrue($row->columnsExists('title'));

        //This columns does not exist
        $this->assertFalse($row->columnsExists('label'));
    }

    /**
     * @covers Centurion_Db_Table_Row_Abstract::__set
     */
    public function testFunction__Set()
    {
        $table = new Asset_Model_DbTable_Simple();
        $row = $table->createRow();

        $row->id = 'test';

        try {
            $row->imnotacolumn = 'test';
            $this->fail('Setting a column that not exist should raised an exception');
        } catch (Centurion_Db_Table_Exception $e) {

        }

    }

    /**
     * @covers Centurion_Db_Table_Row_Abstract::getModifiedData
     * @covers Centurion_Db_Table_Row_Abstract::reset
     */
    public function testModifiedData()
    {
        $table = new Asset_Model_DbTable_Simple();
        $row = $table->createRow();

        $row->save();

        $row->title = 'test';

        $this->assertTrue(array_key_exists('title', $row->getModifiedData()));
        $row->reset();
        $this->assertFalse(array_key_exists('title', $row->getModifiedData()));
    }

    public function testFunctionGetModifiedFields()
    {
        $table = new Asset_Model_DbTable_Simple();
        $row = $table->createRow();

        $this->assertEmpty($row->getModifiedFields());

        $row->title = "test";
        $this->assertEquals(array('title' => true), $row->getModifiedFields());

        $row->reset();
        $this->assertEmpty($row->getModifiedFields());
    }

}

