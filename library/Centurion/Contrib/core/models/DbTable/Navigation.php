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
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Core_Model_DbTable_Navigation extends Centurion_Db_Table_Abstract implements Core_Traits_Mptt_Model_DbTable_Interface, Translation_Traits_Model_DbTable_Interface
{
    protected $_byProxy = array();
    protected $_name = 'centurion_navigation';

    protected $_rowClass = 'Core_Model_DbTable_Row_Navigation';

    protected $_primary = 'id';

    protected $_meta = array('verboseName'   => 'navigation',
                             'verbosePlural' => 'navigations');

    protected $_referenceMap = array(
        'parent_navigation'  =>  array(
            'columns'       => 'mptt_parent_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Core_Model_DbTable_Navigation',
            'onDelete'      => self::RESTRICT,
            'onUpdate'      => self::CASCADE,
        ),
        'model'  =>  array(
            'columns'       => 'proxy_model',
            'refColumns'    => 'id',
            'refTableClass' => 'Core_Model_DbTable_ContentType',
            'onDelete'      => self::CASCADE,
            'onUpdate'      => self::CASCADE,
        )
    );

    protected $_dependentTables = array(
        'navigations' => 'Core_Model_DbTable_Navigation',
    );

    protected $_recursiveDelete = true;

    public function getCache($frontendOptions = null, $backendOptions = null, $backendName = null)
    {
        if (null === $this->_cache) {
            parent::getCache($frontendOptions, $backendOptions, $backendName);

            //Each user have his own cache
            $identity = Centurion_Auth::getInstance()->getIdentity();
            $this->_cache->setCacheSuffix(sprintf('user_%d', ($identity === null)?0:$identity->id));
        }
        return $this->_cache;
    }


    /**
     * Convert the navigation DbTable object to Zend_Navigation.
     *
     * @return Zend_Navigation
     */
    public function toNavigation()
    {
        Centurion_Db_Table_Abstract::setFiltersStatus(true);
        //return new Zend_Navigation($this->_navigation($this->fetchAll('navigation_parent_id IS NULL')));

//        $rowset = $this->fetchAll();
//        $navigations = $this->_navigation2($rowset);
        $navigations = $this->_navigation($this->getRootNodes()->fetchAll());
        return $navigations;
    }

    public function _navigation2($navigationRowset)
    {
        $navigations = array();
        $levelPt = array(0 => &$navigations);
        $pt = &$navigations;
        $identity = Centurion_Auth::getInstance()->getIdentity();
        $previous = null;

        foreach ($navigationRowset as $navigationRow) {
            $navigationData = $navigationRow->getNavigationData($identity);
            if (null === $navigationData) {
                continue;
            }

            $pt = &$levelPt[$navigationRow->getLevel()];
            if (!$navigationRow->isLeafNode()) {
                $navigationData['pages'] = array();
                $temp = &$navigationData['pages'];
                array_push($navigationData, $pt);
                $levelPt[$navigationRow->getLevel() + 1] = &$temp;
            } else {
                array_push($navigationData, $pt);
            }

//            $previous = $navigationRow;
        }

        return $navigations;
    }

    public function findOneByProxy($row)
    {
        $contentType = Centurion_Db::getSingleton('core/contentType')->getCache()->findOneByName(get_class($row->getTable()));

        if (null === $contentType)
            return null;
        $proxyTable = $contentType->id;
        $proxyPk = $row->pk;
        return $this->fetchRow(array('centurion_navigation.proxy_pk=?' => $proxyPk, 'centurion_navigation.proxy_model=?' => $proxyTable));
    }

    /**
     * Convert a rowset to Zend_Navigation array format.
     *
     * @param Centurion_Db_Table_Rowset_Abstract $menus
     * @return array
     */
    private function _navigation(Centurion_Db_Table_Rowset_Abstract $menus, $identity = null)
    {
        $navigations = array();
        if ($identity == null) {
            $identity = Centurion_Auth::getInstance()->getIdentity();

            if (null === $identity) {
                return $navigations;
            }
        }

        foreach ($menus as $menu) {
            $pages = null;
            if (!$menu->isLeafNode()) {
                $pages = $this->_navigation($menu->getChildren());
            }

            $navigationData = $menu->getNavigationData($identity);

            if (null === $navigationData) {
                continue;
            }
            if (isset($navigationData['uri']) && $navigationData['uri'] === '#') {

                if (count($pages) > 0) {
                    if (isset($pages[0]['uri'])) {
                        $navigationData['uri'] = $pages[0]['uri'];
                    } else {
                        $navigationData['route'] = $pages[0]['route'];
                        $navigationData['module'] = $pages[0]['module'];
                        $navigationData['action'] = $pages[0]['action'];
                        $navigationData['controller'] = $pages[0]['controller'];
                        $navigationData['params'] = $pages[0]['params'];
                    }
                } else {
                    //No href, so it will be display with span tag
                    $navigationData['uri'] = '';
                }
            }

            if (null !== $pages) {
                $navigationData['pages'] = $pages;
            }

            array_push($navigations, $navigationData);
        }

        return $navigations;
    }

    public function getTranslationSpec()
    {
        return array(
            Translation_Traits_Model_DbTable::TRANSLATED_FIELDS => array(
                'label',
                'uri',
                'class'
            ),
            Translation_Traits_Model_DbTable::DUPLICATED_FIELDS => array(

            ),
            Translation_Traits_Model_DbTable::SET_NULL_FIELDS => array(
                'module',
                'controller',
                'action',
                'route',

            )
       );
    }

    public function ignoreForeignOnColumn()
    {
        return array(
            'mptt_tree_id',
        );
    }
}