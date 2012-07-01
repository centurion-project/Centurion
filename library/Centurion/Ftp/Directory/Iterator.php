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
 * @subpackage  Directory
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Ftp
 * @subpackage  Directory
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Ftp_Directory_Iterator implements SeekableIterator, Countable, ArrayAccess
{
    /**
     * The directory
     *
     * @var string
     */
    protected $_dir = null;

    /**
     * The converted files and folders
     *
     * @var array
     */
    protected $_rows = array();

    /**
     * The raw files and folders
     *
     * @var array
     */
    protected $_data = array();

    /**
     * The FTP connection
     *
     * @var Centurion_Ftp
     */
    protected $_ftp = null;

    /**
     * The number of rows
     *
     * @var int
     */
    protected $_count = 0;

    /**
     * The iterator pointer
     *
     * @var int
     */
    protected $_pointer = 0;

    /**
     * Instantiate
     *
     * @param string $dir The full path
     * @param Centurion_Ftp $ftp The FTP connection
     */
    public function __construct($dir, $ftp)
    {
        $this->_dir = $dir;
        $this->_filter = $filter;
        $this->_ftp = $ftp;

        $lines = @ftp_rawlist($this->_ftp->getConnection(), $dir);

        foreach ($lines as $line) {
            preg_match('/^([\-dl])([rwx\-]+)\s+(\d+)\s+(\w+)\s+(\w+)\s+(\d+)\s+(\w+\s+\d+\s+[\d\:]+)\s+(.*)$/', $line, $matches);

            list($trash, $type, $permissions, $unknown, $owner, $group, $bytes, $date, $name) = $matches;

            if ($type != 'l') {
                $this->_data[] = array(
                    'type' => $type,
                    'permissions' => $permissions,
                    'bytes' => $bytes,
                    'name' => $name,
                );
            }
        }

        $this->_count = count($this->_data);
    }

    /**
     * Rewind the pointer, required by Iterator
     *
     * @return Centurion_Ftp_Directory_Iterator
     */
    public function rewind()
    {
        $this->_pointer = 0;

        return $this;
    }

    /**
     * Get the current row, required by iterator
     *
     * @return Centurion_Ftp_Directory|Centurion_Ftp_File|null
     */
    public function current()
    {
        if ($this->valid() === false) {
            return null;
        }

        if (empty($this->_rows[$this->_pointer])) {
            $row = $this->_data[$this->_pointer];
            switch ($row['type']) {
                case 'd': // Directory
                    $this->_rows[$this->_pointer] = new Centurion_Ftp_Directory($this->_dir . $row['name'] . '/', $this->_ftp);
                    break;
                case '-': // File
                    $this->_rows[$this->_pointer] = new Centurion_Ftp_File($this->_dir . $row['name'], $this->_ftp);
                    break;
                case 'l': // Symlink
                default:
            }
        }

        return $this->_rows[$this->_pointer];
    }

    /**
     * Return the key of the current row, required by iterator
     *
     * @return int
     */
    public function key()
    {
        return $this->_pointer;
    }

    /**
     * Continue the pointer to the next row, required by iterator
     */
    public function next()
    {
        ++$this->_pointer;
    }

    /**
     * Whether or not there is another row, required by iterator
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->_pointer < $this->_count;
    }

    /**
     * Return the number of rows, required by countable
     *
     * @return int
     */
    public function count()
    {
        return $this->_count;
    }

    /**
     * Seek to the given position, required by seekable
     *
     * @param int $position
     * @return Centurion_Ftp_Directory_Iterator
     */
    public function seek($position)
    {
        $position = (int)$position;
        if ($position < 0 || $position >= $this->_count) {
            throw new Zend_Exception('Illegal index ' . $position);
        }
        $this->_pointer = $position;

        return $this;
    }

    /**
     * Whether or not the offset exists, required by seekable
     *
     * @param int $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[(int)$offset]);
    }

    /**
     * Get the item at the given offset, required by seekable
     *
     * @param int $offset
     * @return Centurion_Ftp_Directory|Centurion_Ftp_File|null
     */
    public function offsetGet($offset)
    {
        $this->_pointer = (int)$offset;

        return $this->current();
    }

    /**
     * Set the item at the given offset (ignored), required by seekable
     *
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Unset the item at the given offset (ignored), required by seekable
     *
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * Get a given row, required by seekable
     *
     * @param int $position
     * @param boolean $seek [optional]
     * @return Centurion_Ftp_Directory|Centurion_Ftp_File|null
     */
    public function getRow($position, $seek = false)
    {
        $key = $this->key();
        try {
            $this->seek($position);
            $row = $this->current();
        } catch (Zend_Exception $e) {
            throw new Zend_Exception('No row could be found at position ' . (int)$position);
        }
        if ($seek == false) {
            $this->seek($key);
        }

        return $row;
    }
}
