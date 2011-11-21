<?php

class Cms_Form_Model_FlatpagePosition extends Centurion_Form_Model_Abstract
{   
    public function __construct($options = array(), Centurion_Db_Table_Row_Abstract $instance = null)
    {
        $this->_model = Centurion_Db::getSingleton('cms/flatpage_position');
        
        $this->_exclude = array();
        
        $this->_elementLabels = array(
            'name'     => $this->_translate('Name'),
            'key'      => $this->_translate('Key'),
        );
        
        $this->setLegend($this->_translate('Edit flatpage position'));
        
        parent::__construct($options, $instance);
    }
}