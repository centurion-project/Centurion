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
 * @author      Laurent Chenay <lchenay@gmail.com>
 */
class Auth_Model_Acl extends Zend_Acl
{
    protected $_roles = array('Auth_Model_DbTable_Row_User'  => array('dbtable'        =>  'Auth_Model_DbTable_User',
                                                'rows'                  =>  array(),
                                                'identColumn'           =>  'username',
                                                'parentColumn'          =>  'parent_user',
                                                'manyDependentTable'    => 'permissions'),
                              'Auth_Model_DbTable_Row_Group'  => array('dbtable'       =>  'Auth_Model_DbTable_Group',
                                                 'rows'                 =>  array(),
                                                 'identColumn'          =>  'name',
                                                 'parentColumn'         =>  'parent_group',
                                                 'manyDependentTable'   => 'permissions'));

    protected $_loaded = array();

    public function isLoaded($role, $key)
    {
        return isset($this->_loaded[$key][$role]);
    }

    public function isLoadedRessource($ressource)
    {
        return $this->has($ressource);
        //return isset($this->_loaded['Auth_Model_DbTable_Permission'][$ressource]);
    }

    public function loadRessource($ressource)
    {
        $this->_loaded['Auth_Model_DbTable_Permission'][$ressource] = $ressource;
        $this->add(new Zend_Acl_Resource($ressource));
    }

    public function isAllowed($role = null, $resource = null, $privilege = null)
    {
        $key = get_class($role);

        if (!isset($this->_roles[$key])) {
            throw new Zend_Acl_Exception(sprintf('The role class %s, can not be used in ACL', $key));
        }
        $resourceString = (string) $resource;
        if (!$this->isLoadedRessource($resourceString)) {
            $this->loadRessource($resourceString);
        }
        $roleName = $role->{$this->_roles[$key]['identColumn']};

        if (!$this->isLoaded($roleName, $key)) {
            $this->load($role, $key);
        }

        return parent::isAllowed($role, $resource, $privilege);
    }

    public function load($ressource, $key)
    {
        if (!isset($this->_loaded[$key])) {
            $this->_loaded[$key] = array();
        }

        $column = $ressource->{$this->_roles[$key]['identColumn']};
        $this->_loaded[$key][$column] = true;

        $this->_addRole($key, $ressource);

        foreach ($ressource->{$this->_roles[$key]['manyDependentTable']} as $permissions) {
            if (!$this->isLoadedRessource($permissions->name)) {
                $this->loadRessource($permissions->name);
            }
            $this->allow($ressource->{$this->_roles[$key]['identColumn']}, $permissions->name);
        }
    }

    protected function _addRole($key, $role)
    {
        $parents = array();
        $identColumn = $this->_roles[$key]['identColumn'];
        $parentColumn = $this->_roles[$key]['parentColumn'];

        if (null !== $role->$parentColumn) {
            if (!$this->isLoaded($role->$parentColumn->{$identColumn}, $key)) {
                $this->load($role->$parentColumn, $key);
            }
            $this->_addRole($key, $role->$parentColumn);
            array_push($parents, $this->getRole($role->$parentColumn->$identColumn));
        }

        if (!$this->hasRole($role->$identColumn)) {
            $this->addRole(new Zend_Acl_Role($role->$identColumn), $parents);
        }
    }
}