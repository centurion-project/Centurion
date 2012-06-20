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
 * @package     Centurion_Rest
 * @subpackage  Route
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Rest
 * @subpackage  Route
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Rest_Route_CRUD extends Zend_Rest_Route
{
    protected $_responders = null;

    public function __construct(Zend_Controller_Front $front, array $defaults = array(), array $responders = array())
    {
        $this->_responders = $responders;
        foreach ($responders as $key => &$value) {
           if (is_array($value)) {
               $value = array_keys($value);
           }
        }

        parent::__construct($front, $defaults, $responders);
    }

    /**
     * Assembles user submitted parameters forming a URL path defined by this route
     *
     * @param array $data An array of variable and value pairs used as parameters
     * @param bool $reset Weither to reset the current params
     * @param bool $encode Weither to return urlencoded string
     * @return string Route path with user submitted parameters
     */
    public function assemble($data = array(), $reset = false, $encode = false)
    {
        if (!$this->_keysSet) {
            if (null === $this->_request) {
                $this->_request = $this->_front->getRequest();
            }
            $this->_setRequestKeys();
        }

        $params = (!$reset) ? $this->_values : array();

        foreach ($data as $key => $value) {
            if ($value !== null) {
                $params[$key] = $value;
            } elseif (isset($params[$key])) {
                unset($params[$key]);
            }
        }

        $params += $this->_defaults;

        $url = '';

        if ($this->_moduleValid || array_key_exists($this->_moduleKey, $data)) {
            if ($params[$this->_moduleKey] != $this->_defaults[$this->_moduleKey]) {
                $module = $params[$this->_moduleKey];
            }
        }
        unset($params[$this->_moduleKey]);

        $controller = $params[$this->_controllerKey];
        unset($params[$this->_controllerKey]);

        unset($params[$this->_actionKey]);

        if (isset($params['index']) && $params['index']) {
            unset($params['index']);
            $url .= '/index';
            foreach ($params as $key => $value) {
                if ($encode)
                    $value = urlencode($value);
                $url .= '/' . $key . '/' . $value;
            }
        } elseif (isset($params['id'])) {
            $url .= '/' . $params['id'];
            unset($params['id']);
        }

        if (!empty($url) || $controller !== $this->_defaults[$this->_controllerKey]) {
            $url = '/' . $controller . $url;
        }

        if (isset($module)) {
            $url = '/' . $module . $url;
        }

        $url = ltrim($url, self::URI_DELIMITER);

        if (count($params)) {
            $query = array();
            foreach($params as $key => $val) {
                if (is_array($val)) {
                    continue;
                }
                $query[] = $key . '=' . urlencode($val);
            }
            $url .= '?' . implode('&', $query);
        }

        return $url;
    }

    public function match($request, $partial = false)
    {
        if (!$request instanceof Zend_Controller_Request_Http) {
            $request = $this->_front->getRequest();
        }

        $this->_request = $request;
        $this->_setRequestKeys();

        $path   = $request->getPathInfo();
        $params = $request->getParams();
        $values = array();
        $path   = trim($path, self::URI_DELIMITER);

        if ($path != '') {

            $path = explode(self::URI_DELIMITER, $path);
            // Determine Module
            $moduleName = $this->_defaults[$this->_moduleKey];
            $dispatcher = $this->_front->getDispatcher();
            if ($dispatcher && $dispatcher->isValidModule($path[0])) {
                $moduleName = $path[0];
                if ($this->_checkRestfulModule($moduleName)) {
                    $values[$this->_moduleKey] = array_shift($path);
                    $this->_moduleValid = true;
                }
            }

            // Determine Controller
            $controllerName = $this->_defaults[$this->_controllerKey];
            if (count($path) && !empty($path[0])) {
                if ($this->_checkRestfulController($moduleName, $path[0])) {
                    $controllerName = $path[0];
                    $values[$this->_controllerKey] = array_shift($path);
                    $values[$this->_actionKey] = 'get';
                } else {
                    // If Controller in URI is not found to be a RESTful
                    // Controller, return false to fall back to other routes
                    return false;
                }
            } elseif ($this->_checkRestfulController($moduleName, $controllerName)) {
                $values[$this->_controllerKey] = $controllerName;
                $values[$this->_actionKey] = 'get';
            } else {
                return false;
            }

            //Store path count for method mapping
            $pathElementCount = count($path);

            // Check for "special get" URI's
            $specialGetTarget = false;
            if ($pathElementCount && array_search($path[0], array('index', 'new')) > -1) {
                $specialGetTarget = array_shift($path);
            } elseif ($pathElementCount && $path[$pathElementCount-1] == 'edit') {
                $specialGetTarget = 'edit';
                $params['id'] = $path[$pathElementCount-2];
            } elseif ($pathElementCount && $path[$pathElementCount-1] == 'list') {
                $specialGetTarget = 'list';
            } elseif ($pathElementCount && $path[$pathElementCount-1] == 'switch') {
                $specialGetTarget = 'switch';
            } elseif ($pathElementCount && $path[$pathElementCount-1] == 'action') {
                $specialGetTarget = 'action';
            }elseif ($pathElementCount == 1) {
                $params['id'] = urldecode(array_shift($path));
            } elseif ($pathElementCount == 0 && !isset($params['id'])) {
                $specialGetTarget = 'index';
            }

            // Digest URI params
            if ($numSegs = count($path)) {
                for ($i = 0; $i < $numSegs; $i = $i + 2) {
                    $key = urldecode($path[$i]);
                    $val = isset($path[$i + 1]) ? urldecode($path[$i + 1]) : null;
                    $params[$key] = $val;
                }
            }

            // Determine Action
            $requestMethod = strtolower($request->getMethod());
            if ($requestMethod != 'get' && $specialGetTarget !== 'list' && $specialGetTarget !== 'action') {
                if ($request->getParam('_method')) {
                    $values[$this->_actionKey] = strtolower($request->getParam('_method'));
                } elseif ( $request->getHeader('X-HTTP-Method-Override') ) {
                    $values[$this->_actionKey] = strtolower($request->getHeader('X-HTTP-Method-Override'));
                } else {
                    $values[$this->_actionKey] = $requestMethod;
                }

                // Map PUT and POST to actual create/update actions
                // based on parameter count (posting to resource or collection)
                switch( $values[$this->_actionKey] ){
                    case 'post':
                        if ($pathElementCount > 0) {
                            $values[$this->_actionKey] = 'put';
                        } else {
                            $values[$this->_actionKey] = 'post';
                        }
                        break;
                    case 'put':
                        $values[$this->_actionKey] = 'put';
                        break;
                }
            } elseif ($specialGetTarget) {
                $values[$this->_actionKey] = $specialGetTarget;
            }
        }
        $this->_values = $values + $params;

        $result = $this->_values + $this->_defaults;

        if ($partial && $result)
            $this->setMatchedPath($request->getPathInfo());

        if ($result == true && isset($this->_responders[$result['module']])
            && array_key_exists($result['controller'], $this->_responders[$result['module']])) {
            $result['model'] = $this->_responders[$result['module']][$result['controller']];
        }

        return $result;
    }
    public function setParam($key, $value = null)
    {
        if (null === $value && isset($this->_values[$key]))
            unset($this->_values[$key]);
        else
            $this->_values[$key] = $value;
    }
}
