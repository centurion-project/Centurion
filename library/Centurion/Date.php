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
 * @package     Centurion_Date
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Date
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Date extends Zend_Date
{
    /**
     * MySQL DATETIME, DATE, TIME format
     */
    const MYSQL_DATETIME = 'yyyy-MM-dd HH:mm:ss';
    const MYSQL_DATE = 'yyyy-MM-dd';
    const MYSQL_TIME = 'HH:mm:ss';

    /**
     * Proxy to access to _getLocalizedToken
     * @static
     * @param $token
     * @param $locale
     * @return string
     */
    public static function getLocalizedToken($token, $locale)
    {
        return self::_getLocalizedToken($token, $locale);
    }
}
