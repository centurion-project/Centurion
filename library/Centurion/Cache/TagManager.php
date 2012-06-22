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
 * @todo        Documentation
 */
class Centurion_Cache_TagManager
{
    /**
     * stack of cache's id
     *
     * @var [int]string
     */
    protected static $_stack = array();

    /**
     *
     *
     * @var [string][string]string
     */
    protected static $_tags = array();

    public static function getTags($id = null)
    {
        if (null === $id) {
            return self::$_tags[end(self::$_stack)];
        } elseif (isset(self::$_tags[$id])) {
            return self::$_tags[$id];
        }

        return null;
    }

    /**
     * Return all cache tag for current cache.
     *
     * @param [int]string $tags Cache tag to merge with.
     * @param string $id Id of the current cache
     * @return [int]string Cache tags
     */
    public static function end($tags = array(), $id = null)
    {
        if ($id === null) {
            $id = array_pop(self::$_stack);
        } else {
            if (end(self::$_stack) === $id) {
                array_pop(self::$_stack);
            }
        }

        if (!is_array($tags))
            $tags = (array) $tags;

        if (isset(self::$_tags[$id])) {
            $tags += self::$_tags[$id];
            unset(self::$_tags[$id]);
        }

        if (count(self::$_stack)) {
            $previousId = end(self::$_stack);
            self::$_tags[$previousId] += $tags;
        }

        return array_unique($tags);
    }

    //TODO:
    public static function start($id, $cache, $data, $startStacking = true)
    {
        if ($data === false) {
            if ($startStacking) {
                self::$_stack[] = $id;
                self::$_tags[$id] = array();
            }
        } else {
            if (count(self::$_stack) > 0) {
                $previousId = end(self::$_stack);
                $metaData = $cache->getBackend()->getMetadatas($id);

                if (false !== $metaData && isset($metaData['tags'])){
                    self::$_tags[$previousId] += $metaData['tags'];
                }
            }
        }
    }

    public static function addTag($tags)
    {
        $store = $tags;

        if (!is_array($tags) && !($tags instanceof Zend_Db_Table_Rowset_Abstract)) {
            $tags = array($tags);
        }

        foreach ($tags as $key => $tag) {
            self::_addTag($tag);
        }

        return $store;
    }

    public static function getTagOf($tag)
    {
        if (is_string($tag)) {
            return $tag;
        } elseif (is_object($tag)) {
            if ($tag instanceof Zend_Db_Table_Row_Abstract) {
            	return $tag->getCacheTag();
            } elseif ($tag instanceof Zend_Db_Table_Abstract){
            	return $tag->getCacheTag();
            }
        }
    }

    protected static function _addTag($tag)
    {
        $lastId = end(self::$_stack);

        if (null === $lastId)
            return;
        $tag = self::getTagOf($tag);
        if ($tag !== null) {
        	foreach (self::$_stack as $id) {
        		self::$_tags[$id][$tag] = $tag;
        	}
        }
    }
}
