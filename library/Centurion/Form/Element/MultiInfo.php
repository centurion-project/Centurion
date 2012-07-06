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
 * @class Centurion_Form_Element_MultiInfo
 * @category    Centurion
 * @package     Centurion_Form
 * @subpackage  Element
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Richard Déloge <rd@octaveoctave.com>
 *
 * Customization of Centurion_Form_Element_Info element to use to display value from a referenced subinstance from a row
 * or a many to many (usually translated in the form by a select or a multi-select)
 */
class Centurion_Form_Element_MultiInfo extends Centurion_Form_Element_Info{
    /**
     * List of values ​​accepted by the element
     * @var array
     */
    protected $_multiOptions = array();

    /**
     * Separator to use to splited several values
     * @var string
     */
    protected $_separator = '<br/>';

    /**
     * Set all options at once (overwrites) allowed by this field (to keep the compliance with select and list elements)
     *
     * @param  array $options
     * @return Zend_Form_Element_Multi
     */
    public function setMultiOptions(array $options){
        $this->_multiOptions = $options;
    }

    /**
     * To customize the separator to use to build list
     * @param string $separator
     */
    public function setSeparator($separator){
        $this->_separator = $separator;
    }

    /**
     * Parse the value, if it is an array, print the value associated by the key in the options list,
     * else, combine all values associates by keys in the array to display the list
     *
     * @param mixed $value
     * @return Zend_Form_Element
     */
    public function setValue($value)
    {
        if(!is_array($value)
            && isset($this->_multiOptions[$value])){
            //Retrieve the value to display from the option list
            return parent::setValue($this->_multiOptions[$value]);
        }
        elseif(is_array($value)){
            //Gets values to display
            $_values = array_intersect_key($this->_multiOptions, array_flip($value));
            //Combine them
            return parent::setValue(implode($this->_separator, $_values));
        }

        return parent::setValue($value);
    }
}