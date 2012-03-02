<?php
require_once dirname(__FILE__) . '/../../../../TestHelper.php';

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
            $groupTable->insert(array('name' => $data[1], 'id' => $data[0]));
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
}

