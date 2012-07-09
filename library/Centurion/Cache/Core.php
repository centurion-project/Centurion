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
 * @package     Centurion_Contrib
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Cache
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Cache_Core extends Zend_Cache_Core
{
    public function load($id, $doNotTestCacheValidity = false, $doNotUnserialize = false)
    {
        if (!$this->_options['caching']) {
            return false;
        }
        
        $data = parent::load($id, $doNotTestCacheValidity, $doNotUnserialize);
        Centurion_Cache_TagManager::start($id, $this, $data, false);
        
        return $data;
    }

    /**
     * @param $tag
     * @return $this
     */
    public function addTag($tag)
    {
        Centurion_Cache_TagManager::addTag($tag);
        return $this;
    }
    
    public function save($data, $id = null, $tags = array(), $specificLifetime = false, $priority = 8)
    {
        return parent::save($data, $id, $tags, $specificLifetime, $priority);
        //return parent::save($data, $id, Centurion_Cache_TagManager::end($tags, $id), $specificLifetime, $priority);
    }
}
