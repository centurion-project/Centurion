<?php

class Auth_AdminScriptPermissionController extends Centurion_Controller_Action
{
    public function preDispatch()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();
        $this->_helper->layout->setLayout('admin');
        parent::preDispatch();
    }
    
    public function indexAction()
    {
        $form = new Centurion_Form();
        
        $form->addElement('text', 'module_name', array('required' => true, 'label' => $this->view->translate('Module:')));
        $form->addElement('text', 'controller_name', array('required' => true, 'label' => $this->view->translate('Controller:')));
        $form->addElement('text', 'ressource_name', array('required' => true, 'label' => $this->view->translate('Ressource:')));
        
        $form->addElement('submit', 'submit', array('label' => $this->view->translate('Submit')));
        
        $post = $this->_request->getParams();
        
        if ($form->isValid($post)) {
            $this->view->message = 'Les permission ont bien été ajoutées. <br />';
            
            $permissionTable = Centurion_Db::getSingleton('auth/permission');
            
            $tab = array(
                'index' => 'View %s %s index',
                'list' => 'View %s %s list',
                'get' => 'View an %s %s',
                'post' => 'Create an %s %s',
                'new' => 'Access to creation of an %s %s',
                'delete' => 'Delete an %s %s',
                'put' => 'Update an %s %s',
                'batch' => 'Batch an %s %s',
                'switch' => 'Switch an %s %s',
            );
            
            foreach ($tab as $key => $description) {
                list($row, $created) = $permissionTable->getOrCreate(array('name' => $post['module_name'] . '_' . $post['controller_name'] . '_' . $key));
                
                if ($created) {
                    $row->description = sprintf($description, $post['module_name'], $post['ressource_name']);
                    $row->save();
                }
            }
            
            /*
                INSERT INTO \`auth_permission\` (\`name\`,\`description\`)
                VALUES
                ('${1:module}_${2:controller}_index', 'View ${1:module} ${3:resource} index'),
                ('${1:module}_${2:controller}_list', 'View ${1:module} ${3:resource} list'),
                ('${1:module}_${2:controller}_get', 'View an ${1:module} ${3:resource}'),
                ('${1:module}_${2:controller}_post', 'Create an ${1:module} ${3:resource}'),
                ('${1:module}_${2:controller}_new', 'Access to creation of an ${1:module} ${3:resource}'),
                ('${1:module}_${2:controller}_delete', 'Delete an ${1:module} ${3:resource}'),
                ('${1:module}_${2:controller}_put', 'Update an ${1:module} ${3:resource}');
            */
        }
        
        $this->view->form = $form;
    }
}