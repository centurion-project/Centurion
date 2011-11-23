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
 * @subpackage  Resource
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Application
 * @subpackage  Resource
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Application_Resource_Navigation extends Zend_Application_Resource_ResourceAbstract
{
    const DEFAULT_REGISTRY_KEY = 'Zend_Navigation';

    /**
     * @var Zend_Navigation
     */
    protected $_navigation = null;

    /**
     * Defined by Zend_Application_Resource_Resource.
     *
     * @return Zend_Navigation
     */
    public function init()
    {
        //$this->_bootstrap->bootstrap('modules');
        //return $this->getNavigation();
    }

    /**
     * Retrieve navigation object.
     *
     * @return Zend_Navigation
     */
    public function getNavigation()
    {
        if (null === $this->_navigation) {
            $options = $this->getOptions();
            $this->_navigation = new Zend_Navigation(Centurion_Db::getSingletonByClassName($options['adapter'])->getCache()->toNavigation());

            $key = (isset($options['registry_key']) && !is_numeric($options['registry_key']))
                 ? $options['registry_key']
                 : self::DEFAULT_REGISTRY_KEY;
            Zend_Registry::set($key, $this->_navigation);
        }
        return $this->_navigation;
    }
}