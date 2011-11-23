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
 * @package     Centurion_Application
 * @subpackage  Resource
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Application
 * @subpackage  Resource
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Application_Resource_ModuleSetup extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @throws Centurion_Application_Exception
     * @return void
     */
    public function init()
    {
        $bootstrap = $this->getBootstrap();

        if (!($bootstrap instanceof Zend_Application_Bootstrap_Bootstrap)) {
            throw new Centurion_Application_Exception('Invalid bootstrap class');
        }

        $bootstrap->bootstrap('frontcontroller');
        $front = $bootstrap->getResource('frontcontroller');
        $modules = $front->getControllerDirectory();

        foreach (array_keys($modules) as $module) {
            $configPath  = $front->getModuleDirectory($module)
                         . DIRECTORY_SEPARATOR . 'configs';
            if (!file_exists($configPath)) {
                continue;
            }

            $cfgdir = new DirectoryIterator($configPath);
            $appOptions = $this->getBootstrap()->getOptions();

            foreach ($cfgdir as $file) {
                if ($file->isFile()) {
                    $filename = $file->getFilename();
                    $options = $this->_loadOptions($configPath
                             . DIRECTORY_SEPARATOR . $filename);
                    if (($len = strpos($filename, '.')) !== false) {
                        $cfgtype = substr($filename, 0, $len);
                    } else {
                        $cfgtype = $filename;
                    }

                    if (strtolower($cfgtype) == 'module') {
                        if (array_key_exists($module, $appOptions)) {
                            if (is_array($appOptions[$module])) {
                                $appOptions[$module] =
                                    array_merge($appOptions[$module], $options);
                            } else {
                                $appOptions[$module] = $options;
                            }
                        } else {
                            $appOptions[$module] = $options;
                        }
                    } else {
                        $appOptions[$module]['resources'][$cfgtype] = $options;
                    }
                }
            }

            $bootstrap->setOptions($appOptions);
        }
    }

    /**
     * Load the config file
     *
     * @param string $fullpath
     * @return array
     */
    protected function _loadOptions($fullpath)
    {
        if (!file_exists($fullpath)) {
            throw new Centurion_Application_Resource_Exception('File does not exist');
        }

        switch(substr(trim(strtolower($fullpath)), -3)) {
            case 'ini':
                $cfg = new Zend_Config_Ini($fullpath, $this->getBootstrap()->getEnvironment());
                break;
            case 'xml':
                $cfg = new Zend_Config_Xml($fullpath, $this->getBootstrap()->getEnvironment());
                break;
            default:
                throw new Zend_Config_Exception('Invalid format for config file');
                break;
        }

        return $cfg->toArray();
    }
}