<?php

class Media_Form_Element_MultiFile extends Zend_Form_Element//Media_Form_Model_Admin_File
{
    protected $_validated = false;

    protected $_file = null;

    protected $_parentForm = null;

    public function init()
    {

        $this->_file = new Media_Form_Model_Admin_File(array('name' => $this->getName()));
        $this->setIsArray(true);
    }

    public function setParentform($form)
    {
        $this->_parentForm = $form;
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function render(Zend_View_Interface $view = null)
    {
        $realName = $this->getName();

        foreach ($this->_parentForm->getElements() as $key => $element) {
            if ($element === $this) {
                $realName = $key;
            }
        }

        $this->setDecorators(array(new Media_Form_Decorator_MultiFile()));
        $ticket = Centurion_Db::getSingleton('media/multiupload_ticket')->createTicket($this->_parentForm, $realName);
        $this->setAttrib('ticket', $ticket->ticket);

        return parent::render($view);
    }
}