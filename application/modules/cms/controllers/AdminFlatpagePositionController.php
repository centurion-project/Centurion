<?php

class Cms_AdminFlatpagePositionController extends Centurion_Controller_CRUD
{
    public function init()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();
        
        $this->_formClassName = "Cms_Form_Model_FlatpagePosition";
        $this->_displays = array(
            'name'          => $this->view->translate('Name@backoffice,cms'),
            'key'           => $this->view->translate('Key@backoffice,cms'),
        );
        
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Manage flatpage positions@backoffice,cms'));
        
        parent::init();
    }
}