<?php

class Cms_AdminFlatpageTemplateController extends Centurion_Controller_CRUD
{
    public function init()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();
        
        $this->_formClassName = "Cms_Form_Model_FlatpageTemplate";
        $this->_displays = array(
            'name'          => $this->view->translate('Name@backoffice,cms'),
            'view_script'   => $this->view->translate('View script@backoffice,cms'),
        );
        
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Manage flatpage templates@backoffice,cms'));
        
        parent::init();
    }
}