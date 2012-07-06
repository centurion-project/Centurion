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
 * @package     Centurion_Controller
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Controller
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 * @todo        Normalize the php Excel class to Zend Convention
 * @todo        Move all header to controller action helper
 */
class Centurion_Controller_Action_Helper_Extract extends Zend_Controller_Action_Helper_Abstract
{
    protected function _convertEncoding($string, $encoding)
    {
        return mb_convert_encoding($string, $encoding, 'UTF-8');
    }

    protected function _convertFields(array $fields, $encoding)
    {
        foreach ($fields as &$value) {
            $value = $this->_convertEncoding($value, $encoding);
        }

        return $fields;
    }
}
