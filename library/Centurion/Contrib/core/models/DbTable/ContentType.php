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
 * @package     Centurion_Model
 * @subpackage  DbTable
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Model
 * @subpackage  DbTable
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Core_Model_DbTable_ContentType extends Centurion_Db_Table_Abstract
{
    protected $_name = 'centurion_content_type';
    
    protected $_rowClass = 'Core_Model_DbTable_Row_ContentType';

    /**
     * Stored id from db to avoid sql request
     * @var array[int]string
     */
    protected $_cachedId = array();

    /**
     * @param string|Centurion_Db_Table_Abstract|Centurion_Db_Table_Row_Abstract $name
     * @return string the id
     * @throws Centurion_Db_Table_Exception
     * @todo add some test unit
     */
    public function getContentTypeIdOf($name)
    {
        if ($name instanceof Centurion_Db_Table_Abstract) {
            $name = get_class($name);
        } elseif ($name instanceof Centurion_Db_Table_Row_Abstract) {
            $name = get_class($name->getTable());
        }

        if (!is_string($name)) {
            throw new Centurion_Db_Table_Exception('Unknown type');
        }

        if (!isset($this->_cachedId[$name])) {
            list($row, ) = $this->getOrCreate(array('name' => $name));
            $this->_cachedId[$name] = $row->id;
        }

        return $this->_cachedId[$name];
    }
}
