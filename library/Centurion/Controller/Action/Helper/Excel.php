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
class Centurion_Controller_Action_Helper_Excel extends Centurion_Controller_Action_Helper_Extract
{
    const DEFAULT_FILENAME = 'extract';
    const DEFAULT_ENCODING = 'UTF-16LE';

    /**
     * Default options.
     *
     * @var array
     */
    public static $options = array(
        'filename'       =>  self::DEFAULT_FILENAME,
        'encoding'       =>  self::DEFAULT_ENCODING
    );

    /**
     * Convert a rowset to a excel view.
     *
     * @param Centurion_Db_Table_Rowset_Abstract|array $rowset
     * @param array $columns Header of the file
     * @param string $options Override default options
     */
    public function direct($rowset, array $columns = array(), $options = array(), $header = null)
    {
        $options = array_merge(self::$options, $options);

        if (!$rowset instanceof Centurion_Db_Table_Rowset_Abstract && !is_array($rowset)) {
            throw new Centurion_Exception('First parameter must be a Centurion valid rowset or an array');
        }

        $data = array();

        if (count($columns) > 0) {
            $data[] = array_values($columns);
            $keys = array_keys($columns);

            foreach ($rowset as $row) {
                $fields = array();

                foreach ($keys as $value) {
                    if ($rowset instanceof Centurion_Db_Table_Row_Abstract) {
                        $fields[] = $row->{$value};
                    } else {
                        $fields[] = $row[$value];
                    }
                }

                $fields = $this->_convertFields($fields, $options['encoding']);
                $data[] = $fields;

            }
        } else {
            if ($rowset instanceof Centurion_Db_Table_Rowset_Abstract) {
                foreach ($rowset as $row) {
                    $data[] = $row->toArray();
                }
            } else {
                $data = $rowset;
            }
        }

        // generate file (constructor parameters are optional)
        $xls = new PhpExcel_ExcelXML($options['encoding'], false, 'Extract');
        $xls->addArray($data);
        $xls->generateXML($options['filename']);

        die();
    }
}
