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
 * @package     Centurion_Model
 * @subpackage  DbTable
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Model
 * @subpackage  DbTable
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Core_Model_DbTable_Row_Navigation extends Centurion_Db_Table_Row_Abstract implements Core_Traits_Mptt_Model_DbTable_Row_Interface, Translation_Traits_Model_DbTable_Row_Interface
{
    protected $_proxy = null;

    public function init()
    {
        $this->_specialGets['proxy'] = 'getProxy';
        $this->_specialGets['is_published'] = 'isPublished';
        $this->_specialGets['published_at'] = 'getPublishedAt';
        $this->_specialGets['title'] = '__toString';

        parent::init();
    }

    public function __toString()
    {
        Centurion_Db_Table_Abstract::setFiltersStatus(true);
        if ($this->getProxy() !== null) {
            Centurion_Db_Table_Abstract::restoreFiltersStatus();
            return $this->getProxy()->__toString();
        }
        Centurion_Db_Table_Abstract::restoreFiltersStatus();
        return $this->label;
    }

    public function getLabel()
    {
        return $this->__toString();
    }

    public function getPublishedAt()
    {
        if ($this->getProxy() !== null) {
            return $this->getProxy()->getPublishedAt();
        }
        return null;
    }

    public function setis_published($isPublished)
    {
        $this->is_visible = $isPublished;
        $proxy = $this->getProxy();

        if ($proxy !== null) {
            $proxy->setPublished($isPublished);
        }
    }

    public function isPublished()
    {
        $isPublished = $this->is_visible;
        $proxy = $this->getProxy();

        if ($proxy !== null) {
            $isPublished &= $proxy->isPublished();
        }
        return (string) $isPublished;
    }

    public function isVisible($identity = null)
    {
        if (null !== $identity) {
            if (!$this->isAllowed($identity)) {
                return false;
            }
        }
        if (!$this->isPublished()) {
            return false;
        }

        if (null !== $this->getProxy() && !$this->getProxy()->isVisible($identity)) {
            return false;
        }

        return true;
    }

    public function getProxy()
    {
        if (null === $this->_proxy && null !==  $this->proxy_pk) {
            $proxyTable = Centurion_Db::getSingletonByClassName($this->model->name);
            $tableName = $proxyTable->info(Centurion_Db_Table_Abstract::NAME);
            $this->_proxy = $proxyTable->select(true)->where($tableName.'.id=?', $this->proxy_pk)->fetchRow();

            //Reverse binding
            if (null !== $this->_proxy && method_exists($this->_proxy, 'setNavigation'))
                $this->_proxy->setNavigation($this);
        }

        return $this->_proxy;
    }

    /**
     * Allows pre-delete logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _delete()
    {
        if ($this->can_be_deleted == '0') {
            throw new Centurion_Db_Table_Row_Exception('This row can not be deleted');
        }
        parent::_delete();
    }

    public function isAllowed($identity)
    {
        $permission = $this->getPermission();

        if (null !== $permission) {
            try {
                return $identity->isAllowed($permission);
            } catch (Exception $e) {
                    return false;
            }
        }
        return true;
    }

    public function getNavigationData($identity = null)
    {
        Centurion_Cache_TagManager::addTag($this);
        if (null !== $this->getProxy()) {
            Centurion_Cache_TagManager::addTag($this->getProxy());
        }

        if (!$this->isVisible($identity)) {
            return null;
        }

        $navigationData = array();
        $navigationData['id'] = $this->id;
        $navigationData['visible'] = $this->is_visible;
        $navigationData['label'] = $this->getLabel();
        $navigationData['class'] = $this->class;

        if (trim($navigationData['label']) == '')
            return null;

        $navigationData['permission'] = $this->getPermission();
        $navigationData += $this->getRouteData($identity);

        return $navigationData;
    }

    public function getPermission()
    {
        if (empty($this->permission)) {
            if (!empty($this->module) || !empty($this->action) || !empty($this->controller) || !empty($this->params)) {
                    return sprintf('%s_%s_%s', $this->module, $this->controller, empty($this->action) ? 'index' : $this->action);
            }
            return null;
        }
        return $this->permission;
    }

    protected function _getAbsoluteUrl($urlParam = null)
    {
        $navigationData = $this->getRouteData();
        if (isset($navigationData['uri'])) {
            return $navigationData['uri'];
        }

        if (!isset($navigationData['route'])) {
            $route = 'default';
        } else {
            $route = $navigationData['route'];
            unset($navigationData['route']);
        }

        return Zend_Controller_Front::getInstance()->getRouter()->assemble($navigationData, $route, true);
    }

    public function getRouteData($identity = null)
    {
        $navigationData = array();

        if (null !== $this->proxy) {
            //Proxy navigation
            if ($this->is_visible) {
                $navigationData['uri'] = $this->proxy->permalink;

                if ($navigationData['uri'] == null) {
//                    $found = false;
//                    $childrens = $this->getChildren();
//                    if (null !== $childrens) {
//                        foreach ($this->getChildren() as $children) {
//                            if (!$children->isVisible($identity))
//                                continue;
//                            $childrenNavigationData = $children->getRouteData($identity);
//
//                            if (null !== $childrenNavigationData && (!isset($childrenNavigationData['uri']) || $childrenNavigationData['uri'] !== '#')) {
//                                $navigationData = array_merge($navigationData, $childrenNavigationData);
//                                $found = true;
//                                break;
//                            }
//                        }
//                    }
//
//                    if (!$found || $navigationData['uri'] === null) {
//                        $navigationData['uri'] = '#';
//                    }
                    $navigationData['uri'] = '#';
                }

            } else {
                $navigationData['uri'] = '#';
            }
            $navigationData['proxy'] = $this->proxy;
        } else if (null !== $this->uri) {
            //Redirect navigation
            $navigationData['uri'] = $this->uri;
        } elseif (!empty($this->module) || !empty($this->action) || !empty($this->controller) || !empty($this->params) || !empty($this->route)) {
            if ($this->route === null) {
                $navigationData['route'] = 'default';
            } else {
                $navigationData['route'] = $this->route;
            }

            $navigationData['module'] = $this->module;
            $navigationData['action'] = $this->action;
            $navigationData['controller'] = $this->controller;
            if (null !== $this->params) {
                $navigationData['params'] = Zend_Json::decode($this->params);
            } else {
                $navigationData['params'] = null;
            }
        } else {
            $found = false;
            $childrens = $this->getChildren();
            if (null !== $childrens) {
                foreach ($this->getChildren() as $children) {
                    if (!$children->isVisible($identity))
                        continue;
                    $childrenNavigationData = $children->getRouteData($identity);
                    if (null !== $childrenNavigationData && (!isset($childrenNavigationData['uri']) || $childrenNavigationData['uri'] !== '#')) {
                        $navigationData += $childrenNavigationData;
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                $navigationData['uri'] = '#';
            }
        }

        return $navigationData;
    }
}