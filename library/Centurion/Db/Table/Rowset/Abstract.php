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
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
abstract class Centurion_Db_Table_Rowset_Abstract extends Zend_Db_Table_Rowset
{
    /**
     * Reference row.
     *
     * @var Centurion_Db_Table_Row_Abstract
     */
    protected $_refRow = null;

    /**
     * Table intersection in Many To Many case.
     *
     * @var string
     */
    protected $_intersectionColumns = null;

    /**
     * Table intersection class name.
     *
     * @var string|Centurion_Db_Table_Abstract
     */
    protected $_intersectionTableClass = null;

    /**
     * @return void
     * @todo documentation
     */
    public function init()
    {
         // Fill $this->_rows with null. Needed by $this->random()
         if ($this->_count)
             $this->_rows = array_fill(0, $this->_count, null);
    }

    /**
     * In Many To Many case, add a specific object, ex: add a permission to an user.
     *
     * @param Centurion_Db_Table_Row_Abstract $object
     * @return void|Centurion_Db_Table_Row_Abstract
     */
    public function add($object)
    {
        if (null !== $this->getIntersectionColumns()) {
            if (is_string($this->_intersectionTableClass))
                $this->_intersectionTableClass = Centurion_Db::getSingletonByClassName($this->_intersectionTableClass);
            list($intersectionRow, $created) = $this->_intersectionTableClass->getOrCreate(array(
                $this->_intersectionColumns['local']   =>  $this->getRefRow()->pk,
                $this->_intersectionColumns['foreign'] =>  $object->pk,
            ));

            return $intersectionRow;
        }
    }

    /**
     * @return void
     * @todo documentation
     */
    public function __wakeup()
    {
        $this->_table = Centurion_Db::getSingletonByClassName($this->_tableClass);
        $this->_connected = true;
    }

    /**
     * Set the reference row.
     *
     * @param Centurion_Db_Table_Row_Abstract $refRow
     * @return Centurion_Db_Table_Rowset_Abstract
     */
    public function setRefRow($refRow)
    {
        $this->_refRow = $refRow;

        return $this;
    }

    /**
     * Get the reference row.
     *
     * @return Centurion_Db_Table_Row_Abstract
     */
    public function getRefRow()
    {
        return $this->_refRow;
    }

    /**
     * Set the intersection columns of the intersection table.
     * array('local'    =>  'local_column',
     *       'foreign'  =>  'foreign_column')
     *
     * @param string $intersectionColumns
     * @return Centurion_Db_Table_Rowset_Abstract
     */
    public function setIntersectionColumns($intersectionColumns)
    {
        $this->_intersectionColumns = $intersectionColumns;

        return $this;
    }

    /**
     * Get the intersection columns
     *
     * @return array
     */
    public function getIntersectionColumns()
    {
        return $this->_intersectionColumns;
    }

    /**
     * Set the intersection table class
     *
     * @param string $intersectionTableClass
     * @return Centurion_Db_Table_Rowset_Abstract
     */
    public function setIntersectionTableClass($intersectionTableClass)
    {
        $this->_intersectionTableClass = $intersectionTableClass;

        return $this;
    }

    /**
     * Get the intersection table class.
     *
     * @return string
     */
    public function getIntersectionTableClass()
    {
        return $this->_intersectionTableClass;
    }

    /**
     * @return Centurion_Db_Table_Rowset_Abstract
     * @todo documentation
     */
    public function random()
    {
        $rand = range(0, $this->_count - 1);
        shuffle($rand);
        array_multisort($rand, $this->_data, $this->_rows);

        return $this;
    }

    /**
     * Return a random row
     * @return null|Zend_Db_Table_Row
     */
    public function randomRow()
    {
        return $this->_count === 0 ? null : $this->getRow(mt_rand(0, $this->_count - 1));
    }

    /**
     * @return void
     * @todo documentation
     */
    /**
     * Returns all data as an array.
     *
     * Updates the $_data property with current row object values.
     *
     * @return array
     */
    public function toArray()
    {
        // @todo This works only if we have iterated through
        // the result set once to instantiate the rows.
        foreach ($this->_rows as $i => $row) {
            if (null !== $row)
                $this->_data[$i] = $row->toArray();
        }

        return $this->_data;
    }

    
    /**
     * Delete all rows in rowset.
     * 
     * @return void 
     */
    public function delete()
    {
        foreach ($this as $row) {
            $row->delete();
        }
    }

    /**
     * @return array
     * @todo documentation
     */
    public function getSelectData()
    {
        $data = array();
        foreach ($this as $row) {
            $data[$row->id] = (string) $row;
        }
        return $data;
    }
}
