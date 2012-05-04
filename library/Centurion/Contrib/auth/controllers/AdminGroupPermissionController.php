<?php

class Auth_AdminGroupPermissionController extends Centurion_Controller_Action
{
    public function preDispatch()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();
        $this->_helper->layout->setLayout('admin');
        parent::init();
    }

    public function indexAction()
    {
        $matrice = array();
        $values = array();
        $groups = array();
        $permissions = array();

        foreach (Centurion_Db::getSingleton('auth/group')->fetchAll() as $groupRow) {
            $values[$groupRow->id] = 0;
            $groups[$groupRow->id] = $groupRow->name;
        }

        foreach (Centurion_Db::getSingleton('auth/permission')->fetchAll(null, 'name asc') as $permissionRow) {
            $matrice[$permissionRow->id] = $values;
            $permissions[$permissionRow->id] = $permissionRow;
        }

        foreach (Centurion_Db::getSingleton('auth/group_permission')->fetchAll() as $groupPermissionRow) {
            $matrice[$groupPermissionRow->permission_id][$groupPermissionRow->group_id] = 1;
        }

        $action = $this->getHelper('url')->simple('switch', 'admin-group-permission', 'auth');

        foreach ($matrice as $permissionId => &$row) {
            foreach ($row as $groupId => &$col) {
                $col = array('group_id'      => $groupId,
                             'permission_id' => $permissionId,
                             'value'         => $col);
            }
        }

        $result = array(
            'sub'       => array(),
            'tab'       => array(),
            'name = ?'  => ''
        );

        foreach ($permissions as $permissionKey => $permission) {
            $tab = explode('_', $permission->name);
            $pt = &$result;
            $length = count($tab) - 1;
            for ($i = 0; $i < $length; $i ++) {
                if (!isset($pt['sub'][$tab[$i]])) {
                    $name = implode('_', array_slice($tab, 0, $i + 1, true));
                    $pt['sub'][$tab[$i]] = array('sub' => array() , 'tab' => array() , 'name' => $name);
                }
                $pt = &$pt['sub'][$tab[$i]];
            }
            $pt['tab'][$permissionKey] = $matrice[$permissionKey];
        }

        $this->view->permissions = $permissions;
        $this->view->groups = $groups;
        $this->view->result = $result;

        $form = new Auth_Form_AutoSwitch();
        $form->setAction($action);

        $this->view->form = $form;
    }

    public function switchAction ()
    {
        $groupId = $this->_request->getPost('group_id');
        $permissionId = $this->_request->getPost('permission_id');
        $value = $this->_request->getParam('act');

        $form = new Auth_Form_AutoSwitch($groupId, $permissionId);

        if ($form->isValid($this->_request->getPost())) {
            $form->save();
        }

        if (!$this->_request->isXmlHttpRequest()) {
            $this->getHelper('redirector')->gotoSimple('index', 'grouppermission', 'auth');
        } else {
        	$this->_helper->json(array('message' => 'ok'));
        }
    }

}
