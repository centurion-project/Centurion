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
class Auth_Model_DbTable_Permission extends Centurion_Db_Table_Abstract
{
    protected $_name = 'auth_permission';
    
    protected $_primary = 'id';
    
    protected $_meta = array('verboseName'   => 'permission',
                             'verbosePlural' => 'permissions');
    
    protected $_rowClass = 'Auth_Model_DbTable_Row_Permission';
    
    protected $_dependentTables = array(
        'user_permissions'    =>  'Auth_Model_DbTable_UserPermission',
        'group_permissions'   =>  'Auth_Model_DbTable_GroupPermission'
    );
    
    protected $_manyDependentTables = array(
        'users'        =>  array(
            'refTableClass'     =>  'Auth_Model_DbTable_User', 
            'intersectionTable' =>  'Auth_Model_DbTable_UserPermission',
            'columns'   =>  array(
                'local'         =>  'permission_id',
                'foreign'       =>  'user_id'
            )
        ),
        'groups'        =>  array(
            'refTableClass'     =>  'Auth_Model_DbTable_Group', 
            'intersectionTable' =>  'Auth_Model_DbTable_GroupPermission',
            'columns'   =>  array(
                'local'         =>  'permission_id',
                'foreign'       =>  'group_id'
            )
        )
    );
}