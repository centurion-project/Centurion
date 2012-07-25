<?php

class Asset_Model_DbTable_Row_SimpleWithSpecialGet extends Centurion_Db_Table_Row
{
    
    public function __construct(array $config) {
        $this->_specialGets['method'] = 'specialGetMethod';
        $this->_specialGets['arrayGet'] = array($this, 'specialGetMethod');
        parent::__construct($config);
    }
    public function specialGetMethod()
    {
        return 'success';
    }
}

