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
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Controller
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Controller_Error extends Centurion_Controller_Action
{
    /**
     * @var Exception
     */
    protected $_exception = null;

    /**
     * Http code
     *
     * @var string
     */
    protected $_httpCode = null;

    /**
     * Priority
     *
     * @var int
     */
    protected $_priority = null;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->_exception = $this->_getParam('error_handler');

        if ($this->_exception) {
            if (isset($this->_exception) && $this->_exception->exception->getMessage() == 'die') {
                return;
            }
            $this->_response->clearBody();

            $this->_response->append('error', null);

            switch ($this->_exception->type) {
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
                    $this->_httpCode = self::STATUS_NOT_FOUND;
                    $this->_priority = Zend_Log::INFO;
                break;
                case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER:

                    switch (get_class($this->_exception->exception)) {
                        case 'Centurion_Controller_Action_Gone_Exception':
                            $this->_httpCode = self::STATUS_GONE;
                            $this->_priority = Zend_Log::INFO;
                            break;
                        case 'Zend_View_Exception' :
                            $this->_httpCode = self::STATUS_INTERNAL_SERVER_ERROR;
                            $this->_priority = Zend_Log::WARN;
                        break;
                        case 'Zend_Db_Exception' :
                            $this->_httpCode = self::STATUS_INTERNAL_SERVER_ERROR;
                            $this->_priority = Zend_Log::CRIT;
                        break;
                        default:
                            $this->_httpCode = self::STATUS_INTERNAL_SERVER_ERROR;
                            $this->_priority = Zend_Log::CRIT;
                        break;
                    }
                break;
            }
        }
    }

    public function errorAction()
    {
        $this->view->headMeta()->appendName('robots', 'noindex,follow');
        $this->view->headTitle()->prepend('Error - ');

        $cachePage = $this->getInvokeArg('bootstrap')->getResource('cachemanager')->getCache('_page');

        if (null !== $cachePage && method_exists($cachePage, 'cancel')) {
            $cachePage->cancel();
        }

        $cachePage = $this->getInvokeArg('bootstrap')->getResource('cachemanager')->getCache('page');

        if (null !== $cachePage && method_exists($cachePage, 'cancel')) {
            $cachePage->cancel();
        }

        if ($this->_httpCode) {
            $this->getResponse()->setHttpResponseCode($this->_httpCode);
        }

        $params = array();

        if ($log = $this->_getLog()) {
            if ($this->_exception) {
                if (method_exists($this->_request, 'getRequestUri')) {
                    $uri = $this->_request->getRequestUri();
                } else {
                    $uri = $this->view->url();
                }

                $referer = '';

                if (isset($_SERVER['HTTP_REFERER'])) {
                    $referer =' (' . $_SERVER['HTTP_REFERER'] . ')';
                }

                $log->log(sprintf("%d: \n%s \n%s: %s\n\n%s\n\n",
                                  $this->_httpCode,
                                  $uri,
                                  $referer,
                                  $this->_exception->exception->getMessage(),
                                  $this->_exception->exception->getTraceAsString()),
                          $this->_priority);
            }
        }

        if ($this->getInvokeArg('displayExceptions') == true) {
            $params = array();
            if ($this->_exception) {
                $params = array('exception'  =>  $this->_exception->exception,
                                'trace'      =>  Zend_Debug::dump($this->_exception->exception->getTraceAsString(), null, false));
            }

            $params['profiler'] = Centurion_Db_Table_Abstract::getDefaultAdapter()->getProfiler()->getQueryProfiles(null, true);

            $this->renderToResponse(array('error/error-dev.phtml',
                                          'centurion/error.phtml'), $params);
        } else {
            $this->renderToResponse(array('error/error.phtml',
                                          'centurion/error.phtml'), $params);
        }
    }

    public function unauthorizedAction()
    {
        $this->getResponse()->setHttpResponseCode(self::STATUS_FORBIDDEN);

        if ($this->_getParam('ajax')) {
            return $this->_helper->json(array('error' => $this->view->translate("Unauthorized action\nAs a %s user, you don't have the permission to accomplish this action.", $this->getUser()->getIdentity()->username)));
        } else {
            $this->view->headTitle($this->view->translate('Unauthorized access - '));
        }
    }

    /**
     * Retrieve the logger adapter.
     *
     * @return false|Zend_Log
     */
    protected function _getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');

        if (!$bootstrap->hasPluginResource('Log')) {
            return false;
        }

        return $bootstrap->getResource('Log');
    }
}
