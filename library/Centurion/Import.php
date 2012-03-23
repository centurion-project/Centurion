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
 * @package     Centurion_Import
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Import
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Import
{
    /**
     * Retrieve the importer with an adapter.
     *
     * @param Zend_Db_Adapter_Abstract|Zend_Config $adapter 
     */
    public static function factory($adapter)
    {
        /*
         * Convert Zend_Config argument to plain string
         * adapter name and separate config object.
         */
        if ($adapter instanceof Zend_Config) {
            if (isset($adapter->adapter)) {
                $adapterName = (string) $adapter->adapter;
            } else {
                $adapterName = null;
            }
            
            $importName = __CLASS__ . '_';
            $importName .= str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapterName))));
            
        } else if ($adapter instanceof Zend_Db_Adapter_Abstract) {
            $importName = str_replace('Zend_Db_Adapter', __CLASS__, get_class($adapter));
        }
        
        $dbImport = new $importName($adapter);
        
        return $dbImport;
    }
}
