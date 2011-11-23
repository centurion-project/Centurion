<?php

class Auth_AdminGroupController extends Centurion_Controller_CRUD
{
    public function init()
    {
        $this->_formClassName = 'Auth_Form_Model_Group';

        $this->_displays = array(
            'id'                =>  'ID',
            'name'      =>  array(
                                        'type'  => self::COL_TYPE_FIRSTCOL,
                                        'label' => $this->view->translate('Name'),
                                        'param' => array(
                                                        'title' => 'name',
                                                        'cover' => null,
                                                        'subtitle' => null,
                                                    ),
                                    ),
            'description'       =>  'Description',
            'left|parent_group__name'   =>  $this->view->translate('Parent group')
        );

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Manage groups'));

        parent::init();
    }

    public function preDispatch()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();
        $this->_helper->layout->setLayout('admin');
        parent::preDispatch();
    }
}
