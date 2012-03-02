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
 * @package     Centurion_Tool
 * @subpackage  Provider
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

require_once 'Centurion/Tool/Project/Provider/Abstract.php';

require_once 'Centurion/Loader/PluginLoader.php';
require_once 'Centurion/Cache/Manager.php';
require_once 'Centurion/Signal.php';
require_once 'Centurion/Access.php';
require_once 'Centurion/Collection.php';
require_once 'Centurion/Signal/Abstract.php';
require_once 'Centurion/Config/Directory.php';
require_once 'Centurion/Iterator/Directory.php';


/**
 * @category    Centurion
 * @package     Centurion_Tool
 * @subpackage  Provider
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Tool_Project_Provider_Cache extends Centurion_Tool_Project_Provider_Abstract
{
    const DEFAULT_ENVIRONMENT = 'development';

    /**
     * Clear cache with "clear cache" command.
     */
    public function clear($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = null, $environment = self::DEFAULT_ENVIRONMENT)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        define('APPLICATION_PATH', $this->_loadedProfile->search('applicationDirectory')->getPath());

        $config = Centurion_Config_Directory::loadConfig(APPLICATION_PATH . '/configs', $environment);
        
        $cacheManager = new Centurion_Cache_Manager();
        $options = $config['resources']['cachemanager'];
        
        foreach ($options as $key => $value) {
            if ($cacheManager->hasCacheTemplate($key)) {
                $cacheManager->setTemplateOptions($key, $value);
            } else {
                $cacheManager->setCacheTemplate($key, $value);
            }
        }
        
        if (null === $tags || (!is_array($tags) && ! is_string($tags))) {
            $tags = array();
        }

        if (is_string($tags)) {
            $tags = explode(',', $tags);
        }

        foreach ($cacheManager->getBackends() as $key => $cache) {
            $this->_registry->getResponse()->appendContent(sprintf(">>> (%s) %s", $key, get_class($cache)));

            if ($cache instanceof Zend_Cache_Backend_ExtendedInterface) {
                switch ($mode) {
                    case Zend_Cache::CLEANING_MODE_ALL:
                        $ids = $cache->getIds();
                        break;
                    case Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                        $ids = $cache->getIdsMatchingAnyTags($tags);
                        break;
                    case Zend_Cache::CLEANING_MODE_MATCHING_TAG:
                        $ids = $cache->getIdsMatchingTags($tags);
                        break;
                    case Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
                        $ids = $cache->getIdsNotMatchingTags($tags);
                        break;
                    case Zend_Cache::CLEANING_MODE_OLD:
                        $ids = $cache->getIds();
                        break;
                }
            }else{
				$this->_registry->getResponse()->appendContent('not extended');
			}

            $cache->clean($mode, $tags);

            if ($cache instanceof Zend_Cache_Backend_ExtendedInterface) {
                if ($mode === Zend_Cache::CLEANING_MODE_OLD) {
                    $ids = array_diff($ids, $cache->getIds());
                }

                if (count($ids)) {
                    foreach($ids as $val) {
                        $this->_registry->getResponse()->appendContent(sprintf('>> %-9s %s', 'id-', $val));
                    }
                }
            }
        }

        if ($mode === Zend_Cache::CLEANING_MODE_ALL) {
            Centurion_Loader_PluginLoader::clean();
        }
     }
}