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
 * @todo        declare columns which represent model and primary key in our model.
 * @todo        Move it to a trait
 */
class Centurion_Db_Table_Row_Proxy extends Centurion_Db_Table_Row_Abstract
{
    protected $_proxy = null;

    /**
     * Set $instance as the proxy instance of the current row.
     *
     * @param Centurion_Db_Table_Row_Abstract $instance
     * @return Centurion_Db_Table_Row_Abstract
     */
    public function setProxy(Centurion_Db_Table_Row_Abstract $instance)
    {
        return $this->_setProxy($instance);
    }

    /**
     * Set $instance as the proxy instance of the current row.
     *
     * @param Centurion_Db_Table_Row_Abstract $instance
     * @return Centurion_Db_Table_Row_Abstract
     */
    public function _setProxy(Centurion_Db_Table_Row_Abstract $instance)
    {
        list($contentTypeRow,) = Centurion_Db::getSingleton('core/contentType')->getOrCreate(array('name' => get_class($instance->getTable())));
        $this->proxy_content_type_id = $contentTypeRow->id;
        $this->proxy_pk = $instance->id;
    }

    /**
     * Get the proxy
     *
     * @return Centurion_Db_Table_Row_Abstract
     */
    public function getProxy()
    {
        return $this->_getProxy($this->proxy_content_type->name, $this->proxy_pk);
    }

    /**
     * Get the proxy
     *
     * @return Centurion_Db_Table_Row_Abstract
     */
    protected function _getProxy($model, $pk)
    {
        if (null === $this->_proxy && null !==  $pk) {
            $proxyTable = Centurion_Db::getSingletonByClassName($model);
            $this->_proxy = $proxyTable->find($pk)->current();
        }

        return $this->_proxy;
    }
}
