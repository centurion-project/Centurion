<?php

class Bootstrap extends Centurion_Application_Bootstrap_Bootstrap
{
    /**
     * Init log for debug, prefixed by current date.
     */
    protected function _initLogDated()
    {
        $options = array(
                'stream' => array(
                    'writerName' => 'Stream',
                    'writerParams' => array(
                        'stream' => APPLICATION_PATH . sprintf('/../data/logs/%s_%s_application.log', APPLICATION_ENV, Zend_Date::now()->toString('yyyy.MM.dd')),
                        'mode'   => 'a'
                    )
                ),
        );

        $this->_loadPluginResource('Log', $options);
        $this->_executeResource('Log');
    }

    protected function _initDb()
    {
        try {
            Zend_Db_Table_Abstract::setDefaultAdapter($this->getPluginResource('db')->getDbAdapter());
            Zend_Db_Table_Abstract::setDefaultMetadataCache($this->_getCache('core'));

            Centurion_Db_Table_Abstract::setDefaultBackendOptions(Centurion_Config_Manager::get('resources.cachemanager.class.backend.options'));
            Centurion_Db_Table_Abstract::setDefaultFrontendOptions(Centurion_Config_Manager::get('resources.cachemanager.class.frontend.options'));
        } catch (Exception $e) {
            exit($e->getMessage());
        }
    }

    protected function _initTranslation()
    {
        $cache = $this->_getCache('core');
        Zend_Translate::setCache($cache);
        Zend_Date::setOptions(array('cache' => $cache));
        Zend_Paginator::setCache($cache);
    }

    protected function _initRequest()
    {
        $this->bootstrap('FrontController');
        $front = $this->getResource('FrontController');
        $request = $front->getRequest();

        if (null === $front->getRequest()) {
            $request = new Zend_Controller_Request_Http();
            $front->setRequest($request);
        }
        return $request;
    }

    protected function _initZFDebug()
    {
        if (Centurion_Config_Manager::get('zfdebug')) {
            $this->bootstrap('frontController');
            $frontController = $this->getResource('frontController');

            $options = array(
                'plugins' => array('Variables',
                                   'File' => array('base_path' => APPLICATION_PATH),
                                   'Memory',
                                   'Time',
                                   'Registry',
                                   'Exception',
                                   'Cache' => array('backend' => array('core'   =>  $this->_getCache('core')->getBackend(),
                                                                       'view'   =>  $this->_getCache('view')->getBackend(),
                                                                       '_page'   =>  $this->_getCache('_page')->getBackend()
                                                                        )
                                                                    )
                                                                )
            );

            if ($this->hasPluginResource('db')) {
                $this->bootstrap('db');
                $db = $this->getPluginResource('db')->getDbAdapter();
                $options['plugins']['Database']['adapter'] = $db;
            }

            $debug = new Centurion_ZFDebug_Controller_Plugin_Debug($options);
            $frontController->registerPlugin($debug);
        }
    }

    protected function _initCacheView()
    {
        Centurion_View::setDefaultCache($this->_getCache('view'));
    }

    protected function _initCachePage()
    {
        $this->bootstrap('FrontController');
        if ($this->getResource('FrontController')->getParam('displayExceptions') == false) {
            $this->bootstrap('contrib')
                 ->bootstrap('dbtable')
                 ->bootstrap('modules');
            $this->bootstrap('translate');

            $translator = $this->getResource('translate');

            $regexps = array(

               );

            $this->bootstrap('cachemanager');
            $cache = $this->getResource('cachemanager')->getCache('_page');

            $cache->setRegexps($regexps);
            $cache->start();
        }
    }
}
