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
 * @package     Centurion_Controller
 * @subpackage  Router
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Controller
 * @subpackage  Router
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Controller_Router_Route_Object extends Centurion_Controller_Router_Route
{
    /**
     * Instantiates route based on passed Zend_Config structure
     *
     * @param Zend_Config $config Configuration object
     */
    public static function getInstance(Zend_Config $config)
    {
        $reqs = ($config->reqs instanceof Zend_Config) ? $config->reqs->toArray() : array();
        $defs = ($config->defaults instanceof Zend_Config) ? $config->defaults->toArray() : array();
        
        return new self($config->route, $defs, $reqs);
    }
    
    /**
     * Assemble route.
     * We just try to get all url variables, that is not isset, from the given object.
     * 
     * @param array[int]string $data
     * @param bool $reset
     * @param bool $encode
     * @param bool $partial
     * @return string Route path with user submitted parameters
     */
    public function assemble($data = array(), $reset = false, $encode = false, $partial = false)
    {
        if (isset($data['object']) && $data['object'] instanceof Centurion_Db_Table_Row_Abstract) {
            foreach ($this->_variables as $variable) {
                if (!isset($data[$variable])) {
                    if (isset($data['object']->{$variable})) {
                        $data[$variable] = $data['object']->{$variable};
                    }
                }
            }
            unset($data['object']);
        } else {
            throw new Zend_Controller_Router_Exception('No object given in a route object, of the object is not valid');
        }
        
        return parent::assemble($data, $reset, $encode, $partial);
    }
}
