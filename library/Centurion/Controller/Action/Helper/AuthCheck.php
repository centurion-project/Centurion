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
class Centurion_Controller_Action_Helper_AuthCheck extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Check if the current user has an identity.
     *
     * @param string $url Redirect to a specific url, by default it redirects to admin login form
     */
    public function direct($url = null, $throwException = false)
    {
        if (!$this->getActionController()->getUser()->hasIdentity() || $this->getActionController()->getUser()->getIdentity()->username == 'anonymous') {
            if (null === $url) {
                if (null != Centurion_Config_Manager::get('auth.login.params')) {
                    $params = Centurion_Config_Manager::get('auth.login.params');
                } else {
                    $params = array('controller'  =>  'login',
                                    'action'      =>  'index',
                                    'module'      =>  'admin');
                }
                if (null !== Centurion_Config_Manager::get('auth.login.params')) {
                    $route = Centurion_Config_Manager::get('auth.login.route');
                } else {
                    $route = 'default';
                }
                
                $url = Zend_Controller_Action_HelperBroker::getStaticHelper('url')->url($params, $route);
            }

            if ($throwException) {
                throw new Zend_Auth_Exception('Authentification required');
            }

            $url .= sprintf('?next=%s', $this->getRequest()->getRequestUri());
            
            if (defined('PHPUNIT') && PHPUNIT == true) {
                $this->getActionController()->getHelper('viewRenderer')->setNoRender();
                $this->getActionController()->getHelper('layout')->disableLayout();
                Zend_Controller_Front::getInstance()->setParam('noErrorHandler', true);
                $this->getActionController()->getHelper('redirector')->setPrependBase(false)->gotoUrl($url);
                throw new Exception ('die');
            } else {
                return $this->getActionController()->getHelper('redirector')->setPrependBase(false)->gotoUrlAndExit($url);
            }
        }
    }
}
