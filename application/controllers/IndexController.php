<?php

class IndexController extends Centurion_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }
    public function installationCompleteAction()
    {
        // action body
    }

    public function testAction()
    {
        $this->_helper->layout->setLayout('admin');

        $form = new Centurion_Form();

        $form->addElement('MapPicker', 'map');

        $form->addElement('submit', 'submit');

        if ($this->getRequest()->isPost()) {
            $form->isValid($this->getRequest()->getParams());
        }

        $this->view->form = $form;
    }
}

