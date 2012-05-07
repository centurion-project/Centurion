<?php

class Asset_Db_Table_Select extends Centurion_Db_Table_Select
{
    /**
     * Proxy function to test _isAlreadyJoined
     *
     * @param unknown_type $tableName
     * @param unknown_type $joinCond
     * @return boolean
     */
    public function isAlreadyJoined($tableName, $joinCond = null) {
        return $this->_isAlreadyJoined($tableName, $joinCond);
    }

    /**
     * Proxy function to test _isConditionEquals
     */
    public function isConditionEquals($cond1, $cond2)
    {
        return $this->_isConditionEquals($cond1, $cond2);
    }
}
