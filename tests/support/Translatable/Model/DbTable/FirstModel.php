<?php
/**
 * @class Translatable_Model_DbTable_FirstModel
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
 *      - second1_id (transltable)
 *      - second2_id (non-translatable)
 *      - is_active (non-translatable)
 *
 * Two parent relations :
 *      - second1 (translatable)
 *      - second2 (non-translatable)
 *
 * Two dependents relations ;
 *      - thirds
 *      - fourths
 *
 */
class Translatable_Model_DbTable_FirstModel
        extends Centurion_Db_Table_Abstract
        implements Translation_Traits_Model_DbTable_Interface{

    protected $_primary = 'id';

    protected $_name = 'test_m_translation_first_model';

    protected $_meta = array('verboseName'   => 'test_translation_first',
                             'verbosePlural' => 'test_translations_first');

    protected $_rowClass = 'Translatable_Model_DbTable_Row_FirstModel';

    protected $_referenceMap = array(
        'second1'   =>  array(
            'columns'       => 'second1_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Translatable_Model_DbTable_SecondModel',
            'onDelete'      => self::SET_NULL
        ),
        'second2'   =>  array(
            'columns'       => 'second2_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Translatable_Model_DbTable_SecondModel',
            'onDelete'      => self::SET_NULL
        ),
    );

    protected $_dependentTables = array(
        'thirds'          =>  'Translatable_Model_DbTable_ThirdModel',
        'fourths'          =>  'Translatable_Model_DbTable_FourthModel',
    );

    public function getTranslationSpec(){
        return array(
            Translation_Traits_Model_DbTable::TRANSLATED_FIELDS => array(
                'title',
                'content',
                'second1_id'
            ),
            Translation_Traits_Model_DbTable::DUPLICATED_FIELDS => array(
                'second2_id',
                'is_active'
            ),
            Translation_Traits_Model_DbTable::SET_NULL_FIELDS => array()
        );
    }
}