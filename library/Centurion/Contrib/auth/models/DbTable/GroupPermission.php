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
class Auth_Model_DbTable_GroupPermission extends Centurion_Db_Table_Abstract
{
    protected $_name = 'auth_group_permission';
    
    protected $_primary = array('group_id', 'permission_id');
    
    protected $_meta = array('verboseName'   => 'group_permission',
                             'verbosePlural' => 'group_permissions');
    
    protected $_rowClass = 'Auth_Model_DbTable_Row_GroupPermission';
    
    protected $_referenceMap = array(
        'group'         =>  array(
            'columns'       => 'group_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Auth_Model_DbTable_Group',
            'onDelete'      => self::CASCADE,
            'onUpdate'      => self::RESTRICT
        ),
        'permission'    =>  array(
            'columns'       => 'permission_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Auth_Model_DbTable_Permission',
            'onDelete'      => self::CASCADE,
            'onUpdate'      => self::RESTRICT
        )
    );
}