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
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_View_Helper_RowToColumn extends Zend_View_Helper_Abstract
{
    public function RowToColumn($array, $nbColumn)
    {
        $nbRow = ceil(count($array) / $nbColumn);
        $count = $nbRow * $nbColumn;

        $newKey = 0;
        $rowset = array_fill(0, $count, null);

        foreach ($array as $key => $row) {
            $rowset[$newKey] = $row;
            $newKey += $nbRow;

            if ($newKey >= $count) {
                $newKey += 1 - $count;
            }
        }

        return $rowset;
    }
}
