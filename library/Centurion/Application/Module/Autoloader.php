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
 * @subpackage  Module
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Application
 * @subpackage  Module
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Application_Module_Autoloader extends Zend_Application_Module_Autoloader
{
    /**
     * Initialize default resource types for module resource classes.
     * @return void
     */
    public function initDefaultResourceTypes()
    {
        parent::initDefaultResourceTypes();

        $this->addResourceTypes(array(
            'controllershelpers' => array(
                'namespace' => 'Controller_Action_Helper',
                'path'      => 'controllers/helpers',
            ),
            'signals' => array(
                'namespace' => 'Signal',
                'path'      => 'signals',
            ),
            'traits' => array(
                'namespace' => 'Traits',
                'path'      => 'traits',
            ),
            'routes' => array(
                'namespace' => 'Route',
                'path'      => 'routes',
            ),
            'tests' => array(
                'namespace' => 'Test',
                'path'      => 'tests',
            ),
        ));
    }

    /**
     * @param $key
     * @return bool|mixed
     */
    public function getComponent($key)
    {
        if (isset($this->_components[$key])) {
            return $this->_components[$key];
        }

        return false;
    }

    /**
     * Retrieve a resource type with a type.
     *
     * @param string $type  Key of resource type
     * @return array|false  Result or false if no resource type found.
     */
    public function getResourceType($type)
    {
        if ($this->hasResourceType($type)) {
            return $this->_resourceTypes[$type];
        }

        return false;
    }
}
