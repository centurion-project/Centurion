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
 * @subpackage  Action
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */
/**
 * @category    Centurion
 * @package     Centurion_Controller
 * @subpackage  Action
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 * @author      Mathias Desloges <m.desloges@gmail.com>
 */
class Centurion_Controller_Action_Helper_ContextAutoSwitch extends Zend_Controller_Action_Helper_Abstract
{
    protected $_contexts = array(
        'xml',
        'json',
    );

    protected function _initContexts($actions)
    {
        $cs = $this->getActionController()->getHelper('ContextSwitch');
        $cs->setAutoJsonSerialization(false);

        foreach ($this->_contexts as $context) {
            foreach ($actions as $action) {
                $cs->addActionContext($action, $context);
            }
        }

        $cs->initContext();
    }

    public function direct($actions = array('index', 'post', 'get', 'put', 'delete'))
    {
        $request = $this->getActionController()->getRequest();

        // Set a Vary response header based on the Accept header
        $this->getResponse()->setHeader('Vary', 'Accept');
        
        if (!$request instanceof Zend_Controller_Request_Http) {
            return;
        }
        
        $header = $request->getHeader('Accept');
        switch (true) {
            case (strstr($header, 'application/json')):
            case ($this->getRequest()->isXMLHttpRequest()):
                $request->setParam('format', 'json');
                break;
            case (strstr($header, 'application/xml')
                  && (!strstr($header, 'html'))):
                $request->setParam('format', 'xml');
                break;
            default:
                break;
        }

        $this->_initContexts($actions);
    }
}
