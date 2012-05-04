<?php

class Auth_AdminUserController extends Centurion_Controller_CRUD
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
        $this->_formClassName = 'Auth_Form_Model_User';

        $this->_displays = array(
            'username'      =>  array(
                                    'type'  => self::COL_TYPE_FIRSTCOL,
                                    'label' => $this->view->translate('Username'),
                                    'param' => array(
                                                    'title' => 'username',
                                                    'cover' => null,
                                                    'subtitle' => null,
                                                ),
                                ),
            'created_at'    =>  array('filters' => self::COL_DISPLAY_DATE, 'label' => $this->view->translate('Created at')),
            'last_login'    =>  array('filters' => self::COL_DISPLAY_DATE, 'label' => $this->view->translate('Last login')),
            'switch'    => array(
                            'type'   => self::COL_TYPE_ONOFF,
                            'column' => 'is_active',
                            'label' => $this->view->translate('Is active'),
                            'onoffLabel' => array($this->view->translate('Active'), $this->view->translate('Not active')),
                        ),
        );

        $this->_filters = array(
            'username'      =>  array('type'    =>  self::FILTER_TYPE_TEXT,
                                      'label'   =>  $this->view->translate('Username')),
            'is_active'     =>  array('type'    =>  self::FILTER_TYPE_RADIO,
                                      'label'   =>  $this->view->translate('Status'),
                                      'data'    =>  array(1 => $this->view->translate('Yes'),
                                                          0 => $this->view->translate('No'))));

        $this->_toolbarActions['Activate'] = $this->view->translate('Activate');
        $this->_toolbarActions['Desactivate'] = $this->view->translate('Desactivate');

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Manage users'));

        parent::init();
    }

    public function activateAction($rowset = null)
    {
        if (null===$rowset) {
            return;
        }

        foreach ($rowset as $key => $row) {
            $row->is_active = 1;
            $row->save();
        }

        $this->_cleanCache();
        $this->getHelper('redirector')->gotoRoute(array_merge(array(
            'controller' => $this->_request->getControllerName(),
            'module'     => $this->_request->getModuleName(),
            'action'         => 'index'
        ), $this->_extraParam), null, true);
    }

    public function desactivateAction($rowset = null)
    {
        if (null===$rowset) {
            return;
        }

        foreach ($rowset as $key => $row) {
            $row->is_active = 0;
            $row->save();
        }

        $this->_cleanCache();
        $this->getHelper('redirector')->gotoRoute(array_merge(array(
            'controller' => $this->_request->getControllerName(),
            'module'     => $this->_request->getModuleName(),
            'action'         => 'index'
        ), $this->_extraParam), null, true);
    }

    public function deleteAction($rowset = null)
    {
        if ($this->_getParam('id', 0) === Centurion_Auth::getInstance()->getIdentity()->id) {
            Zend_Controller_Action_HelperBroker::getStaticHelper('redirector')
                ->gotoSimple('unauthorized', 'error', 'admin');
        } else {
            parent::deleteAction($rowset);
        }
    }
}

