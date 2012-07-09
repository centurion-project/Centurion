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
 * @package     Centurion_Contrib
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Media_Model_Adapter_Ftp extends Media_Model_Adapter_Abstract
{
    protected $_connection = null;
    
    /**
     * @return Centurion_Ftp
     */
    protected function _getConnection()
    {
        if (null === $this->_connection) {
            $this->_connection = Centurion_Ftp::connect($this->getOption('server'),
                                                        $this->getOption('username'),
                                                        $this->getOption('password'));
        }
        
        return $this->_connection;
    }
    
    public function getUrl($dest)
    {
        return $this->getOption('url').$dest;
    }
    
    public function read($dest)
    {
        $tempFile = tempnam(sys_get_temp_dir(), '');
        $destFullPath = $this->getOption('path') . $dest;
        
        if (false === ftp_get($this->_getConnection(), $tempFile, $destFullPath, FTP_BINARY)) {
            return false;
        }
        
        $str = file_get_contents($tempFile);
        unlink($tempFile);
        
        return $str;
    }
    
    public function update($absolutePathSource, $relativePathDest)
    {
        $ftp = $this->_getConnection();
        $destFullPath = $this->getOption('path') . $relativePathDest;
        
        try {
            $ftp->deleteFile($destFullPath);
            $ftp->putFile($absolutePathSource, $destFullPath);
        } catch(Zend_Exception $e) {
            return false;
        }
        
        return true;
    }
    
    public function delete($relativePathDest)
    {
        $ftp = $this->_getConnection();
        $destFullPath = $this->getOption('path') . $relativePathDest;
        
        try {
            $ftp->deleteFile($destFullPath);
        } catch(Zend_Exception $e) {
            return false;
        }
        
        return true;
    }
    
    public function save($absolutePathSource, $relativePathDest)
    {
        $ftp = $this->_getConnection();
        
        $destFullPath = $this->getOption('path') . $relativePathDest;
        if (!$ftp->isDir(dirname($destFullPath)))
            $ftp->mkdirRecursive(dirname($destFullPath));
        
        try {
            $ftp->putFile($absolutePathSource, $destFullPath);
        } catch(Zend_Exception $e) {
            return false;
        }
        
        return true;
    }
}
