<?php
class Core_Traits_Slug_Form_Model extends Centurion_Traits_Form_Abstract
{
    
    public function __construct($form)
    {
        parent::__construct($form);
        
        Centurion_Signal::factory('pre_generate')->connect(array($this, 'preGenerate'), $form);
        //To set as no-mandatory the slug fields when it is required in table (field not null)
        Centurion_Signal::factory('post_generate')->connect(array($this, 'postGenerate'), $form);
        Centurion_Signal::factory('pre_save')->connect(array($this, 'preSave'), $form);
    }

    /**
     * Add the field slug in the form is the form has the attribute showSlugField set at true
     */
    public function preGenerate()
    {
        if (isset($this->_form->showSlugField)) {
            $elementLabels = $this->_elementLabels;
            $elementLabels['slug'] = 'Slug';
            $this->_elementLabels = $elementLabels;
        }
    }

    /**
     * Called to set the field slug as no-mandatory
     */
    public function postGenerate(){
        if (isset($this->_form->showSlugField)) {
            //It is automaticaly generated if it is not exist...
            if($slug = $this->_form->getElement('slug'))
                $slug->setRequired(false);
        }
    }

    public function preSave()
    {
        $this->_form->enableElement('slug');
    }
}