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
 * @package     Centurion_Ftp
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Ftp
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Ftp_Directory
{
    /**
     * The FTP connection
     *
     * @var Centurion_Ftp
     */
    protected $_ftp = null;

    /**
     * The path to the directory
     *
     * @var string
     */
    protected $_path = null;

    /**
     * The files and folders within the directory
     *
     * @var Centurion_Ftp_Iterator
     */
    protected $_contents = null;

    /**
     * The name of the directory
     *
     * @var string
     */
    protected $_name = null;

    /**
     * Instantiate a directory
     *
     * @param string $path The full path
     * @param Centurion_Ftp $ftp The FTP connection
     * @return
     */
    public function __construct($path, $ftp)
    {
        $this->_path = $path;
        $this->_ftp = $ftp;
        $this->_name = basename($this->_path);
    }

    /**
     * Provide read-only access to properties
     *
     * @param string $name The property to get
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'name':
                return $this->_name;
            case 'path':
                return $this->_path;
        }
        throw new Centurion_Ftp_Directory_Exception('Unknown property "' . $name . '"');
    }

    /**
     * Get the contents of the current directory
     *
     * @return Centurion_Ftp_Iterator
     */
    public function getContents()
    {
        if ($this->_contents === null) {
            $this->_changeToDir();
            $this->_contents = new Centurion_Ftp_Directory_Iterator($this->_path, $this->_ftp);
        }

        return $this->_contents;
    }

    /**
     * Change to the current dir so that operations can be performed relatively
     */
    protected function _changeToDir()
    {
        $chdir = ftp_chdir($this->_ftp->getConnection(), $this->_path);
        if ($chdir === false) {
            //throw new Centurion_Ftp_Directory_Exception('Unable to change to directory');
        }
    }

    /**
     * Whether or not this FTP resource is a file
     *
     * @return boolean
     */
    public function isFile()
    {
        return false;
    }

    /**
     * Whether or not this FTP resource is a directory
     *
     * @return boolean
     */
    public function isDirectory()
    {
        return true;
    }

    /**
     * Create a directory with optional recursion
     *
     * @param string|array $path The directory to create
     * @param boolean $recursive [optional] Create all directories in the path
     * @param string|int $permissions [optional] The permissions to set, can be a string e.g. 'rwxrwxrwx' or octal e.g. 0777
     * @return Centurion_Ftp_Directory
     */
    public function makeDirectory($path, $recursive = false, $permissions = null)
    {
        if (!is_array($path)) {
            $path = explode('/', $path);
        }

        $dir = array_shift($path);

        $currentDir = $this->getDirectory($dir);
        if (count($path) == 0 || $recursive) {
            $currentDir->create($permissions);
        }
        if (count($path) > 0) {
            return $currentDir->makeDirectory($path, $recursive, $permissions);
        }

        return $currentDir;
    }

    /**
     * Create the directory
     *
     * @return Centurion_Ftp_Directory
     */
    public function create($permissions = null)
    {
        $makedir = @ftp_mkdir($this->_ftp->getConnection(), $this->_path);
        if ($makedir === false) {
            //throw new Centurion_Ftp_Directory_Exception('Unable to create directory "' . $dir . '"');
        }
        if ($permissions !== null) {
            $chmod = $this->_ftp->chmod($this->_path, $permissions);
            if ($chmod === false) {
                //throw new Centurion_Ftp_Directory_Exception('Unable to chmod directory "' . $dir . '"');
            }
        }

        return $this;
    }

    /**
     * Upload a local file to the current directory
     *
     * @param string $localFilepath The full path and filename to upload
     * @param int $mode [optional] The transfer mode
     * @param string $remoteFilename [optional] Filename to save to on the server
     * @return Centurion_Ftp_File
     */
    public function put($localFilepath, $mode = null, $remoteFilename = null)
    {
        if ($remoteFilename == null) {
            $remoteFilename = basename($localFilepath);
        }
        $remoteFilepath = $this->_path . '/' . $remoteFilename;

        $file = new Centurion_Ftp_File($remoteFilepath, $this->_ftp);
        $file->put($localFilepath, $mode);

        return $file;
    }

    /**
     * Get a file within the current directory
     *
     * @param string $filename The file to get
     * @return Centurion_Ftp_File
     */
    public function getFile($filename)
    {
        return new Centurion_Ftp_File($this->_path . '/' . $filename, $this->_ftp);
    }

    /**
     * Get a folder within the current directory
     *
     * @param string $filename The directory to get
     * @return Centurion_Ftp_Directory
     */
    public function getDirectory($filename)
    {
        return new Centurion_Ftp_Directory($this->_path . '/' . $filename, $this->_ftp);
    }

    /**
     * Whether or not the directory exists
     *
     * @return boolean
     */
    public function exists()
    {
        // Unfinished
    }

    /**
     * Delete the directory
     *
     * @param boolean $recursive [optional] Recursively delete contents
     * @return Centurion_Ftp_Directory
     */
    public function delete($recursive = false)
    {
        // Unfinished

        return $this;
    }

    /**
     * Deletes the contents of the directory
     *
     * @param boolean $recursive [optional] Recursively delete contents
     * @return Centurion_Ftp_Directory
     */
    public function deleteContents($recursive = false)
    {
        // Unfinished

        return $this;
    }

    /**
     * Rename the directory
     *
     * @param string $filename The new name
     * @return Centurion_Ftp_Directory
     */
    public function rename($filename)
    {
        // Unfinished

        return $this;
    }

    /**
     * Copy the directory
     *
     * @param string $filename The new name
     * @return Centurion_Ftp_Directory
     */
    public function copy($filename)
    {
        // Unfinished

        // Return the new directory
    }

    /**
     * Move the directory
     *
     * @param string $filename The new name
     * @return Centurion_Ftp_Directory
     */
    public function move($filename)
    {
        // Unfinished

        return $this;
    }

    /**
     * Change the directory permissions
     *
     * @param int|string $permissions The permissions
     * @return Centurion_Ftp_Directory
     */
    public function chmod($permissions)
    {
        // Unfinished

        return $this;
    }

    /**
     * Save the directory to the given path
     *
     * @param boolean $recursive [optional] Save the contents recursively
     * @return Centurion_Ftp_Directory
     */
    public function saveToPath($recursive = false)
    {
        // Unfinished

        return $this;
    }

    /**
     * Save the directory contents to the given path
     *
     * @param boolean $recursive [optional] Save the contents recursively
     * @return Centurion_Ftp_Directory
     */
    public function saveContentsToPath($recursive = false)
    {
        // Unfinished

        return $this;
    }
}
