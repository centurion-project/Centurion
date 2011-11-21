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
class Auth_Model_DbTable_Row_User extends Centurion_Db_Table_Row_Abstract
{
    
    const DEFAULT_ALGORITHM = 'sha1';

    /**
     * Permissions stack.
     *
     * @var array
     */
    protected $_permissions = array();

    /**
     * Profile instance attached to the user instance.
     *
     * @var Centurion_Db_Table_Row_Abstract
     */
    protected $_profile = null;

    public function __toString()
    {
        return $this->username;
    }

    /**
     * Check if the user has the permission.
     *
     * @param string $permission Permission name
     * @return boolean True if the user has the permission otherwise false
     */
    public function isAllowed($permission)
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->is_super_admin) {
            return true;
        }

        if (!is_string($permission)) {
            $permission = (string) $permission;
        }

        if (Zend_Registry::get('Centurion_Acl')->isAllowed($this, $permission)) {
            return true;
        }

        if (!array_key_exists($permission, $this->_permissions)) {
            $result = false;

            foreach ($this->groups as $groupRow) {
                try {
                    $result = $groupRow->isAllowed($permission);

                    if ($result)
                        break;
                } catch (Zend_Acl_Exception $e) {
                    throw $e;
                    $result = false;
                }
            }

            $this->_permissions[$permission] = $result;
        }

        return $this->_permissions[$permission];
    }

    /**
     * Proxy method for isAllowed([perm]).
     *
     * @param string $permission Permission name
     * @return boolean True if the user has the permission otherwise false
     */
    public function hasPerm($permission)
    {
        return $this->isAllowed($permission);
    }

    /**
     * Set the current password.
     *
     * @param string $password Password string
     * @return void
     */
    public function setPassword($password)
    {
        if (null === $this->salt) {
          $salt = md5(rand(100000, 999999). $this->username);
          $this->salt = $salt;
        }

        $algorithm = isset($this->algorithm) && '' != trim($this->algorithm) ? $this->algorithm : self::DEFAULT_ALGORITHM;

        $algorithmAsStr = is_array($algorithm) ? $algorithm[0] . '::' . $algorithm[1] : $algorithm;

        if (!is_callable($algorithm)) {
            throw new Centurion_Exception(sprintf('The algorithm callable "%s" is not callable.', $algorithmAsStr));
        }

        $this->algorithm = $algorithmAsStr;

        $this->_data['password'] = call_user_func_array($algorithm, array($this->salt . $password));
        $this->_modifiedFields['password'] = true;
    }

    /**
     *  Returns True if the user has each of the specified permissions.
     *  If object is passed, it checks if the user has all required perms
     *  for this object.
     *
     * @param array $permissions Permission list
     * @return boolean True if the user has each of the specified permissions otherwise false
     */
    public function hasPerms($permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPerm($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieve the profile attached to the user instance.
     *
     * @return Centurion_Db_Table_Row_Abstract
     */
    public function getProfile()
    {
        if (null === $this->_profile) {
            $options = Centurion_Config_Manager::get('centurion');

            if (!isset($options['auth_profile'])) {
                throw new Centurion_Exception('No site profile module available: you have to set a centurion.auth_profile in your application.ini');
            }

            if (!class_exists($options['auth_profile'])) {
                throw new Centurion_Exception('The class in centurion.auth_profile option does not exists.');
            }

            $this->_profile = Centurion_Db::getSingletonByClassName($options['auth_profile'])->findOneByUserId($this->pk);
        }

        return $this->_profile;
    }

    /**
     * 
     * Get a random password
     * @param int $length the length of the password wanted
     * @return string a random password
     */
    public function generePassword($length = 10) {
        $list = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $newstring = '';
        $max = strlen($list)-1;
        while( strlen($newstring) < $length ) {
            $newstring .= $list[mt_rand(0, $max)];
        }

        return $newstring;
    }
}