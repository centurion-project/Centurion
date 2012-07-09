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
 * @package     Centurion_Config
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * Inspired by sfConfig of Symfony project.
 *
 * @category    Centurion
 * @package     Centurion_Config
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 * @todo        Documentation
 */
class Centurion_Config_Manager
{
    /**
     *
     * @var array[string]string
     */
    protected static $_config = array();

    /**
     * return a value taken from an array according to the selector
     * selector can contains "." (dot) to go down inside the array
     * @param string $selector
     * @param array $source
     */
    protected static function _extractValueFromArray ($selector, $source = self::_config)
    {
        if (false !== strpos($selector, '.')) {
            $pt = $source;
            $parts = explode('.', $selector);
            foreach ($parts as $part) {
                if (!isset($pt[$part]))
                    return null;

                $pt = $pt[$part];
            }

            return $pt;
        }

        return isset($source[$selector]) ? $source[$selector] : null;

    }

    /**
     * Retrieves a config parameter.
     *
     * @param string $name    A config parameter name
     * @param mixed  $default A default config parameter value
     *
     * @return mixed A config parameter value, if the config parameter exists, otherwise null
     */
    public static function get($name, $default = null)
    {
        $value = self::_extractValueFromArray($name, self::$_config);
        if ($value !== null)
            return $value;
        else
            return $default;
    }

    /**
     * Indicates whether or not a config parameter exists.
     *
     * @param string $name A config parameter name
     *
     * @return bool true, if the config parameter exists, otherwise false
     */
    public static function has($name)
    {
        return array_key_exists($name, self::$_config);
    }

    /**
     * Sets a config parameter.
     *
     * If a config parameter with the name already exists the value will be overridden.
     *
     * @param string $name  A config parameter name
     * @param mixed  $value A config parameter value
     */
    public static function set($name, $value)
    {
        if (false !== strpos($name, '.')) {
            $pt = &self::$_config;
            $parts = explode('.', $name);
            $last = array_pop($parts);
            foreach ($parts as $part) {
                if (!isset($pt[$part]))
                    $pt[$part] = array();

                $pt = &$pt[$part];
            }
            $pt[$last] = $value;
        }

        self::$_config[$name] = $value;
    }

    /**
     * Sets an array of config parameters.
     *
     * If an existing config parameter name matches any of the keys in the supplied
     * array, the associated value will be overridden.
     *
     * @param array $parameters An associative array of config parameters and their associated values
     */
    public static function add($parameters = array())
    {
        self::$_config = array_merge(self::$_config, $parameters);
    }

    /**
     * Retrieves all configuration parameters.
     *
     * @return array An associative array of configuration parameters.
     */
    public static function getAll()
    {
        return self::$_config;
    }

    /**
     * Retrieve a config value from the module config (all values if no param name is provided)
     * @param string $name [OPTIONAL] the property
     * @param mixed $default [OPTIONAL] the default value null
     */
    static public function getModuleConfig($name = '', $default = null)
    {
        $bootstrap = Zend_Controller_Front::getInstance()->getRouter()->getParam('bootstrap');

        if (!$name) {
            return $bootstrap->getOptions();
        } else {
            $source = $bootstrap->getOptions();
            $value = self::_extractValueFromArray($name, $source);
            if ($value !== null)
                return $value;
            else
                return $default;
        }
    }

    /**
     * Clears all current config parameters.
     * @static
     * @return void
     */
    public static function clear()
    {
        self::$_config = array();
    }
}
