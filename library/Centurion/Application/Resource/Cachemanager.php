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
class Centurion_Application_Resource_Cachemanager extends Zend_Application_Resource_Cachemanager
{
    /**
     * @return Centurion_Cache_Manager
     */
    public function getCacheManager()
    {
        if (null === $this->_manager) {
            $this->_manager = new Centurion_Cache_Manager();

            $options = $this->getOptions();
            foreach ($options as $key => $value) {
                if ($this->_manager->hasCacheTemplate($key)) {
                    $this->_manager->setTemplateOptions($key, $value);
                } else {
                    $this->_manager->setCacheTemplate($key, $value);
                }
            }
        }

        Zend_Registry::set('Cache_Manager', $this->_manager);

        return parent::getCacheManager();
    }
}