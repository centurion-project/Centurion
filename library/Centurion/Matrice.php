<?php

class Centurion_Matrice
{
    protected $_data = array();
    protected $_connection = array();
    
    /**
     * 
     * @param int $a
     * @param int $b
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
     * @param unknown_type $a
     * @param unknown_type $b
     * @param unknown_type $oneWay
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
     * @param unknown_type $a
     * @param unknown_type $b
     * @param unknown_type $oneWay
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
    
    public function getConnections($a) {
        if (!isset($this->_data[$a]))
            return array();
        return $this->_data[$a];
    }
    
    public function findPath($a, $b, $maxLevel = 3) {
        $visited = array();
        
        $toVisite = array(
                array($a, array())
                );
        
        $found = false;
        $paths = array();
        
        if ($a == $b) {
            return array(array($a, $b));
        }
        
        $currentLevel = 0;
        
        while (count($toVisite) > 0) {
            $nextToVisit = array();
            $currentLevel++;
            foreach ($toVisite as $data) {
                $nodes = $this->getConnections($data[0]);
                $data[1][] = $data[0];
                
                if (isset($nodes[$b])) {
                    $found = true;
                    $data[1][] = $b;
                    $paths[] = $data[1];
                }
                
                if (!$found) {
                    foreach ($nodes as $node) {
                        if (!isset($visited[$node])) {
                            $nextToVisit[] = array($node, $data[1]);
                            $visited[$node] = true;
                        }
                    } 
                }
            }
            
            if (!$found && $currentLevel < $maxLevel) {
                $toVisite = $nextToVisit;
            } else {
                break;
            }
        }
        
        return $paths;
    }
}