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
 * @package     Centurion_Db
 * @subpackage  Table
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Db
 * @subpackage  Table
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Db_Table_Field_Abstract
{
    protected $_name = null;
    
    public function __construct($name, array $options = null)
    {
        $this->_name = $name;
        
        if (is_array($options)) {
            $this->setOptions($options);
        } elseif (($options instanceof Zend_Config)) {
            $this->setConfig($options);
        }
        
        /**
         * Extensions
         */
        $this->init();
    }
    
    /**
     * Initialize object; used by extending classes
     *
     * @return void
     */
    public function init()
    {
    }
    
    /**
     * Set object state from Zend_Config object
     *
     * @param  Zend_Config $config
     * @return Zend_Form_Element
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }
    
    
    public function setOptions(array $options)
    {
        return $this;
    }
    
    /**
     * Set field attribute
     *
     * @param  string $name
     * @param  mixed $value
     * @return Centurion_Db_Table_Field_Abstract
     * @throws Centurion_Exception for invalid $name values
     */
    public function setAttrib($name, $value)
    {
        $name = (string) $name;
        if ('_' == $name[0]) {
            throw new Centurion_Exception(sprintf('Invalid attribute "%s"; must not contain a leading underscore', $name));
        }
        
        if (null === $value) {
            unset($this->$name);
        } else {
            $this->$name = $value;
        }
        
        return $this;
    }
    
    /**
     * Set multiple attributes at once
     *
     * @param  array $attribs
     * @return Zend_Form_Element
     */
    public function setAttribs(array $attribs)
    {
        foreach ($attribs as $key => $value) {
            $this->setAttrib($key, $value);
        }
        
        return $this;
    }
    
    /**
     * Retrieve element attribute
     *
     * @param  string $name
     * @return string
     */
    public function getAttrib($name)
    {
        $name = (string) $name;
        if (isset($this->$name)) {
            return $this->$name;
        }
        
        return null;
    }
    
    /**
     * Return all attributes
     *
     * @return array
     */
    public function getAttribs()
    {
        $attribs = get_object_vars($this);
        foreach ($attribs as $key => $value) {
            if ('_' == substr($key, 0, 1)) {
                unset($attribs[$key]);
            }
        }
        
        return $attribs;
    }
    
    /**
     * Overloading: retrieve object property
     *
     * Prevents access to properties beginning with '_'.
     *
     * @param  string $key
     * @return mixed
     */
    public function __get($key)
    {
        if ('_' == $key[0]) {
            throw new Centurion_Exception(sprintf('Cannot retrieve value for protected/private property "%s"', $key));
        }
        
        if (!isset($this->$key)) {
            return null;
        }
        
        return $this->$key;
    }
    
    /**
     * Overloading: set object property
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttrib($key, $value);
    }
}