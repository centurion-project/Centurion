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
class Centurion_View_Helper_FormatToTwitterDate extends Zend_View_Helper_Abstract
{
    // @todo format to Zend_Date
    public function formatToTwitterDate($original, $average = false)
    {
        $original = strtotime($original);
        // array of time period chunks
        $chunks = array(
            array(60 * 60 * 24 * 365 , 'an', 'ans'),
            array(60 * 60 * 24 * 30 , 'mois', 'mois'),
            array(60 * 60 * 24 * 7, 'semaine', 'semaines'),
            array(60 * 60 * 24 , 'jour', 'jours'),
            array(60 * 60 , 'heure', 'heures'),
            array(60 , 'min', 'mins'),
            array(1 , 'sec', 'secs'),
        );

        $today = time(); /* Current unix time  */
        $since = $today - $original;

        // $j saves performing the count function each time around the loop
        for ($i = 0, $j = count($chunks); $i < $j; $i++) {
            list($seconds, $single, $plural) = $chunks[$i];

            // finding the biggest chunk (if the chunk fits, break)
            if (($count = floor($since / $seconds)) != 0) {
                break;
            }
        }

        $print = ($count == 1) ? '1 '.$this->view->translate($single) : $count . ' '.$this->view->translate($plural);

        if ($average === false && $i + 1 < $j) {
            // now getting the second item
            list($seconds2, $single, $plural) = $chunks[$i + 1];

            // add second item if its greater than 0
            if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0) {
                $print .= ($count2 == 1) ? ', 1 '.$this->view->translate($single) : $count2 . ' '.$this->view->translate($plural);
            }
        }

        return $print;
    }
}