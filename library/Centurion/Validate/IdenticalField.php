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
 * @package     Centurion_Validate
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Validate
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Validate_IdenticalField extends Centurion_Validate_Field_Abstract
{
    const NOT_MATCH = 'notMatch';

    public function __construct($fieldName, $fieldTitle = null)
    {
        parent::__construct($fieldName, $fieldTitle);

        $this->_messageTemplates[self::NOT_MATCH] = 'Does not match %fieldTitle%.';
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if a field name has been set, the field name is available in the
     * context, and the value of that field name matches the provided value.
     *
     * @param string $value
     * @param array $context
     *
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->_setValue($value);

        $fieldValue = $this->getFieldValue($context);

        if ($fieldValue != $value) {
            $this->_error(self::NOT_MATCH);
            return false;
        }

        return true;
    }
}