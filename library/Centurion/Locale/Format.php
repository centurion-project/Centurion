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
 * @package     Centurion_Locale
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Locale
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@octaveoctave.com>
 */
class Centurion_Locale_Format extends Zend_Locale_Format
{
    /**
     * @see http://docs.jquery.com/UI/Datepicker/$.datepicker.formatDate 
     * @var array[string]string
     */
    static $formatIsoToDatepicker = array(
            'dd' => 'dd',
            'd'  => 'd',
            'DD' => 'EEEE',
            'D'  => 'EEE',
            'oo' => 'DD',
            'o'  => 'D',
            'mm' => 'MM',
            'm'  => 'M',
            'MM' => 'MMMM',
            'M'  => 'MMM',
            'yy' => 'yyyy',
            'y'  => 'yy',
            '@'  => 'U'
        );
        
    public static function convertIsoToPhpFormat($format)
    {
        if ($format === null) {
            return null;
        }
        $convert = array('d' => 'dd'  , 'D' => 'EE'  , 'j' => 'd'   , 'l' => 'EEEE', 'N' => 'eee' , 'S' => 'SS'  ,
                         'w' => 'e'   , 'z' => 'D'   , 'W' => 'ww'  , 'F' => 'MMMM', 'm' => 'MM'  , 'M' => 'MMM' ,
                         'n' => 'M'   , 't' => 'ddd' , 'L' => 'l'   , 'o' => 'YYYY', 'Y' => 'yyyy', 'y' => 'yy'  ,
                         'a' => 'a'   , 'A' => 'a'   , 'B' => 'B'   , 'g' => 'h'   , 'G' => 'H'   , 'h' => 'hh'  ,
                         'H' => 'HH'  , 'i' => 'mm'  , 's' => 'ss'  , 'e' => 'zzzz', 'I' => 'I'   , 'O' => 'Z'   ,
                         'P' => 'ZZZZ', 'T' => 'z'   , 'Z' => 'X'   , 'c' => 'yyyy-MM-ddTHH:mm:ssZZZZ',
                         'r' => 'r'   , 'U' => 'U');

        $convert = array_flip($convert);
        asort($convert);
        
        return strtr($format, $convert);
    }

    /**
     * Format déjà localisé
     */ 
    public static function convertIsoToDatepickerFormat($format)
    {
        if ($format === null) {
            return null;
        }

        $convert = array_flip(self::$formatIsoToDatepicker);
        arsort($convert);

        $value = strtr($format, $convert);

        return $value;
    }

    public static function convertDatepickerToIsoFormat($format)
    {
        if ($format === null) {
            return null;
        }

        //arsort modify original array,so we make a copy
        $convert = self::$formatIsoToDatepicker;
        arsort($convert);

        $value = strtr($format, $convert);

        return $value;
    }
}
