<?php
/**
 * Centurion
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@centurion-project.org so we can send you a copy immediately.
 *
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Admin
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Admin
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@octaveoctave.com>
 */
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
