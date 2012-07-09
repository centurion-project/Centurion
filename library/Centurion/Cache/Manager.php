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
 * @package     Centurion_Cache
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Cache
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 * @todo        Documentation
 */
class Centurion_Cache_Manager extends Zend_Cache_Manager
{

    protected $_shutdownClean = array();

    /**
     * Array in order to save Rowset between pre and post signal
     *
     * @var array[string]Centurion_Db_Table_Rowset_Abstract
     */
    protected $_rowset = array();

    protected $_backends = null;

    /**
     *
     * @return void
     */
    public function __construct()
    {
        $this->connectSignal();
    }

    /**
     * @return void
     */
    public function connectSignal()
    {
        Centurion_Signal::factory('clean_cache')->connect(array($this, 'cleanCacheSignal'));
        Centurion_Signal::factory('clean_cache_asynchronous')->connect(array($this, 'cleanCacheAsynchronousSignal'));

        Centurion_Signal::factory('post_delete')->connect(array($this, 'signalRow'), 'Centurion_Db_Table_Row_Abstract');
        Centurion_Signal::factory('post_update')->connect(array($this, 'signalRow'), 'Centurion_Db_Table_Row_Abstract');
        Centurion_Signal::factory('post_save')->connect(array($this, 'signalRow'), 'Centurion_Db_Table_Row_Abstract');
        Centurion_Signal::factory('post_insert')->connect(array($this, 'signalRow'), 'Centurion_Db_Table_Row_Abstract');

        Centurion_Signal::factory('post_delete')->connect(array($this, 'signalTable'), 'Centurion_Db_Table_Abstract');
        Centurion_Signal::factory('post_update')->connect(array($this, 'signalTable'), 'Centurion_Db_Table_Abstract');

        Centurion_Signal::factory('pre_delete')->connect(array($this, 'preSignalTable'), 'Centurion_Db_Table_Abstract');
        Centurion_Signal::factory('pre_update')->connect(array($this, 'preSignalTable'), 'Centurion_Db_Table_Abstract');

        Centurion_Signal::factory('post_insert')->connect(array($this, 'insertTable'), 'Centurion_Db_Table_Abstract');
    }

    public function cleanCacheSignal($signal, $sender, $mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
        return $this->cleanCache($mode, $tags);
    }

    public function cleanCacheAsynchronousSignal($signal, $sender, $mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array(), $frontend = null)
    {

         return $this->cleanCacheAsynchronous($mode, $tags, $frontend);
    }

    /**
     * Observer function for signal "post_insert" on class Centurion_Db_Table_Abstract.
     *  => cleanCache on tag table
     *
     * @param $signal Centurion_Signal_Abstract
     * @param $sender Centurion_Db_Table_Abstract
     * @param $row array|Centurion_Db_Table_Abstract|Centurion_Db_Table_Row_Abstract
     * @return $this
     */
    public function insertTable($signal, $sender, $row)
    {
        $name = $sender->info(Centurion_Db_Table_Abstract::NAME);
        $tags = array('__' . $name, '__' . $name . '__insert');
        $this->cleanCacheAsynchronous(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);

        try {
            if ($sender->info(Centurion_Db_Table_Abstract::PRIMARY)) {
                if (!is_object($row)) {
                    $row = call_user_func_array(array($sender, 'find'), (array) $row)->current();
                }
                if ($row !== null)
                    $this->signalRow($signal, $row);
            }
        } catch(Exception $e) {

        }

        return $this;
    }

    /**
     * @return $this
     */
    public function preSignalTable()
    {
        $args = func_get_args();
        $signal = array_shift($args);
        $sender = array_shift($args);

        if (2 === count($args))
            $data = array_shift($args);

        $where = array_shift($args);

        $signalName = get_class($signal);
        $signalName = strtolower(substr($signalName, strpos($signalName, 'Pre') + 3));

        $key = sprintf('%s_%s_%s', $signalName, get_class($sender), spl_object_hash($sender));

        $this->_rowset[$key] = $sender->all($where);

        return $this;
    }

    /**
     * @return $this
     */
    public function signalTable()
    {
        list($signal, $table) = func_get_args();

        $name = $table->info(Centurion_Db_Table_Abstract::NAME);

        $signalName = get_class($signal);
        $signalName = strtolower(substr($signalName, strpos($signalName, 'Post')+4));

        $tags = array('__' . $name, '__' . $name . '__' . $signalName);

        $key = sprintf('%s_%s_%s', $signalName, get_class($table), spl_object_hash($table));
        if (isset($this->_rowset[$key])) {
            foreach ($this->_rowset[$key] as $row) {
                $pk = is_string($row->pk) || is_int($row->pk) ? $row->pk : md5(serialize($row->pk));
                array_push($tags, sprintf('__%s__%s', $row->getTable()->info(Centurion_Db_Table_Abstract::NAME), $pk));
            }
        }
        unset($this->_rowset[$key]);

        $this->cleanCacheAsynchronous(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags);

        return $this;
    }

    /**
     * @param $signal
     * @param \Centurion_Db_Table_Row_Abstract $row
     * @return $this
     */
    public function signalRow($signal, Centurion_Db_Table_Row_Abstract $row)
    {
        $pk = is_string($row->pk) || is_int($row->pk) ? $row->pk : md5(serialize($row->pk));

        $tag = sprintf('__%s__%s', $row->getTable()->info(Centurion_Db_Table_Abstract::NAME), $pk);
        $this->cleanCacheAsynchronous(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array($tag));

        $tag = sprintf('__%s', $row->getTable()->info(Centurion_Db_Table_Abstract::NAME));
        $this->cleanCacheAsynchronous(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array($tag));

        return $this;
    }

    /**
     * @param $mode
     * @param $tags
     * @param $frontend
     * @return $this
     */
    public function cleanCacheAsynchronous($mode = Zend_Cache::CLEANING_MODE_ALL, array $tags = array(), $frontend = null)
    {
         if (null === $frontend) {
             // If $frontend not set, we clean all cache registred
             foreach ($this->getBackends() as $name => $backend) {
                 $this->cleanCacheAsynchronous($mode, $tags, $name);
             }
         } else {
            if (!isset($this->_shutdownClean[$frontend]))
                $this->_shutdownClean[$frontend] = array();

            if (isset($this->_shutdownClean[$frontend][Zend_Cache::CLEANING_MODE_ALL]))
                return;

            switch ($mode) {
                case Zend_Cache::CLEANING_MODE_ALL:
                    $this->_shutdownClean[$frontend] = array();
                case Zend_Cache::CLEANING_MODE_OLD:
                    $this->_shutdownClean[$frontend][$mode] = true;
                    break;
                case Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                    if (!isset($this->_shutdownClean[$frontend][$mode])) {
                        $this->_shutdownClean[$frontend][$mode] = array();
                    }

                    foreach ($tags as $tag) {
                        $this->_shutdownClean[$frontend][$mode][$tag] = $tag;
                    }

                    break;
                case Zend_Cache::CLEANING_MODE_MATCHING_TAG:
                case Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
                    if (!isset($this->_shutdownClean[$frontend][$mode]))
                        $this->_shutdownClean[$frontend][$mode] = array();

                    $this->_shutdownClean[$frontend][$mode][] = $tags;
                    break;
            }
        }
        return $this;
    }

    /**
     * @param string $mode
     * @param array $tags
     * @return $this
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
         foreach ($this->getBackends() as $key => $backend) {
             if (null !== $backend) {
                $backend->clean($mode, $tags);
             }
         }
         return $this;
    }

    public function cleanCache($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = array())
    {
         return $this->clean($mode, $tags);
    }

    public function getBackend($key)
    {
        $this->getBackends();

        if (isset($this->_backends[$key]))
            return $this->_backends[$key];
        return null;
    }

    public function getBackends()
    {
        if (null === $this->_backends) {
            foreach ($this->_optionTemplates as $key => $value) {
                //Specific Zend cache
                if ($key === 'skeleton' || $key === 'default' || $key === 'pagetag')
                    continue;

                //Specific Zend cache
                if ($key === 'page')
                    $this->_backends[$key] = $this->getCache('page')->getBackend();
                else if (isset($this->_caches[$key]))
                    $this->_backends[$key] = $this->_caches[$key]->getBackend();
                else {
                    $this->_backends[$key] = Zend_Cache::_makeBackend($value['backend']['name'], $value['backend']['options'], (isset($value['backend']['customBackendNaming']))?$value['backend']['customBackendNaming']:false);
                }
            }
        }
        return $this->_backends;
    }
    
    public function addIdPrefix($id)
    {
        foreach ($this->_optionTemplates as $name => $tmp) {
            if (!isset($this->_optionTemplates[$name]['frontend']['name']))
                continue;
            if ('page' == $name)
                continue;
            try {
                $this->getCache($name)->setOption('cache_id_prefix', $id);
            } catch (Exception $e) {
                
            }
        }
    }

    /**
     * Destruct function for cleaning cache
     *
     * @return $this
     */
    public function __destruct()
    {
        foreach ($this->_shutdownClean as $cacheName => $params) {
            $cacheBackend = $this->getBackend($cacheName);

            if (null === $cacheBackend) {
                continue;
            }

            foreach ($params as $mode => $params) {
                switch ($mode) {
                    case Zend_Cache::CLEANING_MODE_ALL:
                    case Zend_Cache::CLEANING_MODE_OLD:
                        $cacheBackend->clean($mode);
                        break;
                    case Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                        $cacheBackend->clean($mode, $params);
                        break;
                    case Zend_Cache::CLEANING_MODE_MATCHING_TAG:
                    case Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
                        foreach($params as $tags)
                            $cacheBackend->clean($mode, $tags);
                        break;
                }
            }
        }
        
        $this->_shutdownClean = array();
        
        return $this;
    }

    public function getTemplateOptions($name = null)
    {
        if (null === $name)
            return $this->_optionTemplates;

        if (!isset($this->_optionTemplates[$name]))
            return null;
        return $this->_optionTemplates[$name];
    }

    /**
     * Simple method to merge two configuration arrays
     *
     * @param  array $current
     * @param  array $options
     * @return array
     */
    protected function _mergeOptions(array $current, array $options)
    {
        if (isset($options['frontend']['name'])) {
            $current['frontend']['name'] = $options['frontend']['name'];
        }
        if (isset($options['backend']['name'])) {
            $current['backend']['name'] = $options['backend']['name'];
        }
        if (isset($options['frontend']['options'])) {
            foreach ($options['frontend']['options'] as $key=>$value) {
                $current['frontend']['options'][$key] = $value;
            }
        }
        if (isset($options['backend']['options'])) {
            foreach ($options['backend']['options'] as $key=>$value) {
                $current['backend']['options'][$key] = $value;
            }
        }

        if (isset($options['frontend']['customFrontendNaming'])) {
            $current['frontend']['customFrontendNaming'] = $options['frontend']['customFrontendNaming'];
        }

        if (isset($options['backend']['customBackendNaming'])) {
            $current['backend']['customBackendNaming'] = $options['backend']['customBackendNaming'];
        }

        return $current;
    }
}
