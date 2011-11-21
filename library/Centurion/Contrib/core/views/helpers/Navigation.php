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
 * @package     Centurion_View
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_View
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_View_Helper_Navigation extends Zend_View_Helper_Navigation
{
    const DEFAULT_REGISTRY_KEY = 'Zend_Navigation';

    public function getContainer()
    {
        if (null === $this->_container) {
            if (!Zend_Registry::isRegistered('Zend_Navigation')) {
                $this->_container = new Zend_Navigation(Centurion_Db::getSingleton('core/navigation')->getCache()->toNavigation());
            }
        }

        return parent::getContainer();
    }
    /**
     * View helper namespace
     *
     * @var string
     */
    const NS = 'Centurion_View_Helper_Navigation';

    public function findHelper($proxy, $strict = true)
    {
        if (isset($this->_helpers[$proxy])) {
            return $this->_helpers[$proxy];
        }

        if (!$this->view->getPluginLoader('helper')->getPaths(self::NS)) {
            $this->view->addHelperPath(
                    str_replace('_', '/', self::NS),
                    self::NS);
        }

        if ($strict) {
            $helper = $this->view->getHelper($proxy);
        } else {
            try {
                $helper = $this->view->getHelper($proxy);
            } catch (Zend_Loader_PluginLoader_Exception $e) {
                return null;
            }
        }

        if (!$helper instanceof Zend_View_Helper_Navigation_Helper) {
            if ($strict) {
                require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception(sprintf(
                        'Proxy helper "%s" is not an instance of ' .
                        'Zend_View_Helper_Navigation_Helper',
                        get_class($helper)));
                $e->setView($this->view);
                throw $e;
            }

            return null;
        }

        $this->_inject($helper);
        $this->_helpers[$proxy] = $helper;

        return $helper;
    }

    public function getParent($navigation, $level)
    {
        $pt = $navigation;

        if ($this->getLevel($pt) < $level)
            return null;

        while ($this->getLevel($pt) > $level && (null !== $pt->getParent())) {
            $pt = $pt->getParent();
        }

        return $pt;
    }

    public function getLevel($navigation)
    {
        $pt = $navigation;
        $level = 1;

        if (null === $pt)
            return 0;

        while (null !== ($pt = $pt->getParent())) {
            if (!($pt instanceof Zend_Navigation_Page)) {
                //Root node is an Zend_Navigation
                break;
            }
            $level++;
        }
        return $level;
    }

    public function getPrevNext($nav, $maxLevel = 0, $allowSameHref = true)
    {
        $parent = $nav;
        while ($this->getLevel($parent) > $maxLevel && (null !== $parent->getParent())) {
            $parent = $parent->getParent();
            if (!($parent instanceof Zend_Navigation_Page)) {
                //Root node is an Zend_Navigation
                break;
            }
        }
        $prev = null;
        $next = null;
        if ($parent !== null) {
            $iterator = new RecursiveIteratorIterator($parent,
                                RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $page) {
                if ($page == $nav) {
                    $iterator->next();
                    if ($iterator->valid())
                        $next = $iterator->current();
                    break;
                }
                $prev = $page;
            }

            if (null !== $prev && $prev->getHref() == $nav->getHref()) {
                $prev = $this->getPrevious($prev, $maxLevel, $allowSameHref);
            }
        }
        return array($prev, $next);
    }

    public function getNext($nav, $maxLevel = 0, $allowSameHref = true)
    {
        list(,$next) = $this->getPrevNext($nav, $maxLevel);
        return $next;
    }

    public function getPrevious($nav, $maxLevel = 0, $allowSameHref = true)
    {
        list($previous,) = $this->getPrevNext($nav, $maxLevel);
        return $previous;
    }

}
