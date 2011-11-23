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
 * @package     Centurion_Controller
 * @subpackage  Plugin
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Controller
 * @subpackage  Plugin
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Controller_Plugin_ModuleBootstrap extends Zend_Controller_Plugin_Abstract
{
    const MODULE_APPLICATION_INI = "configs/module.ini";

    /**
     * Boostrap instance.
     *
     * @var Zend_Application_Bootstrap_Bootstrap
     */
    protected $_bootstrap = null;

    /**
     * Autoloader.
     *
     * @var Zend_Application_Module_Autoloader
     */
    protected $_autoloader = null;

    /**
     * Called before Zend_Controller_Front enters its dispatch loop.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $moduleName = $request->getModuleName();
        if (empty($moduleName)) {
            $moduleName = 'default';
        }

        $front = Zend_Controller_Front::getInstance();
        $moduleDir = $front->getModuleDirectory($moduleName);

        if (empty($moduleDir)) {
            $moduleDir = APPLICATION_PATH;
        }

        defined('MODULE_PATH')
            || define('MODULE_PATH', (getenv('MODULE_PATH') ? getenv('MODULE_PATH') : $moduleDir));

        defined('MODULE_NAME')
            || define('MODULE_NAME', (getenv('MODULE_NAME') ? getenv('MODULE_NAME') : $moduleName));

        $this->_autoloader = new Zend_Application_Module_Autoloader(array('namespace' => $moduleName , 'basePath' => $moduleDir));
        $this->_bootstrap = $front->getParam('bootstrap');
        $globalOptions = $this->_bootstrap->getOptions();
        $options = array();

        if (isset($globalOptions['default'])) {
            $options = $globalOptions['default'];
        }

        if (isset($globalOptions[$moduleName])) {
            $options = $this->_bootstrap->mergeOptions($options, $globalOptions[$moduleName]);
        }

        $applicationFile = $moduleDir . DIRECTORY_SEPARATOR . self::MODULE_APPLICATION_INI;
        if (isset($globalOptions['application_file'])) {
            $applicationFile = $moduleDir . DIRECTORY_SEPARATOR . $globalOptions['application_file'];
        }

        $options = $this->_bootstrap->mergeOptions($options, $this->_loadConfig($applicationFile));
        if (isset($globalOptions['disable'])) {
            $options = $this->unsetOptions($options, $globalOptions['disable']);
        }

        $this->_bootstrap->setOptions($options);
        $this->_bootstrap->bootstrap();
    }

    /**
     * Unset options recursively.
     *
     * @param  array $array1
     * @param  mixed $array2
     * @return array
     */
    public function unsetOptions(array $array1, $array2 = null)
    {
        if (is_array($array2)) {
            foreach ($array2 as $key => $val) {
                if (!isset($array1[$key])) {
                    continue;
                }

                if (is_array($array2[$key])) {
                    $array1[$key] = $this->unsetOptions($array1[$key], $array2[$key]);
                } else {
                    unset($array1[$key]);
                }
            }
        }

        return $array1;
    }

    /**
     * Based heavily on Zend_Application->_loadConfig
     * Load configuration file of options.
     *
     * @param  string $file
     * @throws Zend_Application_Exception When invalid configuration file is provided
     * @return array
     */
    protected function _loadConfig($file)
    {
        if (!Zend_Loader::isReadable($file)) {
            return;
        }

        $environment = $this->_bootstrap->getApplication()->getEnvironment();
        $suffix = strtolower(pathinfo($file, PATHINFO_EXTENSION));

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
            default:
                throw new Zend_Application_Exception('Invalid configuration file provided; unknown config type');
                break;
        }

        return $config->toArray();
    }
}