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
 * @package     Centurion_Paginator
 * @subpackage  Adapter
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Paginator
 * @subpackage  Adapter
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Nicolas Duteil <nd@octaveoctave.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Paginator_Adapter_DbTable extends Zend_Paginator_Adapter_DbSelect
{
    /**
     * Constructor.
     *
     * @param Zend_Db_Table_Select $select The select query
     */
    public function __construct(Zend_Db_Table_Select $select)
    {
        $this->_select = $select;
    }

    /**
     * Returns a rowset object
     *
     * @param  integer $offset Page offset
     * @param  integer $itemCountPerPage Number of items per page
     * @return Zend_Db_Table_Rowset
     */
    public function getItems($offset, $itemCountPerPage)
    {
        return $this->_select->limit($itemCountPerPage, $offset)->fetchAll();
    }

    /**
     * Returns the total number of rows in the result set.
     *
     * @return integer
     */
    public function count()
    {
        if (null === $this->_rowCount) {
            $rowCount = (int) $this->_select->count();
            $this->setRowCount($rowCount);
        }

        return $this->_rowCount;
    }
}
