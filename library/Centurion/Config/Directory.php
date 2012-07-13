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
 * @subpackage  Directory
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * Inspired by sfConfig of Symfony project.
 *
 * @category    Centurion
 * @package     Centurion_Config
 * @subpackage  Directory
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 * @todo        Documentation
 */
class Centurion_Config_Directory
{
    protected static $_environment = null;
    
    protected static $_noCache = false;

    /**
     * @static
     * @param array $Arr1
     * @param array $Arr2
     * @return array
     */
    public static function mergeArrays($Arr1, $Arr2)
    {
        foreach($Arr2 as $key => $value) {
            if (is_string($key)) {
                if (array_key_exists($key, $Arr1) && is_array($value)) {
                    $Arr1[$key] = self::mergeArrays($Arr1[$key], $Arr2[$key]);
                } else {
                    $Arr1[$key] = $value;
                }
            } else {
                $Arr1[] = $value;
            }
        }
    
      return $Arr1;
    }

    public static function loadConfig($path, $environment, $recursivelyLoadModuleConfig = false)
    {
        self::$_environment = $environment;

        if (is_string($path) && is_dir($path)) {
            $iterator = new Centurion_Iterator_Directory($path);
            $tabFile = array();
            foreach ($iterator as $file) {
                if ($file->isDot()) {
                    continue;
                }
                $tabFile[] = $file->getPathName();
            }

            if (0 == count($tabFile)) {
                return array();
            }

            sort($tabFile);

            $backendOptions = array('cache_dir' => APPLICATION_PATH . '/../data/cache/config/' );
            $frontendOptions = array('master_files' => array_values($tabFile), 'automatic_serialization' => true, 'cache_id_prefix' => str_replace('-', '_', $environment));
            
            try {
                $cacheConfig = Zend_Cache::factory('File', 'File', $frontendOptions, $backendOptions);
            } catch (Exception $e) {
                self::$_noCache = true;
            }

            if (self::$_noCache || !($config = $cacheConfig->load(md5(implode('|', $tabFile))))) {
                $config = array();

                foreach($tabFile as $file) {
                    $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                    switch ($suffix) {
                        case 'ini':
                        case 'xml':
                        case 'php':
                        case 'inc':
                            $result = self::_loadConfig($file);
                            $config = self::mergeArrays($config, $result);
                    }
                }

                if (!self::$_noCache) {
                    $cacheConfig->save($config);
                }
            }

            if ($recursivelyLoadModuleConfig && isset($config['resources']) && isset($config['resources']['modules'])) {
                foreach ($config['resources']['modules'] as $module) {
                    $dir = null;
                    
                    if (file_exists(APPLICATION_PATH . '/../library/Centurion/Contrib/' . $module . '/configs')) {
                        $dir = APPLICATION_PATH . '/../library/Centurion/Contrib/' . $module . '/configs';
                    } else  if (file_exists(APPLICATION_PATH . '/modules/' . $module . '/configs')) {
                        $dir = APPLICATION_PATH . '/modules/' . $module . '/configs';
                    }

                    if (null !== $dir) {
                        $result = self::loadConfig($dir, $environment);
                        $config = self::mergeArrays($result, $config);
                    }
                }
            }

            return $config;
        }
        throw new Centurion_Exception('Path must be a directory', 500);
    }

    /**
     * @static
     * @param $file
     * @return false|mixed|Zend_Config_Ini|Zend_Config_Xml
     * @deprecated
     */
    protected static function _loadConfigCached($file)
    {
        $backendOptions = array('cache_dir' => APPLICATION_PATH . '/../data/cache/config/' );
        $frontendOptions = array('master_file' => $file, 'automatic_serialization' => true);

        $cacheConfig = Zend_Cache::factory('File', 'File', $frontendOptions, $backendOptions);

        if (!($config = $cacheConfig->load(md5($file)))) {
            $config = self::_loadConfig($file);
            $cacheConfig->save($config);
        }

        return $config;
    }

    /**
     * @static
     * @param $file
     * @return mixed|Zend_Config_Ini|Zend_Config_Xml
     * @throws Zend_Application_Exception
     * @see Zend_Application->_loadConfig();
     */
    protected static function _loadConfig($file) {
        $environment = self::$_environment;
        $suffix      = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        switch ($suffix) {
            case 'ini':
                $config = new Zend_Config_Ini($file, $environment);
                break;

            case 'xml':
                $config = new Zend_Config_Xml($file, $environment);
                break;

            case 'php':
            case 'inc':
                $config = include $file;
                if (!is_array($config)) {
                    throw new Zend_Application_Exception('Invalid configuration file provided; PHP file does not return array value');
                }
                return $config;
                break;

            default:
                throw new Zend_Application_Exception('Invalid configuration file provided; unknown config type');
        }

        return $config->toArray();
    }
}
