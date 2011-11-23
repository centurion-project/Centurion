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
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Db
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Db extends Zend_Db
{
    /**
     * Collection of registered variable.
     *
     * @var array
     */
    protected static $_registry = array();

    protected static $_references = array();

    /**
     * Retrieve a value from registry by a key.
     *
     * @param string $key
     * @return mixed
     */
    public static function registry($key)
    {
        if (isset(self::$_registry[$key])) {
            return self::$_registry[$key];
        }

        return null;
    }

    /**
     * Register a new variable.
     *
     * @param string $key
     * @param mixed $value
     * @param bool $graceful
     */
    public static function register($key, $value, $graceful = false)
    {
        if (isset(self::$_registry[$key])) {
            if ($graceful) {
                return;
            }
            throw new Centurion_Exception(sprintf('Centurion registry key "%s" already exists', $key));
        }
        self::$_registry[$key] = $value;
    }

    /**
     * Unregister a new variable.
     *
     * @param string $key
     */
    public static function unregister($key)
    {
        if (isset(self::$_registry[$key])) {
            if (is_object(self::$_registry[$key]) && (method_exists(self::$_registry[$key], '__destruct'))) {
                self::$_registry[$key]->__destruct();
            }
            unset(self::$_registry[$key]);
        }
    }

    /**
     * Retrieve model object
     *
     * @param   string $modelClass
     * @param   array $arguments
     * @return  Centurion_Db_Table_Abstract
     * @throws  Centurion_Db_Exception  When the model classe name does not exists
     */
    public static function getModel($modelClass = '', $arguments = array())
    {
        $className = self::getClassName($modelClass);
        if (!class_exists($className)) {
            throw new Centurion_Db_Exception(sprintf('Model class name "%s" does not exists', $className));
        }

        $object = new $className($arguments);

        if (array_key_exists($className, self::$_references)) {
            $object->setDependentTables(array_merge($object->getDependentTables(), self::$_references[$className]));
        }

        return $object;
    }

    /**
     * Retrieve the class name for a registry name.
     *
     * @param string $registryName
     */
    public static function getClassName($registryName)
    {
        $classArr = explode('/', trim($registryName));
        $className = sprintf('%s_Model_DbTable_%s',
                             ucfirst($classArr[0]),
                             Centurion_Inflector::classify($classArr[1]));
        return $className;
    }

    /**
     * Retrieve registry name for a class name.
     *
     * @param string $className Class name
     */
    public static function getRegistryName($className)
    {
        return substr($className, 0, strpos($className, '_'))
               . '/'
               . substr($className, strrpos($className, '_', 1) - strlen($className) + 1);
    }


    /**
     * Retrieve model object singleton.
     *
     * @param   string  $registryName OPTIONAL  Registry name
     * @param   array   $arguments    OPTIONAL  Arguments to instanciate model object*
     * @return Centurion_Db_Table_Abstract
     * @throws  Centurion_Db_Exception  When the model classe name does not exists
     */
    public static function getSingleton($registryName = '', array $arguments = array())
    {
        $registryKey = strtolower('_singleton/' . $registryName);
        if (! self::registry($registryKey)) {
            self::register($registryKey, self::getModel($registryName, $arguments));
        }

        return self::registry($registryKey);
    }

    /**
     * Retrieve model object singleton with its class name.
     *
     * @param   string    $className    OPTIONAL Class name
     * @param   array     $arguments    OPTIONAL Arguments to instanciate model object
     * @return Centurion_Db_Table_Abstract
     */
    public static function getSingletonByClassName($className = '', array $arguments = array())
    {
        return self::getSingleton(self::getRegistryName($className), $arguments);
    }

    public static function setReferences($references)
    {
        self::$_references = $references;
        
        foreach (self::$_registry as $key => $val) {
            if (isset($references[get_class($val)])) {
                $val->setDependentTables(array_merge($val->getDependentTables(), $references[get_class($val)]));
            }
        }
    }

    /**
     * Get a row with the class name and the primary key value.
     *
     * @param string $class     Class name
     * @param int $pk           Primary key value
     * @return false|Centurion_Db_Table_Row_Abstract
     */
    public static function getRow($class, $pk)
    {
        if (class_exists($class)) {
            $modelTable = Centurion_Db::getSingletonByClassName($class);

            if (null !== $modelTable) {
                return $modelTable->find($pk)
                                  ->current();
            }
        }

        return false;
    }
}