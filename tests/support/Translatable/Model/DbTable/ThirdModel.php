<?php
/**
 * @class Translatable_Model_DbTable_ThirdModel
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
 *      - title (non-translatable field, set-null)
 *      - content (non translatable)
 *      - is_active (non translatable)
 *      - first_id (translatable)
 *
 * - A parent relation : first (translatable)
 *
 */
class Translatable_Model_DbTable_ThirdModel
    extends Centurion_Db_Table_Abstract
    implements Translation_Traits_Model_DbTable_Interface{

    protected $_primary = 'id';

    protected $_name = 'test_m_translation_third_model';

    protected $_meta = array('verboseName'   => 'test_translation_third',
                             'verbosePlural' => 'test_translations_third');

    protected $_rowClass = 'Translatable_Model_DbTable_Row_ThirdModel';

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
                'first_id',
            ),
            Translation_Traits_Model_DbTable::DUPLICATED_FIELDS => array(
                'content',
                'is_active'
            ),
            Translation_Traits_Model_DbTable::SET_NULL_FIELDS => array(
                'title',
            )
        );
    }

    /**
     * To check the behavior of the trait translation where the method ifNotExistsGetDefault is overwritten
     * @return bool
     */
    public function ifNotExistsGetDefault(){
        return true;
    }
}