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
 * @author      Laurent Chenay <lc@octaveoctave.com>
 */
class Centurion_Cache_Frontend_Page extends Zend_Cache_Frontend_Page
{
    /**
     * Start the cache
     *
     * @param  string  $id       (optional) A cache id (if you set a value here, maybe you have to use Output frontend instead)
     * @param  boolean $doNotDie For unit testing only !
     * @return boolean True if the cache is hit (false else)
     */
    public function start($id = false, $doNotDie = false)
    {
        $return = parent::start($id, $doNotDie);

        if (false === $return && true === $this->_activeOptions['cache'] && (false !== $id || $this->_makeId())) {
            Centurion_Cache_TagManager::start((false !== $id) ? $id : $this->_makeId(), $this, $return);
        }

        return $return;
    }

    /**
     * Add a tag to the current cache (if have one)
     *
     * @param string|Centurion_Db_Table_Abstract|Centurion_Db_Table_Row_Abstract $tag
     * @return Centurion_Cache_Frontend_Page
     */
    public function addTag($tag)
    {
        Centurion_Cache_TagManager::addTag($tag);

        return $this;
    }

    /**
     * Callback for output buffering
     * (shouldn't really be called manually)
     *
     * @param  string $data Buffered output
     * @return string Data to send to browser
     */
    public function _flush($data)
    {
        $this->_activeOptions['tags'] = Centurion_Cache_TagManager::end($this->_activeOptions['tags']);

        return parent::_flush($data);
    }

    /**
     * Specific setter for the 'regexps' option (with some additional tests)
     *
     * @param  array $options Associative array
     * @return void
     */
    public function setRegexps($regexps)
    {
        return $this->_setRegexps($regexps);
    }
}