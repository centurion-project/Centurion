<?php
class Centurion_Traits_Form_Abstract extends Centurion_Traits_Abstract
{
    protected $_form;
    
    public function __construct($form) 
    {
        parent::__construct($form);
        
        $this->_form = $form;
    }
}