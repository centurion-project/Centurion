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
 * @package     Centurion_Contrib
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
abstract class Media_Model_Adapter_Abstract
{
    /**
     * Options for current adapter
     * @var array
     */
    protected $_options = null;
    
    abstract public function save($source, $dest);
    
    abstract public function update($source, $dest);
    
    abstract public function delete($dest);
    
    abstract public function read($dest);
    
    abstract public function getUrl($dest);

    /**
     * @param array $options
     * @return Media_Model_Adapter_Abstract
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
        
        return $this;
    }

    /**
     * Setup default options getting it from config file
     * @return Media_Model_Adapter_Abstract
     */
    protected function _setupOptions()
    {
        $this->_options = Centurion_Config_Manager::get('media.params');
        
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if (null === $this->_options)
            $this->_setupOptions();
        
        return $this->_options;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getOption($key)
    {
        if (null === $this->_options)
            $this->_setupOptions();
        
        return $this->_options[$key];
    }

    /**
     * @param $pk
     * @param $effect
     * @param Zend_Date|int $mktime
     * @return string
     */
    public function getTemporaryKey($pk, $effect, $mktime = null)
    {
        if (null === $mktime) {
            $lifetime = Centurion_Config_Manager::get('media.key_lifetime');
            list($lifetimeValue, $lifetimeUnit) = sscanf($lifetime, '%d%s');
            
            $mktime = new Zend_Date();
            
            switch ($lifetimeUnit) {
                case 'j':
                    $mktime->setHour(0);
                case 'h':
                    $mktime->setMinute(0);
                case 'm':
                default:
                    $mktime->setSecond(0);
            }
        }
        
        if ($mktime instanceof Zend_Date) {
            $date = $mktime->toString('MMddYYYY-HH:mm');
        } else {
            $date = date('mdY-H:i', $mktime);
        }

        return md5($pk . $date . $effect);
    }

    /**
     * @param Centurion_Db_Table_Row_Abstract $row
     * @param string $key
     * @param $effect
     * @return bool
     */
    public function isValidKey($row, $key, $effect)
    {
        $lifetime = Centurion_Config_Manager::get('media.key_lifetime');
        list($lifetimeValue, $lifetimeUnit) = sscanf($lifetime, '%d%s');
        
        $lifetimeUnit = strtolower($lifetimeUnit);
        $date = new Zend_Date();
        
        switch ($lifetimeUnit) {
            case 'j':
                $date->setHour(0);
            case 'h':
                $date->setMinute(0);
            case 'm':
            default:
                $date->setSecond(0);
        }
        
        for ($i = 0; $i < $lifetimeValue; $i++) {
            if ($key === $row->getTemporaryKey($effect, $date)) {
                return true;
            }

            switch($lifetimeUnit) {
                case 'j':
                    $date->subDay(1);
                    break;
                case 'h':
                    $date->subHour(1);
                    break;
                case 'm':
                default:
                    $date->subMinute(1);
                    break;
            }
        }
        
        return false;
    }
}
