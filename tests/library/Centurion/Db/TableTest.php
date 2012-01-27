<?php

require_once dirname(__FILE__) . '/../../../TestHelper.php';

class Centurion_Db_Table_TableTest extends PHPUnit_Framework_TestCase
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
}