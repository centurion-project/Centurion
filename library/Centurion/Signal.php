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
 * @package     Centurion_Signal
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Signal
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Signal
{
    protected static $_registry = array();

    const RECEIVER = 'receiver';

    const BEHAVIOR = 'behavior';
    const BEHAVIOR_CONTINUE = 'continue';
    const BEHAVIOR_STOP_PROPAGATION = 'stop_propagation';
    const BEHAVIOR_CAN_STOP = 'can_stop';

    /**
     * @var Zend_Loader_PluginLoader_Interface
     */
    protected static $_pluginLoader;

    public static function register($key, $value, $graceful = false)
    {
        if (isset(self::$_registry[$key])) {
            if ($graceful) {
                return;
            }
            throw new Centurion_Signal_Exception(sprintf('Centurion registry key "%s" already exists', $key));
        }

        self::$_registry[$key] = $value;
    }

    public static function unregister($key = null)
    {
        if (null === $key) {
            self::$_registry = array();
        } else {
            if (isset(self::$_registry[$key])) {
                if (is_object(self::$_registry[$key]) && (method_exists(self::$_registry[$key], '__destruct'))) {
                    self::$_registry[$key]->__destruct();
                }
                unset(self::$_registry[$key]);
            }
        }
    }

    /**
     * Registry a new signal.
     *
     * @param string $key
     * @return Centurion_Signal_Abstract
     */
    public static function registry($key)
    {
        if (isset(self::$_registry[$key])) {
            return self::$_registry[$key];
        }

        return null;
    }

    /**
     *
     * @param string $key
     * @return Centurion_Signal_Abstract
     */
    public static function factory($key)
    {
        $registered = self::registry($key);
        if (is_null($registered)) {
            $className = Centurion_Inflector::classify($key);
            $className = self::getPluginLoader()->load($className);
            $registered = new $className();
            self::register($key, $registered);
        }

        return $registered;
    }

    /**
     * Set PluginLoader for use with broker
     *
     * @param  Zend_Loader_PluginLoader_Interface $loader
     * @return void
     */
    public static function setPluginLoader($loader)
    {
        if ((null !== $loader) && (!$loader instanceof Zend_Loader_PluginLoader_Interface)) {
            throw new Centurion_Signal_Exception('Invalid plugin loader provided to HelperBroker');
        }
        self::$_pluginLoader = $loader;
    }

    /**
     * Retrieve PluginLoader
     *
     * @return Zend_Loader_PluginLoader
     */
    public static function getPluginLoader()
    {
        if (null === self::$_pluginLoader) {
            self::$_pluginLoader = new Centurion_Loader_PluginLoader(array(
                'Centurion_Signal' => 'Centurion/Signal/',
            ), 'Signal');
        }

        return self::$_pluginLoader;
    }
}