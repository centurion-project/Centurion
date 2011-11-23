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
 * @package     Centurion_Application
 * @subpackage  Module
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Application
 * @subpackage  Module
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
abstract class Centurion_Application_Module_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected $_cache = array();
    /**
     * Retrieve module resource loader.
     *
     * @return Centurion_Application_Module_Autoloader
     */
    public function getResourceLoader()
    {
        if (null === $this->_resourceLoader) {
            $r = new ReflectionClass($this);
            $path = $r->getFileName();
            $this->setResourceLoader(new Centurion_Application_Module_Autoloader(array(
                'namespace' => $this->getModuleName(),
                'basePath'  => dirname($path),
            )));
        }
        
        return $this->_resourceLoader;
    }
    
    protected function _initActionHelpers()
    {
        //TODO: do it only once
        $resource = $this->getResourceLoader()->getResourceType('controllershelpers');
        Zend_Controller_Action_HelperBroker::addPath($resource['path'], $resource['namespace']);
    }
    
    protected function _initSignals()
    {
        //TODO: do it only once
        $resource = $this->getResourceLoader()->getResourceType('signals');
        Centurion_Signal::getPluginLoader()->addPrefixPath($resource['namespace'], $resource['path']);
    }
    
    /**
     * Retrieve FrontController.
     *
     * @return Zend_Controller_Front
     */
    protected function _getFrontController()
    {
        $bootstrap = $this->getApplication();
        if ($bootstrap instanceof Zend_Application) {
            $bootstrap = $this;
        }
        
        $bootstrap->bootstrap('FrontController');
        $front = $bootstrap->getResource('FrontController');
        
        return $front;
    }
    
    /**
     * Retrieve a cache from the manager.
     *
     * @param string $key Key name of the cache
     * @return Zend_Cache_Core
     */
    protected function _getCache($key = 'core')
    {
        if (!isset($this->_cache[$key])) {
            $bootstrap = $this->getApplication();
            $bootstrap->bootstrap('cachemanager');
            $this->_cache[$key] = $bootstrap->getResource('cachemanager')->getCache($key);
        }
        return $this->_cache[$key];
    }
}