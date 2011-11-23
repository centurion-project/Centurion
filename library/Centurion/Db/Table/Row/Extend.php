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
 * @package     Centurion_Db
 * @subpackage  Table
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Db
 * @subpackage  Table
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Db_Table_Row_Extend extends Centurion_Db_Table_Row_Abstract
{
    protected $_extend = null;
    
    public function __get($column)
    {
        $value = parent::__get($column);
        
        if (null === $value) {
            $current = $this->getTable()->getReferenceMap($this->_getExtend());
            
            if (isset($this->{$current['columns']})
                && null !== parent::__get($current['columns'])
                && $this->pk !== $this->{$current['columns']}
                && ($parent = parent::__get($this->_getExtend()))
                && $this->_isExtendable($column)
            ) {
                    return $parent->{$column};
            }
        }
        
        return $value;
    }
    
    public function save()
    {
        $current = $this->getTable()->getReferenceMap($this->_getExtend());
        if (isset($this->{$current['columns']}) && $this->pk !== $this->{$current['columns']}) {
            foreach ($this->_data as $key => &$value) {
                if (!$this->_isExtendable($key) || $value != $this->{$this->_getExtend()}->$key) {
                    continue;
                }
                
                $value = null;
            }
        }
        
        return parent::save();
    }
    
    public function toArray()
    {
        $data = array();
        foreach ($this->_data as $key => $value) {
            $data[$key] = $this->{$key};
        }
        
        return $data;
    }
    
    protected function _isExtendable($column)
    {
        if (is_array($this->_extend)) {
            list($referenceMap, $keys) = $this->_extend;
            
            return in_array($column, $keys);
        }
        
        return true;
    }
    
    protected function _getExtend()
    {
        if (is_array($this->_extend)) {
            list($referenceMap, $keys) = $this->_extend;
            
            return $referenceMap;
        }
        
        return $this->_extend;
    }
}