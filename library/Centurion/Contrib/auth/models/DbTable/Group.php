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
class Auth_Model_DbTable_Group extends Centurion_Db_Table_Abstract
{
    protected $_name = 'auth_group';
    
    protected $_primary = 'id';
    
    protected $_meta = array('verboseName'   => 'group',
                             'verbosePlural' => 'groups');
    
    protected $_rowClass = 'Auth_Model_DbTable_Row_Group';
    
    protected $_referenceMap = array(
        'parent_group'   =>  array(
            'columns'       => 'group_parent_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Auth_Model_DbTable_Group',
            'onDelete'      =>  self::SET_NULL
        )
    );  
    
    protected $_dependentTables = array(
        'belongs'            =>  'Auth_Model_DbTable_Belong',
        'group_permissions'  =>  'Auth_Model_DbTable_GroupPermission',
        'groups'             =>  'Auth_Model_DbTable_Group',
    );
    
    protected $_manyDependentTables = array(
        'users'        =>  array(
            'refTableClass'     =>  'Auth_Model_DbTable_User', 
            'intersectionTable' =>  'Auth_Model_DbTable_Belong',
            'columns'   =>  array(
                'local'         =>  'group_id',
                'foreign'       =>  'user_id'
            )
        ),
        'permissions'        =>  array(
            'refTableClass'     =>  'Auth_Model_DbTable_Permission', 
            'intersectionTable' =>  'Auth_Model_DbTable_GroupPermission',
            'columns'        =>  array(
                'local'         =>  'group_id',
                'foreign'       =>  'permission_id'
            )
        )
    );
}