<?php

class Media_Form_Model_Admin_MultiFile extends Media_Form_Model_Admin_File
{
    protected $_belong;
    
    public function init()
    {
        parent::init();
        
        $this->filename->setIsArray(true)
                       ->setAttrib('class', 'multi')
                       ->addValidator('Count', false, array('min' => 1, 'max' => 3));
    }
    
    public function setBelong(Centurion_Db_Table_Row_Abstract $row)
    {
        $this->_belong = $row;
        
        return $this;
    }
    
    public function getBelong()
    {
        return $this->_belong;
    }
    
    public function saveObject($values = null)
    {
        
    }
}