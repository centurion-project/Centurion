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
 * @package     Centurion_Collection
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Collection
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lchenay@@gmail.com>
 */
class Centurion_Collection extends Centurion_Access implements Countable, IteratorAggregate
{
    /**
     * Datas.
     *
     * @var array
     */
    protected $_data = array();

    /**
     * @param array $data
     */
    public function __construct($data = array())
    {
        $this->setData($data);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->_data = $data;

        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->_data);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_data);
    }

    /**
     * Get all the records as an array.
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Get the first record in the collection.
     * @return mixed
     */
    public function getFirst()
    {
        return reset($this->_data);
    }

    /**
     * Get the last record in the collection.
     * @return mixed
     */
    public function getLast()
    {
        return end($this->_data);
    }

    /**
     * Get the last record in the collection.
     * @return mixed
     */
    public function end()
    {
        return $this->getLast();
    }

    /**
     * Get the current key.
     * @return mixed
     */
    public function key()
    {
        return key($this->_data);
    }

    /**
     * Whether or not this collection contains a specified element.
     *
     * @param   mixed       $key    The key of the element
     * @return  boolean
     */
    public function contains($key)
    {
        foreach ($this->_data as $val) {
            if ($val == $key) {
                return true;
            }
        }
        return false;
    }

    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function push($value)
    {
        array_push($this->_data, $value);
        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function unshift($value)
    {
        array_unshift($this->_data, $value);
        return $this;
    }

    /**
     * Pop the element off the end of the stack.
     *
     * @return mixed    The last value of the stack
     */
    public function pop()
    {
        return array_pop($this->_data);
    }

    /**
     * Retrieve all data.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getData();
    }

    /**
     * Set a value with a key.
     *
     * @param string $key Key
     * @param string $value Value
     * @return $this
     */
    public function set($key, $value)
    {
        if (isset($this->_data[$key])) {
            throw new Centurion_Exception(sprintf('Registry key "%s" already exists', $key));
        }

        $this->_data[$key] = $value;

        return $this;
    }

    /**
     * Remove a value from the stack with a key.
     *
     * @param string $key Key for removing the element
     * @return $this
     */
    public function remove($key)
    {
        if (isset($this->{$key})) {
            //TODO: check if it's really necessary
            if (is_object($this->_data[$key]) && (method_exists($this->_data[$key], '__destruct'))) {
                $this->_data[$key]->__destruct();
            }
            unset($this->_data[$key]);
        }

        return $this;
    }

    /**
     * Retrieve a value with a specific key.
     *
     * @param string $key Key
     * @return mixed
     */
    public function get($key)
    {
        if (!isset($this->{$key})) {
            throw new Centurion_Exception(sprintf('Registry key "%s" does not exist', $key));
        }

        return $this->_data[$key];
    }
}
