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
 * @package     Centurion_Contrib
 * @subpackage  Admin
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Admin
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Admin_IndexController extends Centurion_Controller_Action
{
    public function indexAction()
    {
    }
    
    public function preDispatch()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();
        $this->_helper->layout->setLayout('admin');
        parent::preDispatch();
    }

    public function dashboardAction ()
    {
        $config = Centurion_Config_Manager::get('admin.dashboard');

        $this->_helper->widgetRenderer($config);
    }

    public function clearCacheAction()
    {
        //$caches = array();

//        foreach (Centurion_Config_Manager::get('resources.cachemanager') as $key => $value) {
//            Zend_Cache::_makeBackend($value['backend']['name'], $value['backend']['options'])->clean(Zend_Cache::CLEANING_MODE_ALL);
//        }
        Centurion_Signal::factory('clean_cache')->send($this);

        Centurion_Loader_PluginLoader::cleanCache(Centurion_Loader_PluginLoader::getIncludeFileCache());
        Centurion_Loader_PluginLoader::setStaticCachePlugin(null);
    }

    public function cacheAction()
    {

    }

    public function logAction()
    {
        $pageSize = 4096;
        $overlapSize = 128;

        $dir = APPLICATION_PATH . '/../data/logs/';

        $file = $this->_getParam('file', null);
        $this->view->page = $this->_getParam('page', 0);

        if ($file === null) {
            $file = sprintf('%s_application.log', Zend_Date::now()->toString('yyyy.MM.dd'));
        }

        $fp = fopen($dir . $file, 'r');
        fseek($fp, -$pageSize*($this->view->page+1) + $overlapSize, SEEK_END);
        $this->view->errorLog = fread($fp, $pageSize+$overlapSize*2);

        fclose($fp);

        $iterator = new DirectoryIterator($dir);

        while ($iterator->valid()) {
            if (!$iterator->isDot()) {
                if ($iterator->isFile()) {
                    $files[$iterator->getFilename()] = $iterator->getPathName();
                }
            }

            $iterator->next();
        }

        $this->view->itemCountPerPage = $pageSize;
        $this->view->totalItemCount = filesize($dir . $file);
        $this->view->files = $files;
    }
}
