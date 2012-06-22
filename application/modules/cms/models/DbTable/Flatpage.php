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
class Cms_Model_DbTable_Flatpage extends Centurion_Db_Table_Abstract implements Translation_Traits_Model_DbTable_Interface, Core_Traits_Mptt_Model_DbTable_Interface
{
    const NORMAL = 1;
    const REDIRECT = 4;
    const NAV_ONLY = 2;
    const REDIRECT_BLANK = 3;


    public static $types = array(self::NORMAL         => 'Normal',
                                 self::NAV_ONLY       => 'Nav only',
                                 self::REDIRECT       => 'Redirect',
                                 self::REDIRECT_BLANK => 'Redirect (new window)');

    protected $_primary = 'id';

    protected $_name = 'cms_flatpage';

    protected $_meta = array('verboseName'   => 'flatpage',
                             'verbosePlural' => 'flatpages');

    protected $_rowClass = 'Cms_Model_DbTable_Row_Flatpage';

    protected $_selectClass = 'Cms_Model_DbTable_Select_Flatpage';

    protected $_referenceMap = array(
//        'flatpage_parent' => array(
//                'columns' => 'flatpage_parent_id',
//                'refColumns' => 'id',
//                'refTableClass' => 'Cms_Model_DbTable_Flatpage',
//                'onDelete'      => self::CASCADE
//        ),
        'cover' => array (
                'columns' => 'cover_id',
                'refColumns' => 'id',
                'refTableClass' => 'Media_Model_DbTable_File',
                'onDelete'      => self::SET_NULL,
                'onUpdate'      => self::CASCADE,
        ),
        'flatpage_template' => array(
                'columns' => 'flatpage_template_id',
                'refColumns' => 'id',
                'refTableClass' => 'Cms_Model_DbTable_FlatpageTemplate',
                'onDelete'      => self::CASCADE
        ),
    );

    protected $_dependentTables = array(
        'flatpages' => 'Cms_Model_DbTable_Flatpage'
    );

    /*public function getTranslationSpec()
    {
        return array(
            Translation_Traits_Model_DbTable::TRANSLATED_FIELDS => array(
                'title',
                'description',
                'keywords',
                'body',
                'url',
                'slug',
                'language_id'
            ),
            Translation_Traits_Model_DbTable::DUPLICATED_FIELDS => array(
                'flatpage_template_id',
            ),
            Translation_Traits_Model_DbTable::SET_NULL_FIELDS => array(
                'mptt_parent_id',
                'mptt_lft',
                'mptt_rgt',
                'mptt_level',
                'mptt_tree_id'
            )
       );
    }*/

//    protected $_attributes = array(
//        self::MPTT_TREE             =>  'tree_id',
//        self::MPTT_PARENT_REFERENCE =>  'flatpage_parent',
//        self::MPTT_PARENT_COLUMN    =>  'flatpage_parent_id',
//        self::MPTT_LEVEL            =>  'level',
//        self::MPTT_LEFT             =>  'lft',
//        self::MPTT_RIGHT            =>  'rgt',
//        self::MPTT_TREE             =>  'tree_id',
//        self::MPTT_PK               =>  'id'
//    );

    public function getTranslationSpec()
    {
        return array(
            Translation_Traits_Model_DbTable::TRANSLATED_FIELDS => array(
                'title',
                'description',
                'keywords',
                'body',
                'flatpage_template_id',
            ),
            Translation_Traits_Model_DbTable::DUPLICATED_FIELDS => array(
                'is_published',
            ),
            Translation_Traits_Model_DbTable::SET_NULL_FIELDS => array(

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
