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
 * @package     Centurion_Node
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Node
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Node implements Countable, IteratorAggregate
{
    const DEFAULT_CONNECTOR = 'DEFAULT';
    
    protected $_children = null;
    
    protected $_connector = null;
    
    protected $_negated = false;
    
    public function __construct($children = array(), $connector = self::DEFAULT_CONNECTOR, $negated = false)
    {
        $this->_children = $children;
        $this->_connector = $connector;
        $this->_negated = $negated;
    }
    
    public static function newInstance($children = null, $connector = null, $negated = false)
    {
        return new self($children, $connector, $negated);
    }
    
    public function __toString()
    {
        if (!$this->_negated) {
            return sprintf('(NOT (%s: %s))', $this->_connector, implode(', ', $this->_children));
        }
        
        return sprintf('(%s: %s)', $this->_connector, implode(', ', $this->_children));
    }
    
    public function contains($other)
    {
        return in_array($other, $this->_children);
    }
    
    public function getChildren()
    {
        return $this->_children;
    }
    
    public function count()
    {
        return count($this->_children);
    }
    
    public function getIterator()
    {
        return new ArrayIterator($this->_children);
    }

    /**
     * @param $node
     * @param $connType
     * @return $this
     */
    public function add($node, $connType)
    {
        if ($this->contains($node) && $this->_connector === $connType) {
            return;
        }
        
        if (count($this->_children) < 2) {
            $this->_connector = $connType;
        }
        
        if ($this->_connector === $connType) {
            if ($node instanceof self && ($node->getConnector() === $connType || count($node) == 1)) {
                $this->_children = array_merge($this->_children, $node->getChildren());
            } else {
                array_push($this->_children, $node);
            }
        } else {
            $obj = self::newInstance($this->_children, $this->_connector, $this->_negated);
            $this->_connector = $connType;
            $this->_children = array($obj, $node);
        }
        
        return $this;
    }

    /**
     * @return $this
     */
    public function negate()
    {
        $this->_children = array(self::newInstance($this->_children, $this->_connector, !$this->_negated));
        $this->_connector = self::DEFAULT_CONNECTOR;
        
        return $this;
    }
}
