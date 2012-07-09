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
 * @package     Centurion_Form
 * @subpackage  Model
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Form
 * @subpackage  Model
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Nicolas Duteil <nd@octaveoctave.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Admin_Form_Model_Navigation extends Centurion_Form_Model_Abstract implements Translation_Traits_Form_Model_Interface
{
    /**
     * Constructor
     *
     * @param   array|Zend_Config           $options    Options
     * @param   Centurion_Db_Table_Abstract $instance   Instance attached to the form
     * @return void
     */
    public function __construct($options = array(), Centurion_Db_Table_Row_Abstract $instance = null)
    {
        $this->_model = Centurion_Db::getSingleton('core/navigation');

        $this->_exclude = array('mptt_parent_id');

        $this->_elementLabels = array(
            'label'                 => $this->_translate('Label'),
            'module'                => $this->_translate('Module name'),
            'controller'            => $this->_translate('Controller name'),
            'action'                => $this->_translate('Action name'),
            'params'                => $this->_translate('Params (json)'),
            'route'                 => $this->_translate('Route name'),
            'uri'                   => $this->_translate('URI'),
            'is_visible'            => $this->_translate('Visible?'),
            'class'                 => $this->_translate('Stylesheet'),
            'proxy'                 => $this->_translate('Proxy'),
        );

        return parent::__construct($options, $instance);
    }
}
