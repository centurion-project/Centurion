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
 * @subpackage  Cms
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Cms_Bootstrap extends Centurion_Application_Module_Bootstrap
{
    const CMS_FLATPAGE_ROW = 'Cms_Model_DbTable_Row_Flatpage';

    protected $_routePrefix = '';

    protected function _initSignal()
    {
        Centurion_Signal::factory('post_update')->connect(array($this, 'postUpdate'), self::CMS_FLATPAGE_ROW, Centurion_Signal::BEHAVIOR_CONTINUE, Centurion_Signal_Abstract::UNSHIFT);

        Centurion_Signal::factory('post_save')->connect(array($this, 'postSave'), self::CMS_FLATPAGE_ROW);
        Centurion_Signal::factory('pre_delete')->connect(array($this, 'deleteRow'), self::CMS_FLATPAGE_ROW);
        Centurion_Signal::factory('pre_delete')->connect(array($this, 'deleteRowNavigation'), 'Core_Model_DbTable_Row_Navigation');
        Centurion_Signal::factory('post_save')->connect(array($this, 'saveNavigation'), 'Core_Model_DbTable_Row_Navigation');
    }

    public function saveNavigation($signal, Centurion_Db_Table_Row_Abstract $sender)
    {
        $navigationTable = Centurion_Db::getSingleton('core/navigation');

        if ($sender->proxy !== null && $sender->proxy instanceof CMS_FLATPAGE_ROW) {
            $sender->proxy->is_published = $sender->is_visible;
            $sender->setReadOnly(false);
            $sender->proxy->save();
        }
    }

    public function deleteRowNavigation($signal, Centurion_Db_Table_Row_Abstract $sender)
    {
        if (null !== $sender->getProxy() && get_class($sender->getProxy()) == self::CMS_FLATPAGE_ROW)
            $sender->getProxy()->delete();
        return;
        
        $navigationTable = Centurion_Db::getSingleton('core/navigation');
        list($contentType, ) = Centurion_Db::getSingleton('core/contentType')->getOrCreate(array('name' => self::CMS_FLATPAGE_ROW));

        if ($sender->proxy_model === $contentType->id && $sender->proxy !== null) {

            $row = $navigationTable->createRow(array(
                    'proxy_model' => $contentType->id,
                    'proxy_pk'    => $sender->id,
                    'label'       => $sender->label,
                    'is_visible'  => 0,
                ));

            $node = $navigationTable->fetchRow(array('class=?' => 'unactived'));
            $row->insertAt($node, Core_Traits_Mptt_Model_DbTable::POSITION_LAST_CHILD, true);
        }
    }

    public function deleteRow($signal, Centurion_Db_Table_Row_Abstract $sender)
    {
        $navigationTable = Centurion_Db::getSingleton('core/navigation');
        list($contentType, ) = Centurion_Db::getSingleton('core/contentType')->getOrCreate(array('name' => get_class($sender)));

        $data = array(
            'proxy_model=?' => $contentType->id,
            'proxy_pk=?' =>  $sender->id,
        );

        $rowSet = $navigationTable->fetchAll($data);

        foreach ($rowSet as $row) {
            $row->delete();
        }
    }

    public function postSave($signal, Centurion_Db_Table_Row_Abstract $row)
    {
        $application = $this->getApplication();
        $router = $application->getResource('router');

        $routeName = sprintf('%sflatpage_%d', $this->_routePrefix, $row->id);
        $hasRoute = $router->hasRoute($routeName);

        if ($row->is_published) {
            if (!$hasRoute) {
                $router->addRoute($routeName, self::_getRoute($row));
            } else {
                $route = $router->getRoute($routeName);
                if ($route instanceof Centurion_Controller_Router_Route_Static)
                    $route->setRoute($row->url);
            }
        } else {
            if ($hasRoute) {
                $router->removeRoute($routeName);
            }
        }

        return;
        $navigationTable  = Centurion_Db::getSingleton('core/navigation');
        list($contentType, ) = Centurion_Db::getSingleton('core/contentType')->getOrCreate(array('name' => get_class($sender)));

        $data = array(
            'proxy_model=?' => $contentType->id,
            'proxy_pk=?' =>  $sender->id,
        );

        $row = $navigationTable->fetchRow($data);

        if ($row === null) {
            $row = $navigationTable->createRow(array(
                    'proxy_model' => $contentType->id,
                    'proxy_pk'    => $sender->id,
                    'label'       => $sender->title,
                    'is_visible'  => 0,
                ));

            $node = $navigationTable->fetchRow(array('class=?' => 'unactived'));
            $row->insertAt($node, Core_Traits_Mptt_Model_DbTable::POSITION_LAST_CHILD, true);
        } else {
            $row->label = $sender->title;

            if ($sender->is_published == '0')
                $row->is_visible = $sender->is_published;
            $row->save();
        }
    }

    protected function _initFlatpages()
    {
        $this->_generateRoutes();
    }

    protected function _generateRoutes()
    {
        $this->bootstrap('FrontController');

        $application = $this->getApplication();
        $application->bootstrap('router');
        $router = $application->getResource('router');

        $baseRoute = null;
        $this->_routePrefix = '';

        if (false === ($data = $this->_getCache('core')->load('Cms_Flatpage_Route'))) {
             if (Zend_Registry::isRegistered('Centurion_Route_Queue')) {
                $routeQueue = Zend_Registry::get('Centurion_Route_Queue');

                $r = reset($routeQueue);
                if (is_array($r)) {
                    $this->_routePrefix .= $r['name'] . '-';
                    $r = $r['route'];
                }
                while ($rn = next($routeQueue)) {
                    if (is_array($rn)) {
                        $this->_routePrefix .= $rn['name'] . '-';
                        $r = $r->chain($rn['route']);
                    }
                    else {
                        $r = $r->chain($rn);
                    }
                }
                $baseRoute = $r;

            }

            $routes = array();
            $flatpageModel = Centurion_Db::getSingleton('cms/flatpage');

            Centurion_Db_Table_Abstract::setFiltersStatus(false);
            $flatpageRowset = $flatpageModel->select(true)->filter(array('is_published'          =>  1,
                                                                         'published_at__lt'      =>  new Zend_Db_Expr('NOW()')))->fetchAll();
            Centurion_Db_Table_Abstract::restoreFiltersStatus(true);

            foreach ($flatpageRowset as $key => $flatpageRow) {
                if ($flatpageRow->flatpage_type == Cms_Model_DbTable_Flatpage::NORMAL && trim($flatpageRow->url) !== '') {
                    $route = $flatpageRow->_getRoute();
                    $routes[sprintf('%sflatpage_%d', $this->_routePrefix, $flatpageRow->id)] = $route;
                }
            }

            $this->_getCache('core')->save(array($this->_routePrefix, $routes, $baseRoute), 'Cms_Flatpage_Route');
        } else {
            $this->_routePrefix = $data[0];
            $routes = $data[1];
            $baseRoute = $data[2];
        }

        Zend_Registry::set('ROUTE_PREFIX', $this->_routePrefix);

        foreach ($routes as $name => $route) {
            if (null !== $baseRoute) {
                $route = $baseRoute->chain($route);
            }
            $router->addRoute($name, $route);
        }
    }

    public function postUpdate($signal, Centurion_Db_Table_Row_Abstract $row)
    {
//        $application = $this->getApplication();
//        $router = $application->getResource('router');
//
//        $routeName = sprintf('%sflatpage_%d', $this->_routePrefix, $row->id);
//        $hasRoute = $router->hasRoute($routeName);
//
//        if ($row->is_published) {
//            if (!$hasRoute) {
//                $router->addRoute($routeName, $this->_getRoute($row));
//            } else {
//                $route = $router->getRoute($routeName);
//                if ($route instanceof Centurion_Controller_Router_Route_Static)
//                    $route->setRoute($row->url);
//            }
//        } else {
//            if ($hasRoute) {
//                $router->removeRoute($routeName);
//            }
//        }
    }

    static protected function _getRoute(Cms_Model_DbTable_Row_Flatpage $row)
    {
        return $row->_getRoute();
    }
}
