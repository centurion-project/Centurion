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
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_View
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_View_Helper_Truncate extends Zend_View_Helper_Abstract
{
    /**
     * @param null $string
     * @param int $start
     * @param int $length
     * @param string $prefix
     * @param string $postfix
     * @return $this|string
     */
    public function truncate($string = null, $start = 0, $length = 100, $prefix = '...', $postfix = '...')
    {
        if (!func_num_args()) {
            return $this;
        }

        $truncated = trim($string);
        $start = (int) $start;
        $length = (int) $length;

        // Return original string if max length is 0
        if ($length < 1) return $truncated;

        $full_length = iconv_strlen($truncated);

        // Truncate if necessary
        if ($full_length > $length) {
            // Right-clipped
            if ($length + $start > $full_length) {
                $start = $full_length - $length;
                    $postfix = '';
            }

            // Left-clipped
            if ($start == 0) $prefix = '';

            // Do truncate!
            $truncated = $prefix . trim(substr($truncated, $start, $length)) . $postfix;
        }

        return $truncated;
    }

    public function cuttext($value, $length, $separator = '...')
    {
        return Centurion_Inflector::cuttext($value, $length, $separator);
    }
}
