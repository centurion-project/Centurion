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
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Controller_Action_Helper_Csv extends Centurion_Controller_Action_Helper_Extract
{
    const DEFAULT_FILENAME = 'export.csv';
    const DEFAULT_DELIMITER = ';';
    const DEFAULT_ENCODING = 'UTF-16LE';

    /**
     * Default options.
     *
     * @var array
     */
    public static $options = array(
        'filename'       =>  self::DEFAULT_FILENAME,
        'delimiter'      =>  self::DEFAULT_DELIMITER,
        'encoding'       =>  self::DEFAULT_ENCODING
    );


    /**
     * Convert a rowset to a CSV view.
     *
     * @param Centurion_Db_Table_Rowset_Abstract|array $rowset
     * @param array $columns Header of the file
     * @param string $options Override default options
     * @param boolean $noSend : To return the file name rather than sending it to the client
     */
    public function direct($rowset, array $columns, $options = array(), $header = null, $sendToBrowser = true)
    {
        if (!$rowset instanceof Centurion_Db_Table_Rowset_Abstract && !is_array($rowset)) {
            throw new Centurion_Exception('First parameter must be a Centurion valid rowset or an array');
        }

        $options = array_merge(self::$options, $options);
        $handler = null;
        $tmpName = null;

        //To create a temp file
        $tmpName = tempnam(sys_get_temp_dir(), 'csv-export_' . md5(rand()) . '.csv');
        $handler = fopen($tmpName, 'w');

        if (null !== $header) {
            fputcsv($handler, $this->_convertFields($header, $options['encoding']), $options['delimiter']);
        }

        $keys = array_keys($columns);
        fputcsv($handler, $this->_convertFields(array_values($columns), $options['encoding']), $options['delimiter']);
        foreach ($rowset as $row) {
            $fields = array();
            if ($row instanceof Centurion_Db_Table_Row_Abstract) {
                foreach ($keys as $value) {
                    $fields[] = $row->{$value};
                }
            } else {
                $fields = array_values($row);
            }

            $fields = $this->_convertFields($fields, $options['encoding']);

            fputcsv($handler, $fields, $options['delimiter']);
        }

        if (!$sendToBrowser){
            fclose($handler);
            return $tmpName;
        }

        $this->getActionController()->getHelper('layout')->disableLayout();
        $this->getActionController()->getHelper('viewRenderer')->setNoRender(true);

        $size = ftell($handler);

        fseek($handler, 0);

        $this->getResponse()->setHeader('Content-disposition', sprintf('attachment; filename=%s', $options['filename']), true)
                            ->setHeader('Content-Type', sprintf('application/force-download; charset=%s', $options['encoding']), true)
                            ->setHeader('Content-Transfer-Encoding', 'application/octet-stream\n', true)
                            ->setHeader('Content-Length', $size, true)
                            ->setHeader('Pragma', 'no-cache', true)
                            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0, public', true)
                            ->setHeader('Expires', '0', true)
                            ->sendHeaders();
        fpassthru($handler);
        fclose($handler);
    }
}
