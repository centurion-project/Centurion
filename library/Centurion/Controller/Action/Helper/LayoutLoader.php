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
 * @package     Centurion_Controller
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Controller
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Controller_Action_Helper_LayoutLoader extends Zend_Controller_Action_Helper_Abstract
{
    public function preDispatch()
    {
        $bootstrap = $this->getActionController()
                          ->getInvokeArg('bootstrap');
        $config = $bootstrap->getOptions();
        $module = $this->getRequest()->getModuleName();
        $controller = $this->getRequest()->getControllerName();
        $layoutScript = null;

        if (isset($config['resources']['layout']['admin']['layout'])
            && isset($config['admin']['controllers'])
            && in_array($controller, $config['admin']['controllers'])
        ) {
            $layoutScript = $config['resources']['layout']['admin']['layout'];
        } elseif (isset($config['resources']['layout']['layout'])) {
            $layoutScript =
                 $config['resources']['layout']['layout'];
        }

        if (isset($config['resources']['layout']['configs'][$layoutScript])) {
            Centurion_Config_Manager::set('resources.layout.configs', $config['resources']['layout']['configs'][$layoutScript]);
        }

        if (null !== $layoutScript && $layoutScript !== 'default') {
            //$this->getRequest()->setParam('_layout', $layoutScript);
            $this->getActionController()
                 ->getHelper('layout')
                 ->setLayout($layoutScript);
        }
    }
}