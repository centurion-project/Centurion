<?php
/**
 * Created by JetBrains PhpStorm.
 * User: david
 * Date: 06/12/10
 * Time: 23:16
 * To change this template use File | Settings | File Templates.
 */

class Sayyes_Db_Table extends Sayyes_Db_Abstract
{
    protected $_name;

    protected $_fields;

    protected $_primaryKeys;

    protected $_foreignKeys;

    protected $_keyOfManyToMany;

    protected $_parentTables;

    protected $_dependentTables;

    protected $_referenceMap;

    protected $_manyToMany;

    protected $_isJoin;

    public function getModuleName()
    {
    	$tab = explode('_', $this->_name);
    	return $tab[0];
    }
    
    public function getClassName()
    {
    	return substr(strstr($this->_name, '_'), 1);
    }
    
    public function getCapitalName()
    {
        return $this->_getCapital($this->getClassName());
    }

    public function setDependentTables($dependentTables)
    {
        $this->_dependentTables = $dependentTables;
    }

    public function getDependentTables()
    {
        return $this->_dependentTables;
    }

    public function setManyToMany($manyToMany)
    {
        $this->_manyToMany = $manyToMany;
    }

    public function getManyToMany()
    {
        return $this->_manyToMany;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setPrimaryKeys($primaryKeys)
    {
        $this->_primaryKeys = $primaryKeys;
    }

    public function getPrimaryKeys()
    {
        return $this->_primaryKeys;
    }

    public function setReferenceMap($referenceMap)
    {
        $this->_referenceMap = $referenceMap;
    }

    public function getReferenceMap()
    {
        return $this->_referenceMap;
    }

    public function setFields($fields)
    {
        $this->_fields = $fields;
    }

    public function getFields()
    {
        return $this->_fields;
    }

    public function setParentTables($parentTables)
    {
        $this->_parentTables = $parentTables;
    }

    public function getParentTables()
    {
        return $this->_parentTables;
    }

    public function setForeignKeys($foreignKeys)
    {
        $this->_foreignKeys = $foreignKeys;
    }

    public function getForeignKeys()
    {
        return $this->_foreignKeys;
    }

    public function setIsJoin($isJoin)
    {
        $this->_isJoin = $isJoin;
    }

    public function getIsJoin()
    {
        return $this->_isJoin;
    }

    public function pushManyToMany($refName, $ref)
    {
        if(!is_array($this->_manyToMany))
        {
            $this->_manyToMany = array();
        }
        $this->_manyToMany[$refName] = $ref;
    }

    public function setKeyOfManyToMany($keyOfManyToMany)
    {
        $this->_keyOfManyToMany = $keyOfManyToMany;
    }

    public function getKeyOfManyToMany()
    {
        return $this->_keyOfManyToMany;
    }

    /**
	 * Check if the table is a join table
	 *
     * @return bool
	 */
	public function isJoinTable() {
        if($this->_isJoin !== null)
        {
            return $this->_isJoin;
        }
        if(count($this->_fields) > 1 && count($this->_primaryKeys) > 1 && count($this->_foreignKeys))
        {
            foreach(array_keys($this->_foreignKeys) as $foreignKey)
            {
                if(!in_array($foreignKey, $this->_primaryKeys))
                {
                    return false;
                }
            }
        }
        else {
            return false;
        }
        
        $this->_keyOfManyToMany = array();
        $keys = array_keys($this->_foreignKeys);
        $values = array_values($this->_foreignKeys);
        $this->_getCapital($values[1]);
        
        list($refTableClassModule, $refTableClassModel) = $this->separate($values[1]);
        list($interTableClassModule, $interTableClassModel) = $this->separate($this->_name);
        $this->_keyOfManyToMany[$values[0]] = array('ref' => substr(strstr($values[1], '_'), 1) . 's',
                                                    'data' => array(
                                                                'refTableClass' => ucfirst($refTableClassModule) . '_Model_DbTable_' . $this->_getCapital($refTableClassModel),
                                                                'intersectionTable' => ucfirst($interTableClassModule) . '_Model_DbTable_' . $this->_getCapital($interTableClassModel),
                                                                'columns' => array('local' => $keys[0], 'foreign' => $keys[1])));
        
        list($refTableClassModule, $refTableClassModel) = $this->separate($values[0]);
        $this->_keyOfManyToMany[$values[1]] = array('ref' =>  substr(strstr(    $values[0], '_'), 1) . 's',
                                                    'data' => array(
                                                                'refTableClass' => $refTableClassModule . '_Model_DbTable_' . $this->_getCapital($refTableClassModel),
                                                                'intersectionTable' => ucfirst($interTableClassModule) . '_Model_DbTable_' . $this->_getCapital($interTableClassModel),
                                                                'columns' => array('local' => $keys[1], 'foreign' => $keys[0])));
		return true;
	}
}
