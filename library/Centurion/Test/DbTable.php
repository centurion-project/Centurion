<?php

abstract class Centurion_Test_DbTable extends Zend_Test_PHPUnit_DatabaseTestCase 
{
    
    protected $_tableName = null;
    protected $_columns = array();
    protected $_connectionMock;
    
    public function setTable($tableName)
    {
        $this->_tableName = $tableName;
    }
    
    public function addColumn($column)
    {
        $this->_columns[] = $column;
    }
    
    public function addColumns(array $columns)
    {
        foreach ($columns as $column) {
            $this->addColumn($column);
        }
    }
    
    public function setColumns($columns)
    {
        $this->resetColumns();
        $this->addColumns($columns);
    }
    
    public function resetColumns()
    {
        $this->_columns = array();
    }
    
    /**
     * @test
     */
    public function testTable()
    {
        try {
            $table = Centurion_Db::getSingleton($this->_tableName);
        } catch (Centurion_Db_Exception $e) {
            return $this->fail(sprintf('Model %s doesn\'t not exists', $this->_tableName));
        }
        
        //TODO: check if table exists in DB 
        
        if (!class_exists($table->getRowClass()))
            return $this->fail(sprintf('Row class for %s doesn\'t not exists', $this->_tableName));
    }
    
    /**
     * @test 
     * @depends testTable
     */
    public function testColumns()
    {
        $table = Centurion_Db::getSingleton($this->_tableName);
            
        foreach ($this->_columns as $column) {
            $this->assertTrue($table->hasColumn($column), sprintf('Table %s  don\'t have the columns %s', $this->_tableName, $column));
        }
        
        foreach ($table->info('cols') as $column) {
            if (!in_array($column, $this->_columns)) {
                $this->fail(sprintf('Warning !!! Column %s haven\'t been tested', $column));
            }
            
            if (0 !== strcmp($column, mb_strtolower($column))) {
                $this->fail(sprintf('You can not use uper letter in column name : %s in %s', $column, $this->_tableName));
            }
        }
    }
    
    /**
     * @test
     * @depends testColumns
     */
    public function testIndex()
    {
        $table = Centurion_Db::getSingleton($this->_tableName);
        
        foreach ($this->_columns as $column) {
            if (
                0 === strncmp('is_', $column, 3) ||
                0 === strncmp('has_', $column, 4) ||
                0 === strncmp('can_be_', $column, 6) ||
                substr($column, -3) == '_id'
                ) {
                    $this->assertTrue($table->isIndex($column), sprintf('Column %s is not an index and it should be!', $column));
            }
        }
    }
    
    public function testForeignKey()
    {
        $table = Centurion_Db::getSingleton($this->_tableName);
        
    }
 
    /**
     * By default, return an empty dataset
     * @see PHPUnit_Extensions_Database_TestCase::getDataSet()
     */
    protected function getDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
    }
    
    protected function getConnection()
    {
        if($this->_connectionMock == null) {
            $connection = Zend_Db_Table::getDefaultAdapter();
            $this->_connectionMock = $this->createZendDbConnection(
                $connection, 'zfunittests'
            );
            Zend_Db_Table_Abstract::setDefaultAdapter($connection);
        }
        return $this->_connectionMock;
    }
}
