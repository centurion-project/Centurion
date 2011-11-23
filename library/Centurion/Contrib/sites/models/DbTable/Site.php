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
 */
class Sites_Model_DbTable_Site extends Centurion_Db_Table_Abstract
{
    /**
     * Stack of loaded sites.
     *
     */
    protected static $_sites = array();

    protected $_name = 'centurion_site';

    protected $_meta = array('verboseName'   => 'site',
                             'verbosePlural' => 'sites');

    protected $_primary = 'id';

    protected $_rowClass = 'Sites_Model_DbTable_Row_Site';

    /**
     * Retrieve the current site.
     *
     * @return Sites_Model_DbTable_Row_Site
     * @throws Centurion_Exception When the current site was not found
     */
    public function getCurrent()
    {
        $options = Centurion_Config_Manager::get('centurion');

        if (!array_key_exists('site_id', $options)) {
            throw new Centurion_Exception("You're using the Centurion \"sites framework\" without having set the \"site_id\" setting in your application.ini. "
                                          . "Create a site in your database and set the \"centurion.site_id\" setting to fix this error.");
        }

        if (!isset(self::$_sites[$options['site_id']])) {
            self::$_sites[$options['site_id']] = $this->get(array($this->_primary =>  $options['site_id']));
        }

        return self::$_sites[$options['site_id']];
    }

    /**
     * Updates existing rows.
     *
     * @param  array        $data  Column-value pairs.
     * @param  array|string $where An SQL WHERE clause, or an array of SQL WHERE clauses.
     * @return int          The number of rows updated.
     */
    public function update(array $values, $where)
    {
        $siteRow = $this->fetchRow($where);

        if (in_array($siteRow->pk, self::$_sites)) {
            unset(self::$_sites[$siteRow->pk]);
        }

        return parent::update($values, $where);
    }

    /**
     * Deletes existing rows.
     *
     * @param  array|string $where SQL WHERE clause(s).
     * @return int          The number of rows deleted.
     */
    public function delete($where)
    {
        $pk = $this->fetchRow($where)->pk;

        $object = parent::delete($where);

        try {
            unset(self::$_sites[$pk]);
        } catch (Exception $e) {

        }

        return $object;
    }

    /**
     * Clear the loaded sites stack.
     *
     * @return void
     */
    public static function clear()
    {
        self::$_sites = array();
    }

    /**
     * Retrieve all loaded sites.
     *
     * @return array
     */
    public static function getSites()
    {
        return self::$_sites;
    }

    /**
     * Set the loaded sites stack.
     *
     * @param array $sites Stack of sites
     * @return void
     */
    public static function setSites($sites)
    {
        self::$_sites = $sites;
    }
}