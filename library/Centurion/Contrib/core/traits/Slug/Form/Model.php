<?php
class Core_Traits_Slug_Form_Model extends Centurion_Traits_Form_Abstract
{
    public function __construct($form)
    {
        parent::__construct($form);
        
        Centurion_Signal::factory('pre_generate')->connect(array($this, 'preGenerate'), $form);
        Centurion_Signal::factory('pre_save')->connect(array($this, 'preSave'), $form);
    }
    
    public function preGenerate()
    {
        if (isset($this->_form->showSlugField)) {
            $elementLabels = $this->_elementLabels;
            $elementLabels['slug'] = $this->_translate('Slug');
            $this->_elementLabels = $elementLabels;
        }
    }
    
    public function preSave()
    {
        $this->_form->enableElement('slug');
    }
}
