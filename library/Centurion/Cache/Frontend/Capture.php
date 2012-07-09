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
 * @subpackage  Frontend
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Cache
 * @subpackage  Frontend
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Cache_Frontend_Capture extends Zend_Cache_Frontend_Capture
{
    /**
     * If true, the page won't be cached
     *
     * @var boolean
     */
    protected $_cancel = false;
    
    /**
     * Start the cache
     *
     * @param  string  $id                     Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @param  boolean $echoData               If set to true, datas are sent to the browser if the cache is hit (simpy returned else)
     * @return mixed True if the cache is hit (false else) with $echoData=true (default) ; string else (datas)
     */
    public function start($id, array $tags, $extension = null)
    {
        parent::start($id, $tags, $extension);
        Centurion_Cache_TagManager::start($id, $this, false);
        return false;
    }

     /**
     * Add a tag to the current cache (if have one)
     *
     * @param string|Centurion_Db_Table_Abstract|Centurion_Db_Table_Row_Abstract $tag
     * @return $this
     */
    public function addTag($tag)
    {
        Centurion_Cache_TagManager::addTag($tag);
        return $this;
    }

    /**
     * Stop the cache
     *
     * @param  array   $tags             Tags array
     * @param  int     $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @param  string  $forcedDatas      If not null, force written datas with this
     * @param  boolean $echoData         If set to true, datas are sent to the browser
     * @param  int     $priority         integer between 0 (very low priority) and 10 (maximum priority) used by some particular backends
     * @return void
     */
    public function _flush($data)
    {
        if ($this->_cancel) {
            return $data;
        }
        
        $this->_tags = Centurion_Cache_TagManager::end($this->_tags);
        return parent::_flush($data);
    }
    
    /**
     * Cancel the current caching process
     */
    public function cancel()
    {
        $this->_cancel = true;
    }

    /**
     * @param string $extension
     * @return $this
     */
    public function setExtention($extension = 'html')
    {
        $this->_extension = $extension;
        return $this;
    }
}
