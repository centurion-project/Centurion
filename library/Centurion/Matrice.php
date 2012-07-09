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
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 *
 * @category    Centurion
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Matrice
{
    protected $_data = array();
    protected $_connection = array();
    
    /**
     *
     * Return true if $a and $b are directly connected in this way
     *
     * @param int|string $a
     * @param int|string $b
     * return bool
     */
    public function isConnected($a, $b)
    {
        if (!isset($this->_connection[$a])) {
            return false;
        }
        
        if (!isset($this->_connection[$a][$b])) {
            return false;
        }

        return true;
    }
    
    /**
     *
     * Connect $a with $b. If $twoWay is true, also connect $b with $a
     *
     * @param int|string $a
     * @param int|string $b
     * @param bool $oneWay
     */
    public function connect($a, $b, $twoWay = false)
    {
        if (!isset($this->_connection[$a]))
            $this->_connection[$a] = array();
        
        $this->_connection[$a][$b] = true;
        
        if (!isset($this->_data[$a]))
            $this->_data[$a] = array();
        
        $this->_data[$a][$b] = $b;
        
        if ($twoWay) {
            $this->connect($b, $a);
        }
    }
    
    /**
     *
     * Remove the connection between $a and $b. If $twoWay is true, also remove connection between $b and $a.
     *
     * @param int|string $a
     * @param int|string $b
     * @param bool $oneWay
     */
    public function removeConnection($a, $b, $twoWay = false) {
        if (isset($this->_connection[$a])) {
            unset($this->_connection[$a][$b]);
        }
        
        if (isset($this->_data[$a])) {
            unset($this->_data[$a][$b]);
        }
        
        if ($twoWay) {
            $this->removeConnection($b, $a);
        }
    }

    /**
     * Return all node connected to $a
     * @param int|string $a
     * @return array
     */
    public function getConnections($a) {
        if (!isset($this->_data[$a]))
            return array();
        return $this->_data[$a];
    }

    /**
     *
     * Find the path between $a and $b, with a max deep of $maxLevel
     *
     * @param int|string $a
     * @param int|string $b
     * @param int $maxLevel
     * @return array
     */
    public function findPath($a, $b, $maxLevel = 3, $findAllPathOfFirstDeep = true) {
        $visited = array();
        
        $toVisit = array(
                array($a, array())
                );
        
        $found = false;
        $paths = array();
        
        if ($a == $b) {
            return array(array($a, $b));
        }
        
        $currentLevel = 0;
        
        while (count($toVisit) > 0) {
            $nextToVisit = array();
            $currentLevel++;
            foreach ($toVisit as $data) {
                $visited[$data[0]] = true;
                $nodes = $this->getConnections($data[0]);
                $data[1][] = $data[0];
                
                if (isset($nodes[$b])) {
                    $data[1][] = $b;
                    $paths[] = $data[1];

                    if ($findAllPathOfFirstDeep) {
                        //We not break here, because we want all the path of the minimum deep
                        $found = true;
                    } else {
                        break;
                    }
                }
                
                if (!$found) {
                    foreach ($nodes as $node) {
                        if (!isset($visited[$node])) {
                            $nextToVisit[] = array($node, $data[1]);
                        }
                    } 
                }
            }
            
            if (!$found && $currentLevel < $maxLevel) {
                $toVisit = $nextToVisit;
            } else {
                break;
            }
        }
        
        return $paths;
    }
}
