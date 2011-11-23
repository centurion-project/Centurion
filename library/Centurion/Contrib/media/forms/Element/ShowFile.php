<?php 

class Media_Form_Element_ShowFile extends Zend_Form_Element
{    
    public function render(Zend_View_Interface $view = null)
    {
        $file = Centurion_Db::getSingleton('media/file')->findOneById($this->_value);
        return parent::render($view)
               . '<img src="'.$file->getStaticUrl(array('resize' => array('maxWidth' => 100, 'maxHeight' => 100))).'" />';
    }
}