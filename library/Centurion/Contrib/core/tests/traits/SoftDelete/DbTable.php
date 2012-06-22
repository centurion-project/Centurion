<?php

require_once dirname(__FILE__) . '/../../../../../../../tests/TestHelper.php';

class Core_Test_Traits_SoftDelete_DbTable extends PHPUnit_Framework_TestCase
{
    public function testTrait()
    {
        $simpleTable = new Asset_Model_DbTable_Simple();

        //We make sure that we have nothing in the table
        $this->assertCount(0, $simpleTable);

        //We ensure that filter status is at true
        Centurion_Db_Table_Abstract::setFiltersStatus(true);

        //We create a row
        $row = $simpleTable->createRow();
        $row->save();

        //it's in the table
        $this->assertCount(1, $simpleTable);

        //We delete it
        $row->delete();

        //It should disappear from table
        $this->assertCount(0, $simpleTable);

        //Even if we set filter to false
        Centurion_Db_Table_Abstract::setFiltersStatus(false);
        $this->assertCount(0, $simpleTable);

        Centurion_Db_Table_Abstract::setFiltersStatus(true);

        //We make a injection to add trait to the table
        Centurion_Traits_Common::addTraits($simpleTable, 'Core_Traits_SoftDelete_Model_DbTable_Interface');

        //We create a new row
        $row = $simpleTable->createRow();
        $row->save();

        //It's in the table
        $this->assertCount(1, $simpleTable);

        //We remove it
        $row->delete();

        //The row is no more visible in table
        $this->assertCount(0, $simpleTable);

        //But this time it must be still here, but filtered by the trait.
        Centurion_Db_Table_Abstract::setFiltersStatus(false);
        $this->assertCount(1, $simpleTable);
    }
}
