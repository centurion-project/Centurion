<?php

class Centurion_View_Helper_FormTextarea extends Zend_View_Helper_FormTextarea
{
    /**
     * Generates CUI validator
     *
     * @see Zend_View_Helper_FormTextarea::formTextarea()
     * @param mixed $value The element value.
     * @param array $attribs Attributes for the element tag.
     * @return string The element XHTML.
     */
    public function formTextarea($name, $value = null, $attribs = null)
    {
        $xhtml = parent::formTextarea($name, $value, $attribs);

        if (isset($this->view->form) && null !== $this->view->form->getElement($name)) {
            $validators = $this->view->form->getElement($name)->getValidators();
            
            foreach ($validators as $validator) {
                $class = get_class($validator);
                
                
                //TODO: add more validator
                if ($class == 'Zend_Validate_StringLength') {
                    $xhtml = '<script>$(function(){$("#'.$name.'").form(\'letterLimit\', {maxChar: '. $validator->getMax() .'});});</script>' . $xhtml;
                }
            }
        }
        
        return $xhtml;
    }
}