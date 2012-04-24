<?php
/**
 * @class Translatable_Model_DbTable_FourthModel
 * @package Tests
 * @subpackage Translatable
 * @author Richard Déloge, rd@octaveoctave.com
 *
 * Model of test to check is the behavior of the trait translation is the excepted behavior.
 *
 * This model contains 7 fields :
 *      - id
 *      - original_id
 *      - language_id
 *      - title (translatable)
 *      - content (translatable)
 *      - is_active (non-translatable)
 *      - first_id (non-translatable)
 *
 *  A parent relation : first (non translatable)
 */
class Translatable_Model_DbTable_FourthModel
    extends Centurion_Db_Table_Abstract
    implements Translation_Traits_Model_DbTable_Interface{

    protected $_primary = 'id';

    protected $_name = 'test_m_translation_fourth_model';

    protected $_meta = array('verboseName'   => 'test_translation_fourth',
                             'verbosePlural' => 'test_translations_fourth');

    protected $_rowClass = 'Translatable_Model_DbTable_Row_FourthModel';

    protected $_referenceMap = array(
        'first'   =>  array(
            'columns'       => 'first_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Translatable_Model_DbTable_FirstModel',
            'onDelete'      => self::SET_NULL
        ),
    );

    public function getTranslationSpec(){
        return array(
            Translation_Traits_Model_DbTable::TRANSLATED_FIELDS => array(
                'title',
                'content',
            ),
            Translation_Traits_Model_DbTable::DUPLICATED_FIELDS => array(
                'first_id',
                'is_active'
            ),
            Translation_Traits_Model_DbTable::SET_NULL_FIELDS => array()
        );
    }

    public function ifNotExistsGetDefault(){
        return false;
    }
}