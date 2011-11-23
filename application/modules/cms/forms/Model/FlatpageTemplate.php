<?php

class Cms_Form_Model_FlatpageTemplate extends Centurion_Form_Model_Abstract
{   
    public function __construct($options = array(), Centurion_Db_Table_Row_Abstract $instance = null)
    {
        $this->_model = Centurion_Db::getSingleton('cms/flatpage_template');
        
        $this->_exclude = array();
        
        $this->_elementLabels = array(
            'name'          => $this->_translate('Name'),
            'view_script'   => $this->_translate('View script'),
        );
        
        $this->setLegend($this->_translate('Edit flatpage template'));
        
        parent::__construct($options, $instance);
    }
}