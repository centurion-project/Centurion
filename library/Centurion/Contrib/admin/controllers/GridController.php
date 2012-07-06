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
 * @subpackage  Admin
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Admin
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Admin_GridController extends Centurion_Controller_CRUD
{    
    public function init()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();
        
        $this->_formClassName = 'Auth_Form_Model_User';
        
        $this->_layout = 'media';
        
        $this->_displays = array(
            'id'        =>  'ID',
            'username'      =>  $this->view->translate('name'),
            'switch'    => array(
                            'type'   => self::COL_TYPE_ONOFF,
                            'column' => 'is_active',
                            'label' => $this->view->translate('Is active'),
                            'onoffLabel' => array($this->view->translate('Active'), $this->view->translate('Not active')),
                        ),
        );
        
        //TODO: behaviour => behavior
        
        $this->_filters = array(
            'username'      =>  array('type' =>  self::FILTER_TYPE_TEXT,
                                      'behavior'=>  self::FILTER_BEHAVIOR_CONTAINS,
                                      'label'   =>  $this->view->translate('Username')),
            'is_active'     =>  array('type'    =>  self::FILTER_TYPE_RADIO,
                                      'label'   =>  $this->view->translate('Status'),
                                      'data'    =>  array($this->view->translate('Yes') => 1,
                                                          $this->view->translate('No')  => 0)),
            'between'       => array('type'     => self::FILTER_TYPE_BETWEEN_DATE,
                                     'column'   => 'created_at',
                                     'label'    => $this->view->translate('date')),
            );
        
        $this->view->placeholder('headling_1_content')->set($this->view->translate('Manage users'));
        
        parent::init();
    }
}
