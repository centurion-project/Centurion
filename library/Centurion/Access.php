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
 * @package     Centurion_Access
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Access
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
abstract class Centurion_Access implements ArrayAccess
{
    /**
     * Set an entire array to the data.
     *
     * @param   array $array An array of key => value pairs
     * @return  Centurion_Access
     */
    public function setArray(array $array)
    {
        foreach ($array as $k => $v) {
            $this->set($k, $v);
        }
        
        return $this;
    }
    
    /**
     * Set key and value to data.
     *
     * @see     set, offsetSet
     * @param   $name
     * @param   $value
     * @return  void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }
    
    /**
     * Get key from data
     *
     * @see     get, offsetGet
     * @param   mixed $name
     * @return  mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }
    
    /**
     * Check if key exists in data.
     *
     * @param   string $name
     * @return  boolean whether or not this object contains $name
     */
    public function __isset($name)
    {
        return $this->contains($name);
    }
    
    /**
     * Remove key from data
     *
     * @param   string $name
     * @return  void
     */
    public function __unset($name)
    {
        return $this->remove($name);
    }
    
    /**
     * Check if an offset exists.
     *
     * @param   mixed $offset
     * @return  boolean Whether or not this object contains $offset
     */
    public function offsetExists($offset)
    {
        return $this->contains($offset);
    }
    
    /**
     * An alias of get().
     *
     * @see     get, __get
     * @param   mixed $offset
     * @return  mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
    
    /**
     * Sets $offset to $value.
     *
     * @see     set, __set
     * @param   mixed $offset
     * @param   mixed $value
     * @return  void
     */
    public function offsetSet($offset, $value)
    {
        if ( ! isset($offset)) {
            $this->add($value);
        } else {
            $this->set($offset, $value);
        }
    }
    
    /**
     * Unset a given offset.
     *
     * @see   set, offsetSet, __set
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        return $this->remove($offset);
    }
    
    /**
     * Remove the element with the specified offset.
     *
     * @param mixed $offset The offset to remove
     * @return boolean True if removed otherwise false
     */
    public function remove($offset)
    {
        throw new Centurion_Exception('Remove is not supported for ' . get_class($this));
    }
    
    /**
     * Return the element with the specified offset.
     *
     * @param mixed $offset     The offset to return
     * @return mixed
     */
    public function get($offset)
    {
        throw new Centurion_Exception('Get is not supported for ' . get_class($this));
    }
    
    /**
     * Set the offset to the value.
     *
     * @param mixed $offset The offset to set
     * @param mixed $value The value to set the offset to
     *
     */
    public function set($offset, $value)
    {
        throw new Centurion_Exception('Set is not supported for ' . get_class($this));
    }
    
    /**
     * Check if the specified offset exists .
     * 
     * @param mixed $offset The offset to check
     * @return boolean True if exists otherwise false
     */
    public function contains($offset)
    {
        throw new Centurion_Exception('Contains is not supported for ' . get_class($this));
    }
    
    /**
     * Add the value.
     * 
     * @param mixed $value The value to add 
     * @return void
     */
    public function add($value)
    {
        throw new Centurion_Exception('Add is not supported for ' . get_class($this));
    }
}
