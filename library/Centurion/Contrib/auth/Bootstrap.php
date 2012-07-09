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
 * @subpackage  Auth
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Auth
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Auth_Bootstrap extends Centurion_Application_Module_Bootstrap
{
    /**
     * @deprecated
     */
    protected $_roles = array('users'  => array('dbtable'        =>  'Auth_Model_DbTable_User',
                                                'rows'                  =>  array(),
                                                'identColumn'           =>  'username',
                                                'parentColumn'          =>  'user_parent_id',
                                                'manyDependentTable'    => 'permissions'),
                              'groups'  => array('dbtable'       =>  'Auth_Model_DbTable_Group',
                                                 'rows'                 =>  array(),
                                                 'identColumn'          =>  'name',
                                                 'parentColumn'         =>  'group_parent_id',
                                                 'manyDependentTable'   => 'permissions'));

    protected $_acl = null;

    protected $_resources = array();

    /**
     * Proxy function in order to make _initAcl() callable in test mode.
     */
    public function testInitAcl()
    {
        $this->_acl = null;
        return $this->_initAcl();
    }

    protected function _initAcl()
    {
        $bootstrap = $this->getApplication();
        $bootstrap->bootstrap('cachemanager');
        $cache = $bootstrap->getResource('cachemanager')->getCache('core');

        if (!($acl = $cache->load('Centurion_Acl'))) {
            $acl = $this->_getAcl();

            $cache->save($acl, 'Centurion_Acl', array('user', 'permission', 'group', 'belong', 'acl',
                                                     '__auth_user', '__auth_belong', '__auth_permission', '__auth_group_permission',
                                                      '__auth_user_permission'));
        }

        Zend_Registry::set('Centurion_Acl', $acl);
    }

    /**
     * @deprecated
     */
    protected function _addRole($key, $role)
    {
        $parents = array();
        $identColumn = $this->_roles[$key]['identColumn'];
        $parentColumn = $this->_roles[$key]['parentColumn'];

        if (null !== $role[$parentColumn]) {
            $parentRole = $this->_roles[$key]['rows'][$role[$parentColumn]];
            $this->_addRole($key, $parentRole);
            array_push($parents, $this->_getAcl()->getRole($parentRole[$identColumn]));
        }

        if (!$this->_getAcl()->hasRole($role[$identColumn])) {
            $this->_getAcl()->addRole(new Zend_Acl_Role($role[$identColumn]), $parents);
        }
    }

    protected function _getAcl()
    {
        if (null === $this->_acl) {
            $this->_acl = new Auth_Model_Acl();
        }

        return $this->_acl;
    }
}
