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
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Controller_Action_Helper_WidgetRenderer extends Zend_Controller_Action_Helper_ActionStack
{

    public function direct($config, $controller = null, $module = null, array $params = array())
    {
        if (!isset($config['widgets']))
            throw new Centurion_Exception('missing key \'widgets\' in config');

        $this->getActionController()->getHelper('viewRenderer')->setNoRender();

        if (!$this->getRequest()->getParam('render', false)) {
            //$this->_actionStack->setClearRequestParams(true);

            $this->actionToStack($this->getRequest()->getActionName(), $this->getRequest()->getControllerName(), $this->getRequest()->getModuleName(), array('render' => true));

            foreach ($config['widgets'] as $key => $value) {
               $this->actionToStack($value['action'], $value['controller'], $value['module'], array_merge($value['params'], array('placeholderName' => $key, 'format' => 'widget')));
            }

        } else {
            if (isset($config['viewScript']) && $config['viewScript'])
                $script = $config['viewScript'];
            else
                $script = $this->getActionController()->getViewScript($this->getRequest()->getActionName());

//            die($this->getRequest()->getModuleName());

            $this->getActionController()->renderToResponse($script, array());
        }
    }

    public function renderAsWidget()
    {
        $action = $this->getActionController();

        $action->getHelper('viewRenderer')->setNoRender();

        $script = $action->getViewScript($this->getRequest()->getActionName());

        $widgetContent = $action->renderToString($script, array());
        $placeholderName = $action->getRequest()->getParam('placeholderName', '');

        $action->view->placeholder($placeholderName)->set($widgetContent);
    }

}
