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

    /**
     * This action list all admin controller in all active modules.
     * It can help to build navigation.
     */
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
