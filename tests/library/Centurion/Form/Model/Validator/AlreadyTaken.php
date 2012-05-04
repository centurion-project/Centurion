<?php

require_once dirname(__FILE__) . '/../../../../../../tests/TestHelper.php';

class Centurion_Form_Model_Validator_AlreadyTakenTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Centurion_Form_Model_Validator_AlreadyTaken
     */
    public function testSimpleValid()
    {
        $simpleTable = new Asset_Model_DbTable_Simple();
        $simpleTable->all()->delete();

        $validator = new Centurion_Form_Model_Validator_AlreadyTaken($simpleTable, 'title');
        //No record in database. Should be ok
        $this->assertTrue($validator->isValid('test'));

        $simpleTable->insert(array('title' => 'test'));
        //Should be fail. We just add a record in DB
        $this->assertFalse($validator->isValid('test'));

        $simpleTable->delete('title = \'test\'');
        //Should be ok again: we remove the conflict record.
        $this->assertTrue($validator->isValid('test'));
    }

    /**
     * @covers Centurion_Form_Model_Validator_AlreadyTaken
     */
    public function testValidWithParam()
    {
        $simpleTable = new Asset_Model_DbTable_Simple();
        $simpleTable->all()->delete();


        $row = $simpleTable->createRow(array('id' => 1, 'title' => 'test'));
        $row->save();

        //This time we have a current record in DB, but we have filter by id > 1. So no conflict should be detected
        $validator = new Centurion_Form_Model_Validator_AlreadyTaken($simpleTable, 'title', array('id > 1'));
        $this->assertTrue($validator->isValid('test'));

        $simpleTable->insert(array('title' => 'test'));
        $this->assertFalse($validator->isValid('test'));

        $simpleTable->delete('title = \'test\'');
        $this->assertTrue($validator->isValid('test'));
    }
}
