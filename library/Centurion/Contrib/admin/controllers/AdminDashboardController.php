<?php
class Admin_AdminDashboardController extends Centurion_Controller_Action
{
   public function preDispatch()
   {
       $this->_helper->authCheck();
       $this->_helper->aclCheck();
       $this->_helper->layout->setLayout('admin');

       parent::preDispatch();
   }
   
   public function dashboardAction()
   {
       
   }

    public function listAdminAction()
    {
        $front = $this->getFrontController();
        $modules = $front->getControllerDirectory();

        $moduleEnabled = Centurion_Config_Manager::get('resources.modules');

        $this->view->modules = array();

        foreach ($modules as $moduleName => $module) {
            $this->view->modules[$moduleName] = array();
            if (!in_array($moduleName, $moduleEnabled)) {
                continue;
            }

            $dbTableDir = realpath($module);
            if (!file_exists($dbTableDir)) {
                continue;
            }

            $dir = new Centurion_Iterator_DbTableFilter($dbTableDir);

            foreach ($dir as $fileInfo) {
                if (substr($fileInfo, 0, 5) == 'Admin') {
                    if (substr($fileInfo, -14) == 'Controller.php') {
                        $controllerName = Centurion_Inflector::tableize(substr($fileInfo, 0, -14), '-');
                        $this->view->modules[$moduleName][] = $controllerName;
                    }
                }
            }
        }

        ksort($this->view->modules);
    }
}
