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
class Auth_Model_DbTable_User extends Centurion_Db_Table_Abstract
{
    protected $_name = 'auth_user';

    protected $_primary = 'id';

    protected $_rowClass = 'Auth_Model_DbTable_Row_User';

    protected $_meta = array('verboseName'   => 'user',
                             'verbosePlural' => 'users');

    protected $_referenceMap = array(
        'user_parent'   =>  array(
            'columns'       => 'user_parent_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Auth_Model_DbTable_User',
            'onDelete'      =>  self::SET_NULL,
            'onUpdate'      =>  self::CASCADE,
        ),
         'profile'   =>  array(
             'columns'       => 'id',
             'refColumns'    => 'user_id',
             'refTableClass' => 'User_Model_DbTable_Profile',
         )
    );

    protected $_dependentTables = array(
        'users'            =>  'Auth_Model_DbTable_User',
        'profiles'         =>  'User_Model_DbTable_Profile',
        'belongs'          =>  'Auth_Model_DbTable_Belong',
        'user_permissions' =>  'Auth_Model_DbTable_UserPermission',
    );

    protected $_manyDependentTables = array(
        'groups'        =>  array(
            'refTableClass'     =>  'Auth_Model_DbTable_Group',
            'intersectionTable' =>  'Auth_Model_DbTable_Belong',
            'columns'   =>  array(
                'local'     =>  'user_id',
                'foreign'   =>  'group_id'
            )
        ),
        'permissions'        =>  array(
            'refTableClass'     =>  'Auth_Model_DbTable_Permission',
            'intersectionTable' =>  'Auth_Model_DbTable_UserPermission',
            'columns'   =>  array(
                'local'     =>  'user_id',
                'foreign'   =>  'permission_id'
            )
        )
    );

    /**
     * Make a random password.
     *
     * @param string $length Length of the password
     * @param string $allowedChars Allowed characters to generate a random password
     * @return string The password generated
     */
    public static function makeRandomPassword($length = 10, $allowedChars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789')
    {
        $password = '';
        for ($i = 0; $i < $length; ++$i)
            $password .= $allowedChars[rand(0, strlen($allowedChars) - 1)];

        return $password;
    }
}