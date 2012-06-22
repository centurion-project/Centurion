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
 * @copyright   Copyright (c) 2008-2009 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Controller
 * @copyright   Copyright (c) 2008-2009 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
abstract class Centurion_Controller_Action extends Zend_Controller_Action implements Centurion_Traits_Traitsable
{
    const STATUS_OK = 200;
    const STATUS_CREATED = 201;
    const STATUS_UNPROCESSABLE_ENTITY = 422;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_GONE = 410;
    const STATUS_INTERNAL_SERVER_ERROR = 500;

    //Is it still used ??
    const REG_KEY_CONTEXT = 'centurion_request_context';
    const REQ_CONTEXT_BACK = 'backoffice';
    const REQ_CONTEXT_FRONT = 'frontoffice';

    protected static $_cache = null;

    protected $_traitQueue;

    public function getTraitQueue()
    {
        if (null == $this->_traitQueue)
            $this->_traitQueue = new Centurion_Traits_Queue();

        return $this->_traitQueue;
    }

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);

        Centurion_Traits_Common::initTraits($this);
    }

    public function preDispatch()
    {
        parent::preDispatch();

        Centurion_Signal::factory('pre_dispatch')->send($this);

        //TODO: temporary, implement a way in Zend_Navigation_Page_Mvc to active a page in all actions
        $pages = $this->view->navigation()
                            ->findBy('uri', $this->_request->getRequestUri(), true);
        if (count($pages) === 0) {
            $tempPages = $this->view->navigation()
                            ->findBy('controller', $this->_request->getControllerName(), true);

            $pages = array();
            foreach ($tempPages as $key => $page) {
                if ($page->module == $this->_request->getModuleName()) {
                    $pages[] = $page;
                }
            }

            if (count($pages) === 0) {
                if (Zend_Controller_Front::getInstance()->getRouter()->getCurrentRouteName() !== 'default')
                    $pages = $this->view->navigation()
                                ->findBy('route', Zend_Controller_Front::getInstance()->getRouter()->getCurrentRouteName(), true);
            }
        }
        foreach ($pages as $key => $page) {
            $page->active = true;
            $page->setActive(true);
        }
    }

    public function postDispatch()
    {
        parent::postDispatch();
        Centurion_Signal::factory('post_dispatch')->send($this);
    }

    /**
     * Dispatch the requested action
     *
     * @param string $action Method name of action
     * @return void
     */
    public function dispatch($action)
    {
        try {
            parent::dispatch($action);
        } catch (Exception $e) {
            $cachePage = $this->getInvokeArg('bootstrap')->getResource('cachemanager')->getCache('_page');
            if (null !== $cachePage && method_exists($cachePage, 'cancel')) {
                $cachePage->cancel();
            }
            $cachePage = $this->getInvokeArg('bootstrap')->getResource('cachemanager')->getCache('page');
            if (null !== $cachePage && method_exists($cachePage, 'cancel')) {
                $cachePage->cancel();
            }
            throw $e;
        }
    }

    public function __get($name)
    {
        throw new Centurion_Controller_Action_Exception(sprintf('Undefined property %s', $name));
    }

    public function __set($name, $value)
    {
        // @todo : should test if property exists
        $this->$name = $value;
    }

    public function __isset($name)
    {
        try {
            if (!$this->$name) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function __call($method, $args)
    {
        $retVal = null;
        try {
            $retVal = parent::__call($method, $args);
        } catch(Zend_Controller_Action_Exception $e) {

            list($found, $retVal) = Centurion_Traits_Common::checkTraitOverload($this, $method, $args);

            if (!$found) {
                throw $e;
            }
        }
        return $retVal;
    }

    public function __unset($name)
    {
        throw new Centurion_Exception('not implemented yet');
    }

    /**
     * @see Centurion_Delegate_Interface::isAllowedContext()
     */
    public function isAllowedContext($context, $resource = null)
    {
        return in_array($context, iterator_to_array($this->_traitQueue), true);
    }
    
    /**
     * @see Centurion_Delegate_Interface::delegateGet()
     */
    public function delegateGet($context, $column)
    {
        if (!$this->isAllowedContext($context, $column)) {
            throw new Centurion_Controller_Action_Exception(sprintf('Undefined property %s', $column));
        }

        return $this->$column;
    }
    
    /**
     * @see Centurion_Delegate_Interface::delegateSet()
     */
    public function delegateSet($context, $column, $value)
    {
        if (!$this->isAllowedContext($context, $column)) {
            throw new Centurion_Controller_Action_Exception(sprintf('Undefined property %s', $column));
        }

        $this->$column = $value;
    }

    /**
     * @see Centurion_Delegate_Interface::delegateCall()
     */
    public function delegateCall($context, $method, $args = array())
    {
        if (!$this->isAllowedContext($context, $method)) {
            throw new Centurion_Controller_Action_Exception(sprintf('Undefined method %s', $method));
        }

        return call_user_func_array(array($this, $method), $args);
    }

    /**
     *
     * @return Centurion_Auth
     */
    public function getUser()
    {
        return Centurion_Auth::getInstance();
    }

    /**
     *
     * @return Zend_Cache_Core
     */
    protected function _getCache()
    {
        if (null === self::$_cache) {
            self::$_cache = $this->getInvokeArg('bootstrap')->getResource('cachemanager')->getCache('core');
        }

        return self::$_cache;
    }

    /**
     * Forwards current action to the default 404 error action.
     *
     * @param string $message Message of the generated exception
     * @throws Zend_Controller_Action_Exception
     */
    public function forward404($message = null)
    {
        throw new Centurion_Controller_Action_Exception($this->_get404Message($message), 404);
    }

    /**
     * Forwards current action to the default 404 error action unless the specified condition is true.
     *
     * @param bool   $condition A condition that evaluates to true or false
     * @param string $message   Message of the generated exception
     * @throws Zend_Controller_Action_Exception
     */
    public function forward404Unless($condition, $message = null)
    {
        if (!$condition) {
            $this->forward404($message);
        }
    }

    /**
     * Forwards current action to the default 404 error action if the specified condition is true.
     *
     * @param bool   $condition A condition that evaluates to true or false
     * @param string $message   Message of the generated exception
     *
     * @throws Zend_Controller_Action_Exception
     */
    public function forward404If($condition, $message = null)
    {
        if ($condition) {
            $this->forward404($message);
        }
    }

    /**
     * Redirects current request to a new URL, only if specified condition is true.
     *
     * This method stops the action. So, no code is executed after a call to this method.
     *
     * @param bool   $condition  A condition that evaluates to true or false
     * @param string $url        Url
     * @param string $statusCode Status code (default to 302)
     */
    public function redirectIf($condition, $url)
    {
        if ($condition) {
            $this->_redirect($url);
        }
    }

    /**
     * Redirects current request to a new URL, unless specified condition is true.
     *
     * This method stops the action. So, no code is executed after a call to this method.
     *
     * @param bool   $condition  A condition that evaluates to true or false
     * @param string $url        Url
     * @param string $statusCode Status code (default to 302)
     */
    public function redirectUnless($condition, $url)
    {
        if (!$condition) {
            $this->_redirect($url);
        }
    }

    /**
     * Render a View script to response with given parameters.
     *
     * @param string $viewScript View script
     * @param string $kwargs Parameters to assign
     * @return Centurion_Controller_Action
     */
    public function renderToResponse($viewScript, $kwargs)
    {
        $this->getHelper('viewRenderer')->setNoRender(true);
        $this->getResponse()->appendBody($this->renderToString($viewScript, $kwargs));

        return $this;
    }

    /**
     * @todo use renderToResponse instead
     */
    public function renderIfNotExists($action = null, $name = null, $noController = false)
    {
        $dirs = $this->view->getScriptPaths();
        $renderScript = false;
        $viewFile = $this->getRequest()->getControllerName()
                  . DIRECTORY_SEPARATOR
                  . $this->getRequest()->getActionName()
                  . '.' . $this->viewSuffix;
        foreach ($dirs as $dir) {
            if (is_readable($dir . $viewFile)) {
                $renderScript = true;
                break;
            }
        }

        if (!$renderScript) {
            $this->_helper->viewRenderer->setNoRender(true);
            $this->render($action, $name, $noController);
        }
    }

    /**
     * Render a View script to string with given parameters.
     *
     * @param string $viewScript View script
     * @param string $kwargs Parameters to assign
     */
    public function renderToString($viewScript, $kwargs)
    {
        return $this->view->renderToString($viewScript, $kwargs);
    }

    /**
     * @param null $message
     * @return null|string
     * @todo documentation
     */
    protected function _get404Message($message = null)
    {
        return null === $message
               ? sprintf('This request has been forwarded to a 404 error page by the action "%s/%s".',
                         $this->getRequest()->getModuleName(),
                         $this->getRequest()->getActionName())
               : $message;
    }

    /**
     * To disseminate the defined main object to the platform
     * @param mixed $object
     */
    public function definingMainObject($object){
        Centurion_Signal::factory('on_defining_main_object')->send($this, array($object));
    }
}
