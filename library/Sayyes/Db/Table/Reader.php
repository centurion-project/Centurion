<?php

class Sayyes_Db_Table_Reader extends Sayyes_Db_Abstract {

    protected $_dbName;
	public function __construct($dbName) {
		$this->_dbName = $dbName;
	}
	
	/**
	 * Return table list
	 * 
	 * @return array
	 */
	public function getTableList() {
		$db = $this->_getAdapter()->getConfig();
		$db['dbname'] = $this->_dbName;
		$adapter = new Zend_Db_Adapter_Mysqli($db);
		return $adapter->listTables();
	}
	
	/**
	 * Describe a table
	 * 
	 * @param string $tableName
	 * @param string $schemaName
	 * @param bool	$comments (default false)
	 * @return array
	 */
	public function describeTable($tableName, $comments = false) {
		$db = $this->_getAdapter()->getConfig();
		$db['dbname'] = $this->_dbName;
		$adapter = new Zend_Db_Adapter_Mysqli($db);
		$tableDescription = $adapter->describeTable($tableName, $this->_dbName);
		if(!$comments) {
			return $tableDescription;
		}
		else {
			$result = $adapter->fetchAll('SHOW FULL COLUMNS FROM `'.$tableName.'`');
			foreach ($result as $field) {
				if (preg_match("/(.*?INT|FLOAT|DOUBLE|REAL|DECIMAL)/i", $tableDescription[$field['Field']]['DATA_TYPE'])) {
					$tableDescription[$field['Field']]['DATA_TYPE'] = 'int';
				}
    			//elseif (preg_match("/(DATE.*?|TIME.*?|YEAR)/i", $tableDescription[$field['Field']]['DATA_TYPE']))
        			//$tableDescription[$field['Field']]['DATA_TYPE'] = 'Date';
    			elseif (preg_match("/(.*?CHAR|.*?BLOB|.*?TEXT|ENUM|SET)/i", $tableDescription[$field['Field']]['DATA_TYPE'])) {
    				$tableDescription[$field['Field']]['DATA_TYPE'] = 'string';
    			}
				$tableDescription[$field['Field']]['COMMENT'] = $field['Comment'];
			}
			return $tableDescription;
		}
		
	}
	
	/**
	 * Return dependent tables
	 * 
	 * @param string $tableName
	 * @return array
	 */
	public function getDependentTables($tableName, $primaryKeys) {
		$db = $this->_getAdapter();
        $dependentTables = array();
        foreach($primaryKeys as $key)
        {
            $select = $db->select();
            $select->from('information_schema.KEY_COLUMN_USAGE', array('TABLE_NAME'))
                    ->where('REFERENCED_TABLE_NAME = ?', $tableName)
                    ->where('REFERENCED_COLUMN_NAME = ?', $key)
                    ->where('TABLE_SCHEMA = ?', $this->_dbName);
            $dependentTables = array_merge($db->fetchAll($select), $dependentTables);
        }
        return $dependentTables;
	}

    public function getParentTables($tableName)
    {
        $db = $this->_getAdapter();
        $select = $db->select();
        $select->from('information_schema.KEY_COLUMN_USAGE', array('REFERENCED_TABLE_NAME'))
                    ->where('TABLE_NAME = ?', $tableName)
                    ->where('CONSTRAINT_NAME != ?','PRIMARY')
                    ->where('TABLE_SCHEMA = ?', $this->_dbName);
        return $db->fetchAll($select);
    }
	
	/**
	 * Return reference map
	 * 
	 * @param string $tableName
	 * @return array
	 */
	public function getReferenceMap($tableName) {
		$db = $this->_getAdapter();
        $select = $db->select();
        $select->from('information_schema.KEY_COLUMN_USAGE', array('CONSTRAINT_NAME', 'REFERENCED_TABLE_NAME',
        															'COLUMN_NAME', 'REFERENCED_COLUMN_NAME'))
        		->where('TABLE_NAME = ?', $tableName)
        		->where('CONSTRAINT_NAME != ?','PRIMARY')
                ->where('CONSTRAINT_NAME NOT LIKE "%_UNIQUE"')
        		->where('TABLE_SCHEMA = ?', $this->_dbName);
		return $db->fetchAll($select);
	}
	
	/**
	 * Return primary key(s)
	 * 
	 * @param string $tableName
	 * @return array
	 */
	public function getPrimaryKeys($tableName) {
		$db = $this->_getAdapter();
        $select = $db->select();
        $select->from('information_schema.KEY_COLUMN_USAGE', array('COLUMN_NAME'))
        		->where('TABLE_NAME = ?', $tableName)
        		->where('CONSTRAINT_NAME = ?','PRIMARY')
        		->where('TABLE_SCHEMA = ?', $this->_dbName);
		return  $db->fetchCol($select);
	}

    public function getForeignKeys($tableName)
    {
        $db = $this->_getAdapter();
        $select = $db->select();
        $select->from('information_schema.KEY_COLUMN_USAGE', array('COLUMN_NAME', 'REFERENCED_TABLE_NAME'))
        		->where('TABLE_NAME = ?', $tableName)
        		->where('CONSTRAINT_NAME != ?','PRIMARY')
                ->where('CONSTRAINT_NAME NOT LIKE "%_UNIQUE"')
        		->where('TABLE_SCHEMA = ?', $this->_dbName);
		return  $db->fetchPairs($select);
    }

}