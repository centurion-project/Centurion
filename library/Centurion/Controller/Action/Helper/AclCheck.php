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
class Centurion_Controller_Action_Helper_AclCheck extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Check the ACLs for the current logged user and redirect to a url.
     *
     * @param string $permission Permission name to check
     * @param bool $throwException Throw an exception (Centurion_Auth_Exception) if the user is not allowed
     * @param string $url Redirect to a url, by default it redirects to a unauthorized action
     * @return bool Result
     */
    public function direct($permission = null, $throwException = false, $url = null)
    {
        $aclResult = false;
        if (null === $permission) {
            $permission = sprintf('%s_%s_%s', $this->getRequest()->getModuleName(),
                                              $this->getRequest()->getControllerName(),
                                              $this->getRequest()->getActionName());
        }

        if ($this->getActionController()->getUser()->hasIdentity()) {
            $aclResult = $this->getActionController()->getUser()->getIdentity()->isAllowed($permission);
        }

        if (!$aclResult) {
            if ($throwException) {
                //TODO: Do we have a closer exception ?
                throw new Centurion_Exception(sprintf('permission (%s) denied', $permission));
            }

            if (null !== $url) {
                if (defined('PHPUNIT') && PHPUNIT == true) {
                    $this->getActionController()->getHelper('viewRenderer')->setNoRender();
                    $this->getActionController()->getHelper('layout')->disableLayout();
                    Zend_Controller_Front::getInstance()->setParam('noErrorHandler', true);
                    $this->getActionController()->getHelper('redirector')->setPrependBase(false)->gotoUrl($url);
                    throw new Exception('die');
                } else {
                    return $this->getActionController()->getHelper('redirector')->setPrependBase(false)->gotoUrlAndExit($url);
                }
            } else {
                $params = array();
                if ($this->getRequest()->isXmlHttpRequest()) {
                    $params = array('ajax' => true);
                }

                if (defined('PHPUNIT') && PHPUNIT == true) {
                    $this->getActionController()->getHelper('viewRenderer')->setNoRender();
                    $this->getActionController()->getHelper('layout')->disableLayout();
                    Zend_Controller_Front::getInstance()->setParam('noErrorHandler', true);
                    $this->getActionController()->getHelper('redirector')->setPrependBase(false)
                                            ->gotoSimple('unauthorized',
                                                                'error',
                                                                'admin', $params);
                    throw new Exception('die');
                } else {
                    return $this->getActionController()->getHelper('redirector')->setPrependBase(false)
                                            ->gotoSimpleAndExit('unauthorized',
                                                                'error',
                                                                'admin', $params);
                }
            }
        }

        return $aclResult;
    }
}
