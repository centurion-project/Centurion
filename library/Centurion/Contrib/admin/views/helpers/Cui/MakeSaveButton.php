<?php

class Centurion_View_Helper_Cui_MakeSaveButton extends Zend_View_Helper_Abstract
{
    public function Cui_MakeSaveButton(Centurion_Form $form)
    {
        $label = 'Save';
        
        if (null !== $form->getTranslator())
            $label = $form->getTranslator()->_($label);
        $form->addElement('button', '_saveBig', array('type' => 'submit', 'label' => $label));
        
        if (null === $form->getDisplayGroup('_header')) {
            $this->view->getHelper('GridForm')->makeHeader($form, array('_saveBig'));
        } else {
            $form->addInDisplayGroup('_saveBig', '_header');
        }
        
        $save = $form->getElement('_saveBig');
        
        $save->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'ui-button-big'));
        $save->setAttrib('class', 'ui-button ui-button-bg-white-gradient ui-button-text-red ui-button-text-only');
        $save->setAttrib('class1', null);
        $save->setAttrib('class2', 'ui-button-text');
        $save->setAttrib('role', 'submit');
        
        return $form;
    }
}