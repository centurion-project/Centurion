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
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Auth_Model_Acl extends Zend_Acl
{
    protected $_roles = array('Auth_Model_DbTable_Row_User'  => array(
                                                'dbtable'        =>  'Auth_Model_DbTable_User',
                                                'rows'                  => array(),
                                                'identityColumn'           => 'username',
                                                'parentColumn'          => 'user_parent',
                                                'manyDependentTable'    => 'permissions'
                                    ),
                              'Auth_Model_DbTable_Row_Group'  => array(
                                                 'dbtable'       =>  'Auth_Model_DbTable_Group',
                                                 'rows'                 =>  array(),
                                                 'identityColumn'          =>  'name',
                                                 'parentColumn'         =>  'parent_group',
                                                 'manyDependentTable'   => 'permissions'
                              )
    );

    protected $_loaded = array();

    /**
     *
     * @param string $role
     * @param string $key
     * @return bool
     */
    public function isLoaded($role, $key)
    {
        return isset($this->_loaded[$key][$role]);
    }

    /**
     * @param Zend_Acl_Resource_Interface|string $resource
     * @return bool
     */
    public function isLoadedResource($resource)
    {
        return $this->has($resource);
        //return isset($this->_loaded['Auth_Model_DbTable_Permission'][$resource]);
    }

    /**
     * @param string $resource
     */
    public function loadResource($resource)
    {
        $this->_loaded['Auth_Model_DbTable_Permission'][$resource] = $resource;
        $this->addResource(new Zend_Acl_Resource($resource));
    }

    public function isAllowed($role = null, $resource = null, $privilege = null)
    {
        $key = get_class($role);
        
        if (!isset($this->_roles[$key])) {
            throw new Zend_Acl_Exception(sprintf('The role class %s, can not be used in ACL', $key));
        }
        $resourceString = (string) $resource;
        if (!$this->isLoadedResource($resourceString)) {
            $this->loadResource($resourceString);
        }
        $roleName = $role->{$this->_roles[$key]['identityColumn']};

        if (!$this->isLoaded($roleName, $key)) {
            $this->load($role, $key);
        }

        return parent::isAllowed($role, $resource, $privilege);
    }

    /**
     * @param Centurion_Db_Table_Row_Abstract $resource
     * @param string $key One of key in $this->$_roles
     */
    public function load($resource, $key)
    {
        if (!isset($this->_roles[$key])) {
            throw new Zend_Acl_Exception('An error occurred');
        }

        if (!isset($this->_loaded[$key])) {
            $this->_loaded[$key] = array();
        }

        $column = $resource->{$this->_roles[$key]['identityColumn']};
        $this->_loaded[$key][$column] = true;

        $this->_addRole($key, $resource);

        foreach ($resource->{$this->_roles[$key]['manyDependentTable']} as $permissions) {
            if (!$this->isLoadedResource($permissions->name)) {
                $this->loadResource($permissions->name);
            }
            $this->allow($resource->{$this->_roles[$key]['identityColumn']}, $permissions->name);
        }
    }

    /**
     * @param string $key One of key in $this->$_roles
     * @param Centurion_Db_Table_Row_Abstract $role
     */
    protected function _addRole($key, $role)
    {
        if (!isset($this->_roles[$key])) {
            throw new Zend_Acl_Exception('An error occurred');
        }

        $parents = array();
        $identityColumn = $this->_roles[$key]['identityColumn'];
        $parentColumn = $this->_roles[$key]['parentColumn'];

        if (null !== $role->$parentColumn) {
            if (!$this->isLoaded($role->$parentColumn->{$identityColumn}, $key)) {
                $this->load($role->$parentColumn, $key);
            }
            $this->_addRole($key, $role->$parentColumn);
            array_push($parents, $this->getRole($role->$parentColumn->$identityColumn));
        }

        if (!$this->hasRole($role->$identityColumn)) {
            $this->addRole(new Zend_Acl_Role($role->$identityColumn), $parents);
        }
    }
}
