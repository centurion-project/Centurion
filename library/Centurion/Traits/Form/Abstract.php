<?php
class Centurion_Traits_Form_Abstract extends Centurion_Traits_Abstract
{
    /**
     * @var Centurion_Form_Model
     */
    protected $_form;
    

    /**
     * @param Centurion_Form $form
     */
    public function __construct($form) 
    {
        parent::__construct($form);
        
        $this->_form = $form;
    }
}
