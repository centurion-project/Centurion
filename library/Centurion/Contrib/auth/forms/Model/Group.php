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
class Auth_Form_Model_Group extends Centurion_Form_Model_Abstract
{
    public function __construct($options = array(), Centurion_Db_Table_Row_Abstract $instance = null)
    {
        $this->_model = Centurion_Db::getSingleton('auth/group');
        $this->_elementLabels = array(
            'name'              =>  $this->_translate('Name'),
            'description'       =>  $this->_translate('Description'),
            'group_parent_id'   =>  $this->_translate('Group parent'),
            'users'             =>  $this->_translate('Users'),
            'permissions'       =>  $this->_translate('Permissions')
        );
        
        parent::__construct($options, $instance);
    }
    
    public function init()
    {
        parent::init();

        $this->getElement('description')->setAttrib('class', 'field-rte')
                                        ->setAttrib('large', true)
                                        ->removeFilter('StripTags');
    }
}