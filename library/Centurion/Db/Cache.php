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
 * @package     Centurion_Db
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Db
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Db_Cache
{
    const DEFAULT_CLEAN_MODE = 'all';
    const DEFAULT_BACKEND_NAME = 'File';
    const DEFAULT_CACHE_BY_DEFAULT = true;

    protected $_object = null;

    protected $_cache = null;

    protected $_objectMethods = null;

    protected $_frontendOptions = null;

    protected $_backendOptions = null;

    public function __construct($object, $frontendOptions = null, $backendOptions = null, $backendName = self::DEFAULT_BACKEND_NAME)
    {
        $this->_object = $object;

        if (null !== $frontendOptions && null !== $backendOptions) {
            $this->_setFrontendOptions($frontendOptions)
                 ->_setBackendOptions($backendOptions);

            if (null === $backendName) {
                $backendName = self::DEFAULT_BACKEND_NAME;
            }

            $this->_setupCache($backendName);
        }
    }

    /**
     * Call object's methods from cache.
     *
     * @param $method
     * @param $args
     * @return unknown
     */
    public function __call($method, $args)
    {
        $class_methods = array_merge($this->_getObjectMethods(), get_class_methods($this->_cache));

        //This was commented beacause it does not check in magic call
//        if (!in_array($method, $class_methods)) {
//            throw new Centurion_Db_Exception(sprintf("Method %s does not exist in this class %s", $method, get_class($this->_object)));
//        }

        return call_user_func_array(array($this->_cache , $method), $args);
    }

    public function setCacheSuffix($suffix)
    {
        if (!is_string($suffix)) {
            throw new Centurion_Db_Exception('Cache Suffix must be a string');
        }

        $this->_cache->setOption('cache_id_prefix', $suffix . '_' . $this->_cache->getOption('cache_id_prefix'));

        return $this;
    }

    public function clean($mode = self::DEFAULT_CLEAN_MODE, $tags = array())
    {
        return $this->_cache->clean($mode, $tags);
    }

    public function setTagsArray($tags = array())
    {
        $this->_cache->setTagsArray($tags);

        return $this;
    }

    public function setPriority($priority)
    {
        $this->_cache->setPriority($priority);

        return $this;
    }

    public function setLifetime($lifetime = false)
    {
        $this->_cache->setLifetime($lifetime);

        return $this;
    }

    public function setCache(Zend_Cache_Core $cache)
    {
        $this->_cache = $cache;

        return $this;
    }

    /**
     * Retrieve object methods.
     *
     * @return array
     */
    protected function _getObjectMethods()
    {
        if ($this->_objectMethods === null && $this->_object !== null) {
            $class = get_class($this->_object);
            $this->_objectMethods = get_class_methods($class);
        }

        return $this->_objectMethods;
    }

    /**
     * Set the FrontendOptions for Cache Frontend.
     *
     * @param array $frontendOptions
     * @return BaseModelCache
     */
    protected function _setFrontendOptions($frontendOptions)
    {
        if (!is_array($frontendOptions)) {
            throw new Zend_Cache_Exception('frontendOptions must be an array.');
        }

        if (!isset($frontendOptions['cache_id_prefix'])) {
            $frontendOptions['cache_id_prefix'] = '';
        }

        $frontendOptions['cache_id_prefix'] = get_class($this->_object) . '_' . $frontendOptions['cache_id_prefix'];

        if (!isset($frontendOptions['cached_entity'])) {
            $frontendOptions['cached_entity'] = $this->_object;
        }

        if (!isset($frontendOptions['cache_by_default'])) {
            $frontendOptions['cache_by_default'] = self::DEFAULT_CACHE_BY_DEFAULT;
        }

        $this->_frontendOptions = $frontendOptions;

        return $this;
    }

    /**
     * @todo implementation, with options.
     * @param string $backendOptions
     */
    protected function _setBackendOptions($backendOptions)
    {
        $this->_backendOptions = $backendOptions;

        return $this;
    }

    protected function _setupCache($backendName)
    {
        $frontend = new Zend_Cache_Frontend_Class($this->_frontendOptions);

        $this->_cache = Zend_Cache::factory($frontend, $backendName, $this->_frontendOptions, $this->_backendOptions);
        $this->_cache->setTagsArray(array(Centurion_Cache_TagManager::getTagOf($this->_object)));

        return $this;
    }
}