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
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Application
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Application extends Zend_Application
{
    /**
     * @throws Zend_Application_Exception
     * @param $environment
     * @param null $options
     */
    public function __construct($environment, $options = null)
    {
        $this->_environment = (string) $environment;
        require_once 'Zend/Loader/Autoloader.php';
        $this->_autoloader = Zend_Loader_Autoloader::getInstance();

        if (is_string($options) && is_dir($options)) {
            $config = Centurion_Config_Directory::loadConfig($options, $environment);
            $this->setOptions($config);
        } else {
            if (null !== $options) {
                if (is_string($options)) {
                    $options = $this->_loadConfig($options);
                } elseif ($options instanceof Zend_Config) {
                    $options = $options->toArray();
                } elseif (!is_array($options)) {
                    throw new Zend_Application_Exception('Invalid options provided; must be location of config file, a config object, or an array');
                }
    
                $this->setOptions($options);
            }
        }
    }
    /**
     * Set options for Centurion_Config_Manager
     *
     * @param array $options Options
     * @return $this
     */
    public function setOptions(array $options)
    {
        parent::setOptions($options);
        Centurion_Config_Manager::add($options);
        
        return $this;
    }
}
