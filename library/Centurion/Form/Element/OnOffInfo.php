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
 * @subpackage  Element
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @class Centurion_Form_Element_OnOffInfo
 * @category    Centurion
 * @package     Centurion_Form
 * @subpackage  Element
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Richard Déloge <rd@octaveoctave.com>
 *
 * Customization of Centurion_Form_Element_Info element to use to display a boolean row's field
 * (usually translated in the form by a checkbox)
 */
class Centurion_Form_Element_OnOffInfo extends Centurion_Form_Element_Info{
    /**
     * Display value to print if the element is true
     * @var string
     */
    protected $_onValue = 'On';

    /**
     * Display value to print if the lement is false
     * @var string
     */
    protected $_offValue = 'Off';

    /**
     *
     * @param mixed $value
     * @return Zend_Form_Element
     */
    public function setValue($value)
    {
        if(empty($value)){
            //Build the info element with the off value
            return parent::setValue($this->_offValue);
        }
        else{
            //Build the info element with the on value
            return parent::setValue($this->_onValue);
        }
    }

    /**
     * To customize the value to display when this field is true
     * @param string $value
     * @return $this
     */
    public function setOnValue($value){
        $this->_onValue = $value;
        return $this;
    }

    /**
     * To customize the value to display when this field is false
     * @param string $value
     * @return $this
     */
    public function setOffValue($value){
        $this->_offValue = $value;
        return $this;
    }
}