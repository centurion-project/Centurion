<?php
/**
 * @class Translatable_Model_DbTable_SecondModel
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
 *      - title (non-translatable)
 *      - content (translatable)
 *      - is_active (non translatable)
 *
 * A dependant relation : first
 *
 */
class Translatable_Model_DbTable_SecondModel
    extends Centurion_Db_Table_Abstract
    implements Translation_Traits_Model_DbTable_Interface{

    protected $_primary = 'id';

    protected $_name = 'test_m_translation_second_model';

    protected $_meta = array('verboseName'   => 'test_translation_second',
                             'verbosePlural' => 'test_translations_second');

    protected $_rowClass = 'Translatable_Model_DbTable_Row_SecondModel';

    protected $_referenceMap = array(
    );

    protected $_dependentTables = array(
        'first'          =>  'Translatable_Model_DbTable_FirstModel',
    );

    public function getTranslationSpec(){
        return array(
            Translation_Traits_Model_DbTable::TRANSLATED_FIELDS => array(
                'content',
            ),
            Translation_Traits_Model_DbTable::DUPLICATED_FIELDS => array(
                'title',
                'is_active'
            ),
            Translation_Traits_Model_DbTable::SET_NULL_FIELDS => array()
        );
    }
}