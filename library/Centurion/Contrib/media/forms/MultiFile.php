<?php

class Media_Form_MultiFile extends Media_Form_Model_Admin_File
{
    protected $_validated = false;

    public function init()
    {
        $this->addDecorator(new Media_Form_Decorator_MultiFile());

        parent::init();
    }

    public function render(Zend_View_Interface $view = null)
    {

        $ticket = Centurion_Db::getSingleton('media/multiupload_ticket')->createTicket($this);
        $this->setAttrib('ticket', $ticket->ticket);

        return parent::render($view);
    }

    public function save($adapter = null)
    {
        return parent::save($adapter);
    }

    public function isValid($data)
    {
        $this->_validated = true;

        if (is_string($data)){
            return true;
        }

        if (is_array($data) && count($data)) {
           $this->_values = $data;

           return true;
        }

        return parent::isValid($data);
    }

    public function getValues($suppressArrayNotation = false)
    {
        if ($this->_validated) {
            if (null !== $this->_values)
                return $this->_values;

            return parent::getValues($suppressArrayNotation);
        }
    }
}