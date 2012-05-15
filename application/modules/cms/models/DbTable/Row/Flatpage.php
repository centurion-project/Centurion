<?php

class Cms_Model_DbTable_Row_Flatpage extends Centurion_Db_Table_Row implements Translation_Traits_Model_DbTable_Row_Interface, Core_Traits_Mptt_Model_DbTable_Row_Interface, Core_Model_DbTable_Row_Navigable_Interface, Core_Traits_Slug_Model_DbTable_Row_Interface
{
    protected $_navigation = false;
    protected $_route = null;

    public function init()
    {
        parent::init();

        $this->_specialGets['level_title'] = 'getLevelTitle';
        $this->_specialGets['href'] = 'getHref';
        $this->_specialGets['is_visible'] = 'isVisible';
    }

    public function __toString()
    {
        return $this->title;
    }

    public function isVisible($identity = null)
    {
        if (!$this->isPublished())
            return false;
        return ($this->getDateObjectBy('published_at')->compare(Zend_Date::now()) < 0);
    }

    public function isPublished()
    {
        if (!$this->is_published)
            return false;
        return true;
    }

    public function getPublishedAt()
    {
        return $this->published_at;
    }

    public function setPublished($isPublished = true)
    {
        $this->is_published = $isPublished;
        $this->save();
    }

    public function getHref()
    {
        switch ($this->flatpage_type) {
            case Cms_Model_DbTable_Flatpage::NORMAL:
                    $router = Zend_Controller_Front::getInstance()->getRouter();
                    if (!$router->hasRoute(sprintf('%sflatpage_%d', Zend_Registry::get('ROUTE_PREFIX'), $this->pk))) {
                        Centurion_Signal::factory('clean_cache')->send($this);
                        return '/';
                    }

                    return $router->assemble(array('object' => $this), sprintf('%sflatpage_%d', Zend_Registry::get('ROUTE_PREFIX'), $this->pk), true);
                break;
            case Cms_Model_DbTable_Flatpage::REDIRECT:
                if (null !== $this->route) {
                    return Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), $this->route, true);
                } else {
                    return $this->forward_url;
                }
            case Cms_Model_DbTable_Flatpage::NAV_ONLY:
                $children = $this->getDescendantsSelect(false, 1)->filter('is_published = 1')->fetchRow();

                if (null == $children || !$children->count())
                    throw new Exception(sprintf('No child flatpage for id %s', $this->pk));

                return $children->href;
            case Cms_Model_DbTable_Flatpage::REDIRECT_BLANK:
                return $this->forward_url;
        }
    }

    public function _getRoute()
    {
        Centurion_Cache_TagManager::addTag($this);
        if (null === $this->_route) {
            $url = $this->url;
            $pt = Centurion_Db::getSingleton('core/navigation')->findOneByProxy($this);

            if (null !== $pt) {
                while ((null !== ($pt = $pt->getParent())) && $pt !== null && null != ($proxy = $pt->getProxy())) {
                    if (isset($proxy->url))
                        $url = $proxy->url . $url;
                    else if (isset($proxy->slug))
                        $url = '/' . $proxy->slug . $url;
                }
            }

            $this->_route = new Centurion_Controller_Router_Route_Static($url, array('controller'  =>  'flatpage',
                                                                                              'action'      =>  'get',
                                                                                              'module'      =>  'cms',
                                                                                              'id'          =>  $this->pk));
        }

        return $this->_route;
    }

    public function _getAbsoluteUrl($urlParam = null)
    {
        return $this->getHref();
    }

    public function getAbsoluteUrl()
    {
        return array(sprintf('flatpage_%d', $this->pk), array('object' => $this));
    }

    public function getLevelTitle()
    {
        return sprintf('<div style="padding-left: %dpx">%s</div>', $this->level * 10, $this->title);
    }

    public function getNumber()
    {
        return str_pad($this->order, 2, '0', STR_PAD_LEFT);
    }

    protected function _postSave()
    {
        parent::_postSave();

        foreach ($this->flatpages as $key => $flatpageRow) {
            $flatpageRow->save();
        }
    }
    public function getNavigation()
    {
        if (false === $this->_navigation) {
            $this->_navigation = Centurion_Db::getSingleton('core/navigation')->findOneByProxy($this);
        }

        return $this->_navigation;
    }
    public function setNavigation($navigation)
    {
        $this->_navigation = $navigation;
    }
    public function getSlugifyName()
    {
        return 'title';
    }
}
