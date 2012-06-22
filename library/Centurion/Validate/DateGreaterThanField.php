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
 * @package     Centurion_View
 * @copyright   Copyright (c) 2008-2009 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_View
 * @copyright   Copyright (c) 2008-2009 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Validate_DateGreaterThanField extends Centurion_Validate_Field_Abstract
{
    
    const NOT_GREATER = 'not_greater';
    
    protected $_allowEqual = false;
    
    public function __construct($fieldName, $fieldTitle = null, $allowEqual = false)
    {
        $this->_messageTemplates[self::NOT_GREATER] = 'Is not greated than %fieldTitle%.';
        
        $this->_allowEqual = $allowEqual;
        
        parent::__construct($fieldName, $fieldTitle);
    }
    
    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if a field name has been set, the field name is available in the
     * context, and the value of that field name matches the provided value.
     *
     * @param  string $value
     * @param array $context
     *
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->_setValue($value);
        
        $fieldValue = $this->getFieldValue($context);
        
        if (null === $fieldValue)
            return false;
        
        $date = new Zend_Date($value, Zend_Date::ISO_8601);
        $date2 = new Zend_Date($fieldValue, Zend_Date::ISO_8601);
        
        if ($date->compare($date2) < 0 || (!$this->_allowEqual && $date->compare($date2) == 0)) {
            $this->_error(self::NOT_GREATER, $date2);
            return false;
        }
        
        return true;
    }
}
