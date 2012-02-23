<?php

class Centurion_Form_Element_OnOff extends Zend_Form_Element_Select
{
    protected $_defaultOptions = array(
        'class' => 'field-switcher',
        'multiOptions' => array('0' => 'Off', '1' => 'On')
    );

    public function __construct($spec, $options = null)
    {
        $options = array_merge($options, $this->_defaultOptions);
        return parent::__construct($spec, $options);
    }
}