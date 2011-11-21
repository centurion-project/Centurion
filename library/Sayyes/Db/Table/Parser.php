<?php

class Sayyes_Db_Table_Parser extends Sayyes_Db_Abstract {
	
	/**
	 * Return parsed string of dependent tables
	 * 
	 * @param string $tableName
	 * @param array $dependentTables
	 * @param string $namespace
	 * @return array
	 */
	public function getParsedDependentTables($tableName, $dependentTables, $namespace) {
		$tables = array();
		
		foreach ($dependentTables as $key => $table) {
			list($namespace, $className) = $this->separate($table['TABLE_NAME']);
			
			$tables[substr(strstr($table['TABLE_NAME'], '_'), 1) . 's'] = $namespace . '_Model_DbTable_' . $this->_getCapital($className);
		}
		return $tables;
	}
	
	/**
	 * Return parsed reference map
	 * 
	 * @param stirng $tableName
	 * @param array $referenceMap
	 * @param string $namespace
	 * @return array
	 */
	public function getParsedReferenceMap($tableName, $referenceMap, $namespace) {
	    $references = array();
	    foreach ($referenceMap as $key => $ref) {
	    	$refName = substr($ref['COLUMN_NAME'], 0, strlen($ref['COLUMN_NAME']) - 3);
	    	if(isset($references[$refName])) {
	    		$refName .= mt_rand(0,100); 
	    	} 
	    	
	    	list($namespace, $className) = $this->separate($ref['REFERENCED_TABLE_NAME']);
	    	
	    	$references[$refName] = array(
                       	'columns' 			=> $ref['COLUMN_NAME'],
                        'refColumns'		=>  $ref['REFERENCED_COLUMN_NAME'],
                       	'refTableClass'		=> $namespace . '_Model_DbTable_' . $className,
                       	);
	    }
	    return (count($references) >= 1) ? $references : null;
	}
	
	/**
	 * Return parsed parent tables
	 * 
	 * @param array $referenceMap
	 */
	public function getParsedParentTable($referenceMap) {
		$parentTables = array();
		foreach ($referenceMap as $key => $ref) {
			$parentTables[$ref['COLUMN_NAME']] = $ref['REFERENCED_TABLE_NAME'];
		}
		return $parentTables;
	}
	
	/**
	 * Check if the table is a join table
	 * 
	 * @param string $name
	 * @param array $fields
	 * @param array $parentTables
	 */
	public function isJoinTable($name, $fields, $parentTables) {
		$isJoin = true;
		if (count($fields) == 2 && 
			isset($parentTables[$fields[0]['COLUMN_NAME']]) &&
			isset($parentTables[$fields[1]['COLUMN_NAME']])) {
				return true;
		}
		else {
			foreach ($fields as $field) {
				if($field['PRIMARY'] && !isset($parentTables[$field['COLUMN_NAME']])) {
					$isJoin = false;
				}
			}
		}
		return $isJoin;
	}
}