<?php

class Admin_LoginController extends Centurion_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->layout->setLayout('adminlogin');
        
        $this->_redirectIfAuthenticated();
        
        $form = new Auth_Form_Login(array(
            'dbAdapter'         =>  Zend_Db_Table_Abstract::getDefaultAdapter(),
            'tableName'         =>  'auth_user',
            'loginColumn'       =>  'username',
            'passwordColumn'    =>  'password',
            'authAdapter'       =>  'Centurion_Auth_Adapter_DbTable',
            'checkColumn'       =>  'is_active = 1',
        ));
        
        if (null !== $this->_getParam('next', null)) {
            $form->getElement('next')->setValue($this->_getParam('next', null));
        }
        
        if ($this->getRequest()->isPost()) {
            $posts = $this->getRequest()->getParams();
            if ($form->isValid($posts)) {
                $userRow = Centurion_Auth::getInstance()->getIdentity();
                $userRow->last_login = date('Y-m-d h:i:s');
                $userRow->save();
                
                $this->_redirectIfAuthenticated();
            } else {
                $form->populate($posts);
            }
        }
        
        $this->view->form = $form;
    }
    
    private function _redirectIfAuthenticated()
    {
        if (Centurion_Auth::getInstance()->hasIdentity()) {
            if ($this->_hasParam('next') && '' != $this->_getParam('next')) {
                $this->getHelper('redirector')->gotoUrlAndExit($this->_getParam('next'));
            } else {
                $this->getHelper('redirector')->gotoUrlAndExit('/');
            }
        }
    }
    
    public function logoutAction()
    {
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Session::destroy();
        
        $this->getHelper('redirector')->gotoSimple('index', 'index', 'admin');
    }
}