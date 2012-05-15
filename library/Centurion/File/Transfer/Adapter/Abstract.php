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
 * @package     Centurion_File
 * @subpackage  Centurion_File_Transfert
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_File
 * @subpackage  Centurion_File_Transfert
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@octaveoctave.com>
 */
class Centurion_File_Transfer_Adapter_Abstract extends Zend_File_Transfer_Adapter_Abstract
{
    protected $_defaultDestination = null;

    /**
     * @var bool
     */
    protected $_keepOriginal = false;

    /**
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }
    
    /**
     * Send file
     *
     * @param  mixed $options
     * @return bool
     */
    public function send($options = null)
    {
        require_once 'Zend/File/Transfer/Exception.php';
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }
    
    /**
     * Receive file
     *
     * @param  mixed $options
     * @return bool
     */
    public function receive($files = null)
    {
        if (!$this->isValid($files)) {
            return false;
        }

        $check = $this->_getFiles($files);
        foreach ($check as $file => $content) {
            if (!isset($content['received'])||!$content['received']) {
                $directory   = '';
                $destination = $this->getDestination($file);

                if ($destination !== null) {
                    $directory = $destination . DIRECTORY_SEPARATOR;
                }
                
                $filename = $directory . $content['name'];
                $rename   = $this->getFilter('Rename');
                if ($rename !== null) {
                    
                    $tmp = $rename->getNewName($content['tmp_name']);
                     
                    if ($tmp != $content['tmp_name']) {
                        $filename = $tmp;
                    }

                    if (dirname($filename) == '.') {
                        $filename = $directory . $filename;
                    }

                    $key = array_search(get_class($rename), $this->_files[$file]['filters']);
                    unset($this->_files[$file]['filters'][$key]);
                }
                
                // Should never return false when it's tested by the upload validator
                if (is_writable($content['tmp_name']) && !$this->_keepOriginal) {
                    $valid = rename($content['tmp_name'], $filename);
                } else {
                    $valid = copy($content['tmp_name'], $filename);
                }
                if (!$valid) {
                    if ($content['options']['ignoreNoFile']) {
                        $this->_files[$file]['received'] = true;
                        $this->_files[$file]['filtered'] = true;
                        continue;
                    }

                    $this->_files[$file]['received'] = false;
                    return false;
                }

                if ($rename !== null) {
                    $this->_files[$file]['destination'] = dirname($filename);
                    $this->_files[$file]['name']        = basename($filename);
                }

                $this->_files[$file]['tmp_name'] = $filename;
                $this->_files[$file]['received'] = true;
            }

            if (!isset($content['filtered'])||!$content['filtered']) {
                if (!$this->_filter($file)) {
                    $this->_files[$file]['filtered'] = false;
                    return false;
                }

                $this->_files[$file]['filtered'] = true;
            }
        }
        
        return true;
    }

    /**
     * Is file sent?
     *
     * @param  array|string|null $files
     * @return bool
     */
    public function isSent($files = null)
    {
        require_once 'Zend/File/Transfer/Exception.php';
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Is file received?
     *
     * @param  array|string|null $files
     * @return bool
     */
    public function isReceived($files = null)
    {
        require_once 'Zend/File/Transfer/Exception.php';
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Has a file been uploaded ?
     *
     * @param  array|string|null $files
     * @return bool
     */
    public function isUploaded($files = null)
    {
        $files = $this->_getFiles($files, false, true);
        if (empty($files)) {
            return false;
        }

        foreach ($files as $file) {
            if (empty($file['name'])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Has the file been filtered ?
     *
     * @param array|string|null $files
     * @return bool
     */
    public function isFiltered($files = null)
    {
        $files = $this->_getFiles($files, false, true);
        if (empty($files)) {
            return false;
        }

        foreach ($files as $content) {
            if ($content['filtered'] !== true) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param bool $bool
     */
    public function setKeepOriginal($bool = true) {
        $this->_keepOriginal = $bool;
    }
}
