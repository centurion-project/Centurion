<?php
abstract class Centurion_Traits_Model_DbTable_Select_Abstract extends Centurion_Traits_Abstract
{
    /**
     * @var Centurion_Db_Table_Select
     */
    protected $_select;
        
    public function __construct($select) 
    {
        parent::__construct($select);
        
        $this->_select = $select;        
    }
}