<?php

class Auth_AdminPermissionController extends Centurion_Controller_CRUD
{
    public function preDispatch()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();
        $this->_helper->layout->setLayout('admin');
        parent::preDispatch();
    }

    public function init()
    {
        $this->_formClassName = 'Auth_Form_Model_Permission';

        $this->_displays = array(
            'name'      =>  array(
                                    'type'  => self::COL_TYPE_FIRSTCOL,
                                    'label' => $this->view->translate('Name'),
                                    'param' => array(
                                                    'title' => 'name',
                                                    'cover' => null,
                                                    'subtitle' => null,
                                                ),
                                ),
            'description'   =>  'Description'
        );

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Manage permissions'));

        parent::init();
    }
}

