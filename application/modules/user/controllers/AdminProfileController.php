<?php

class User_AdminProfileController extends Centurion_Controller_CRUD
{
    public function init()
    {
        $this->_formClassName = 'User_Form_Model_AdminProfile';

        $this->_displays = array(
            'nickname'         =>  $this->view->translate('Nickname'),
            'user__username'   =>  $this->view->translate('Login'),
            'created_at'       =>  $this->view->translate('Created at'),
            'user__last_login' =>  $this->view->translate('Last login'),
        );

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Manage users'));
        $this->view->placeholder('headling_1_add_button')->set($this->view->translate('user'));

        parent::init();
    }

    public function preDispatch()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();
    }
}