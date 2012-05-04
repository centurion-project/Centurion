<?php

class Cms_AdminFlatpageController extends Centurion_Controller_CRUD implements Translation_Traits_Controller_CRUD_Interface
{
    public function init()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();

        $this->_model = Centurion_Db::getSingleton('cms/flatpage');

        $this->_formClassName = 'Cms_Form_Model_Flatpage';

        /*$this->setOptions(array(
            'titleColumn'        =>  'title',
            'publishColumn'      =>  'is_published',
            'publishDateColumn'  =>  'published_at'
        ));*/
        

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Manage flatpage@backoffice,cms'));
        $this->view->placeholder('headling_1_add_button')->set($this->view->translate('flatpage@backoffice,cms'));
        
        parent::init();
    }
    
    public function indexAction()
    {
        $this->_redirect($this->view->url(array('controller' => 'admin-navigation', 'module' => 'admin'), 'default', true));
        die();
    }
}