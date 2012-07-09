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
class Centurion_Controller_Action_Helper_Params extends Zend_Controller_Action_Helper_Abstract
{
    protected static $_paramsLoaded = false;

    /**
     * @var array Parameters detected in raw content body
     */
    protected $_bodyParams = array();

    /**
     * Do detection of content type, and retrieve parameters from raw body if
     * present
     *
     * @return void
     */
    protected function _loadParams()
    {
        $request = $this->getRequest();
        $contentType = $request->getHeader('Content-Type');
        $rawBody = $request->getRawBody();

        if (!$rawBody) {
            return;
        }

        switch (true) {
            case (strstr($contentType, 'application/json')):
                $this->setBodyParams(Zend_Json::decode($rawBody));
                break;
            case (strstr($contentType, 'application/xml')):
                $config = new Zend_Config_Xml($rawBody);
                $this->setBodyParams($config->toArray());
                break;
            default:
                if ($request->isPut()) {
                    parse_str($rawBody, $params);
                    $this->setBodyParams($params);
                }
                break;
        }

        self::$_paramsLoaded = true;
    }

    /**
     * Set body params
     *
     * @param  array $params
     * @return Scrummer_Controller_Action
     */
    public function setBodyParams(array $params)
    {
        $this->_bodyParams = $params;

        return $this;
    }

    /**
     * Retrieve body parameters
     *
     * @return array
     */
    public function getBodyParams()
    {
        return $this->_bodyParams;
    }

    /**
     * Get body parameter
     *
     * @param  string $name
     * @return mixed
     */
    public function getBodyParam($name)
    {
        if ($this->hasBodyParam($name)) {
            return $this->_bodyParams[$name];
        }

        return null;
    }

    /**
     * Is the given body parameter set?
     *
     * @param  string $name
     * @return bool
     */
    public function hasBodyParam($name)
    {
        return isset($this->_bodyParams[$name]);
    }

    /**
     * Do we have any body parameters?
     *
     * @return bool
     */
    public function hasBodyParams()
    {
        return !empty($this->_bodyParams);
    }

    /**
     * Get submit parameters
     *
     * @return array
     */
    public function getSubmitParams()
    {
        if (!self::$_paramsLoaded) {
            $this->_loadParams();
        }

        if ($this->hasBodyParams()) {
            return $this->getBodyParams();
        }
        return $this->getRequest()->getPost();
    }

    /**
     * @return array
     */
    public function direct()
    {
        return $this->getSubmitParams();
    }
}
