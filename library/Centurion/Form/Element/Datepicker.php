<?php

class Centurion_Form_Element_DatePicker extends Zend_Form_Element_Text
{
    protected $_defaultOptions = array(
        'class' => 'field-datepicker',
    );

    public function __construct($spec, $options = null)
    {
        $options = array_merge($this->_defaultOptions, $options);
        return parent::__construct($spec, $options);
    }

}