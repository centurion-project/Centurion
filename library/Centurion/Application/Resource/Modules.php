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
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Application_Resource_Modules extends Zend_Application_Resource_Modules
{
    /**
     * Initialize modules
     *
     * @return array[string]Centurion_Application_Module_Bootstrap
     * @throws Centurion_Application_Resource_Exception When bootstrap class was not found
     */
    public function init()
    {
        $bootstrap = $this->getBootstrap();
        $bootstrap->bootstrap('FrontController');
        
        $front = $bootstrap->getResource('FrontController');

        $modules = $front->getControllerDirectory();

        $default = $front->getDefaultModule();
        $curBootstrapClass = get_class($bootstrap);
        $options = $this->getOptions();

        if (is_array($options) && !empty($options[0])) {
            $diffs = array_diff($options, array_keys($modules));
            if (count($diffs)) {
                throw new Centurion_Application_Resource_Exception(sprintf('The modules %s is not found in your registry (%s)',
                                                                   implode(', ', $diffs),
                                                                   implode(PATH_SEPARATOR, $modules)));
            }

            foreach ($modules as $key => $module) {
                if (!in_array($key, $options) && $key !== $default) {
                    unset($modules[$key]);
                    $front->removeControllerDirectory($key);
                }
            }

            $modules = Centurion_Inflector::sortArrayByArray($modules, array_values($options));
        }

        foreach ($modules as $module => $moduleDirectory) {
            $bootstrapClass = $this->_formatModuleName($module) . '_Bootstrap';
            if (!class_exists($bootstrapClass, false)) {
                $bootstrapPath  = dirname($moduleDirectory) . '/Bootstrap.php';
                if (!file_exists($bootstrapPath)) {
                    throw new Centurion_Application_Exception('Module ' . $module . ' has no Bootstrap class' , 500);
                }

                include_once $bootstrapPath;

                if (($default != $module) && !class_exists($bootstrapClass, false)) {
                    $eMsgTpl = 'Bootstrap file found for module "%s" but bootstrap class "%s" not found';
                    throw new Centurion_Application_Resource_Exception(sprintf(
                        $eMsgTpl, $module, $bootstrapClass
                    ));
                } elseif ($default == $module) {
                    if (!class_exists($bootstrapClass, false)) {
                        $bootstrapClass = 'Bootstrap';
                        if (!class_exists($bootstrapClass, false)) {
                            throw new Zend_Application_Resource_Exception(sprintf(
                                $eMsgTpl, $module, $bootstrapClass
                            ));
                        }
                    }
                }
            }

            if ($bootstrapClass == $curBootstrapClass) {
                // If the found bootstrap class matches the one calling this
                // resource, don't re-execute.
                continue;
            }

            $moduleBootstrap = new $bootstrapClass($bootstrap);
            $moduleBootstrap->bootstrap();

            $this->_bootstraps[$module] = $moduleBootstrap;
        }
        
        return $this->_bootstraps;
    }
}
