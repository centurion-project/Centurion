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
class Auth_Model_DbTable_Row_Group extends Centurion_Db_Table_Row_Abstract
{
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Check if the group has the permission.
     *
     * @param string $permission Permission name
     * @return boolean True if the group has the permission otherwise false
     */
    public function isAllowed($permission)
    {
        return Zend_Registry::get('Centurion_Acl')->isAllowed($this, $permission);
    }

    /**
     * Proxy method for isAllowed([perm]).
     *
     * @param string $permission Permission name
     * @return boolean True if the group has the permission otherwise false
     */
    public function hasPerm($permission)
    {
        return $this->isAllowed($permission);
    }

    /**
     *  Returns True if the user has each of the specified permissions.
     *  If object is passed, it checks if the user has all required perms
     *  for this object.
     *
     * @param array $permissions
     * @return boolean True if the group has each of the specified permissions otherwise false
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
}
