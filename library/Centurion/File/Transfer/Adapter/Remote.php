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
class Centurion_File_Transfer_Adapter_Remote extends Centurion_File_Transfer_Adapter_Abstract 
{
    /**
     * Adds one or more files
     *
     * @param  string|array $file      File to add
     * @param  string|array $validator Validators to use for this file, must be set before
     * @param  string|array $filter    Filters to use for this file, must be set before
     * @return Zend_File_Transfer_Adapter_Abstract
     * @throws Zend_File_Transfer_Exception Not implemented
     */
    public function addFile($files, $validator = null, $filter = null)
    {
        foreach ($files as $name => $file) {
            $dest = tempnam(sys_get_temp_dir(), 'transfert');
            
            $client = new Zend_Http_Client($file, array('adapter' => 'Zend_Http_Client_Adapter_Curl'));
            $response = $client->setStream($dest)->request(Zend_Http_Client::GET);
            
            $this->_files[$name] = array(
                'size'      => filesize($dest),
                'tmp_name'  => $dest,
                'name'      => basename($file),
            );
            
            $this->_files[$name]['type'] = $this->_detectMimeType($this->_files[$name]);
        }
        
        return $this;
    }
}
