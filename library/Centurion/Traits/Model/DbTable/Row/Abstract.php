<?php
abstract class Centurion_Traits_Model_DbTable_Row_Abstract extends Centurion_Traits_Abstract
{
    protected $_row;
    
    protected $_requiredColumns = array();
    
    public function __construct($row) 
    {
        parent::__construct($row);
        
        $this->_row = $row;
    }
    
    public function init()
    {
//        $tableTraitClass = substr(get_class($this), 0, strpos(get_class($this), '_Row'));
//                
//        if ($this->getTable() instanceof $tableTraitClass)
//            $this->getTable()->checkForRequiredColumn($this->_requiredColumns);        
    }
}