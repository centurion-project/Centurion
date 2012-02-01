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
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Controller
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Controller_Action_Helper_GetObjectOr404 extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * Retrieve an object with custom parameters, throw a Zend_Controller_Action_Exception exception if no matches found.
     *
     * @param string|Centurion_Db_Table_Abstract $objectTable The name of the Model DbTable or the object
     * @param array $params Parameters
     * @return Centurion_Db_Table_Row_Abstract
     */
    public function direct($objectTable, array $params = array())
    {
        if (is_string($objectTable)) {
            $objectTable = Centurion_Db::getSingleton($objectTable);
         } elseif (!($objectTable instanceof Centurion_Db_Table_Abstract)
                    && !($objectTable instanceof Centurion_Db_Cache)) { //To Support getCache() from Centurion Model
            throw new Centurion_Exception('Unknown type of first argument');
        }
        
        try {
            $row = $objectTable->get($params);
        } catch (Centurion_Db_Table_Row_Exception_DoesNotExist $e) {
            throw new Zend_Controller_Action_Exception(sprintf('No %s matches the given query %s.',
                                                               get_class($objectTable),
                                                               implode(', ', $params)), 404);
        }
        
        return $row;
    }
}
