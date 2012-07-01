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
 * @category         Centurion
 * @package          Centurion_Application
 * @subpackage       Bootstrap
 * @copyright        Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license          http://centurion-project.org/license/new-bsd     New BSD License
 * @version          $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Application
 * @subpackage  Bootstrap
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
abstract class Centurion_Application_Bootstrap_Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    /**
     * Constructor
     *
     * Ensure FrontController resource is registered.
     *
     * @param  Zend_Application|Zend_Application_Bootstrap_Bootstrapper $application
     * @return void
     */
    public function __construct($application)
    {
        parent::__construct($application);
        Zend_Controller_Action_HelperBroker::addPrefix('Centurion_Controller_Action_Helper', 'Centurion/Controller/Action/Helper/');
    }

    /**
     * Get the plugin loader for resources
     *
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader()
    {
        if ($this->_pluginLoader === null) {
            $options = array(
                'Zend_Application_Resource'      => 'Zend/Application/Resource',
                'Centurion_Application_Resource' => 'Centurion/Application/Resource',
            );

            $this->_pluginLoader = new Centurion_Loader_PluginLoader($options, 'Bootstrap');
        }

        return $this->_pluginLoader;
    }

    /**
     * Initialize request: replace default front controller request by Centurion's one.
     *
     * @return void
     */
    protected function _initSetRequest()
    {
        $this->bootstrap('FrontController');
        $this->getResource('FrontController')->setRequest('Centurion_Controller_Request_Http');
    }

    /**
     * Initialize Helper.
     *
     * @return void
     */
    protected function _initHelper()
    {
        $this->bootstrap('FrontController');
        Zend_Controller_Action_HelperBroker::addHelper(new Centurion_Controller_Action_Helper_LayoutLoader());
        Zend_Controller_Action_HelperBroker::addHelper(new Centurion_Controller_Action_Helper_DbAutoFiltersSwitch());
    }

    /**
     * Add Contrib directory to module directory.
     *
     * @return void
     */
    protected function _initContrib()
    {
        $this->bootstrap('FrontController');
        $this->getResource('FrontController')->addModuleDirectory(APPLICATION_PATH . '/../library/Centurion/Contrib');
    }

    /**
     * Initialize plugins.
     *
     * @return void
     */
    protected function _initPlugins()
    {
        $this->bootstrap('FrontController');
        $front = $this->getResource('FrontController');
        $front->registerPlugin(new Centurion_Controller_Plugin_ModuleBootstrap())
            ->registerPlugin(new Centurion_Controller_Plugin_ErrorControllerBootstrap());
    }

    /**
     * Initialize DbTable for each loaded module.
     *
     * @return void
     */
    protected function _initDbTable()
    {
        $cache = $this->_getCache('core');

        if (!($references = $cache->load('references_apps'))) {
            $this->bootstrap('FrontController');
            $this->bootstrap('db');
            $this->bootstrap('modules');

            $front   = $this->getResource('FrontController');
            $modules = $front->getControllerDirectory();

            $references = array();

            $moduleEnabled = Centurion_Config_Manager::get('resources.modules');

            foreach ($modules as $moduleName => $module) {
                if (!in_array($moduleName, $moduleEnabled)) {
                    continue;
                }

                $dbTableDir = realpath($module . '/../models/DbTable');
                if (!file_exists($dbTableDir)) {
                    continue;
                }

                $dir = new Centurion_Iterator_DbTableFilter($dbTableDir);

                foreach ($dir as $fileInfo) {
                    $filename  = $fileInfo->getFilenameWithoutExtension();
                    $className = sprintf('%s_Model_DbTable_%s', ucfirst($moduleName), $filename);

                    $model    = Centurion_Db::getSingletonByClassName($className);
                    $meta     = $model->getMeta();
                    $metaData = $model->info('metadata');

                    foreach ($model->getReferenceMap() as $key => $referenceMap) {
                        $refTableClass   = $referenceMap['refTableClass'];
                        $referencedModel = Centurion_Db::getSingletonByClassName($refTableClass);

                        $plural = true;
                        if (isset($metaData[$referenceMap['columns']]['UNIQUE']) && $metaData[$referenceMap['columns']]['UNIQUE'] == true) {
                            $plural = false;
                        }

                        if ($plural) {
                            $dependentTableKey = $meta['verbosePlural'];
                        } else {
                            $dependentTableKey = $meta['verboseName'];
                        }

                        if (array_key_exists($dependentTableKey, $referencedModel->getDependentTables())) {
                            continue;
                        }

                        if (!array_key_exists($refTableClass, $references)) {
                            $references[$refTableClass] = array();
                        }

                        $references[$refTableClass][$dependentTableKey] = $className;
                    }
                }
            }

            $cache->save($references, 'references_apps');
        }

        Centurion_Db::setReferences($references);
    }

    /**
     * Fetch the named cache object, or instantiate and return a cache object
     * using a named configuration template.
     *
     * @param  string $key
     * @return Zend_Cache_Core
     */
    protected function _getCache($key = 'core')
    {
        $this->bootstrap('cachemanager');
        return $this->getResource('cachemanager')->getCache($key);
    }
}
