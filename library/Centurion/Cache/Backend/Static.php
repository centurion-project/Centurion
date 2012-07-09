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
 * @package     Centurion_Cache
 * @subpackage  Backend
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Cache
 * @subpackage  Backend
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Cache_Backend_Static extends Zend_Cache_Backend_Static implements Zend_Cache_Backend_ExtendedInterface
{
    public function _setTagged()
    {
        if (is_null($this->_tagged)) {
            $tagged = $this->getInnerCache()->load(self::INNER_CACHE_NAME);
            $this->_tagged = $tagged;
        }
    }
    /**
     * @see Zend_Cache_Backend_ExtendedInterface::getCapabilities()
     */
    public function getCapabilities()
    {
        $this->getInnerCache()->getBackend()->getCapabilities();
    }

    /**
     * 
     * @see Zend_Cache_Backend_ExtendedInterface::getFillingPercentage()
     */
    public function getFillingPercentage()
    {
        $this->getInnerCache()->getFillingPercentage();
    }

    /**
     * 
     * @see Zend_Cache_Backend_ExtendedInterface::getIds()
     */
    public function getIds()
    {
        $this->_setTagged();
        
        if (is_null($this->_tagged) || empty($this->_tagged)) {
            return array();
        }
                
        return array_keys($this->_tagged);
    }

    public function getIdsMatchingAnyTags($tags = array())
    {
        $this->_setTagged();
        
        $ids = array();
        
        foreach ($tags as $tag) {
            $urls = array_keys($this->_tagged);
            foreach ($urls as $url) {
                if (isset($this->_tagged[$url]['tags']) && in_array($tag, $this->_tagged[$url]['tags'])) {
                    $ids[] = $url;
                }
            }
        }
        
        return $ids;
    }

    public function getIdsMatchingTags($tags = array())
    {
        return $this->getInnerCache()->getIdsMatchingTags($tags);
    }

    public function getIdsNotMatchingTags($tags = array())
    {
        $this->_setTagged();
        
        $ids = array();
        
        $urls = array_keys($this->_tagged);
        foreach ($urls as $url) {
            $difference = array_diff($tags, $this->_tagged[$url]['tags']);
            if (count($tags) == count($difference)) {
                $ids[] = $url;
            }
        }
        
        return $ids;
    }

    public function getMetadatas($id)
    {
        return $this->getInnerCache()->getMetadatas($id);
    }

    public function getTags()
    {
        return $this->getInnerCache()->getTags();
    }

    public function touch($id, $extraLifetime)
    {
        $this->getInnerCache()->touch($id, $extraLifetime);
    }
}
