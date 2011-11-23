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
 * @subpackage  Pdo
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Import
 * @subpackage  Pdo
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Import_Pdo_Mysql extends Centurion_Import_Abstract
{
    /**
     * lists table relations
     *
     * Expects an array of this format to be returned with all the relationships in it where the key is 
     * the name of the foreign table, and the value is an array containing the local and foreign column
     * name
     *
     * @param string $tableName table name
     * @return void
     */
    public function listTableRelations($tableName)
    {
        $config = $this->_adapter->getConfig();
        $relations = array();
        $sql = "SELECT column_name, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.key_column_usage WHERE table_name = '" 
               . $tableName . "' AND table_schema = '" . $config['dbname'] . "' and REFERENCED_COLUMN_NAME is not NULL";
        
        $results = $this->_adapter->fetchAssoc($sql);
        foreach ($results as $result) {
            $result = array_change_key_case($result, CASE_LOWER);
            $relations[] = array('table'   => $result['referenced_table_name'],
                                 'local'   => $result['column_name'],
                                 'foreign' => $result['referenced_column_name']);
        }
        
        return $relations;
    }
}