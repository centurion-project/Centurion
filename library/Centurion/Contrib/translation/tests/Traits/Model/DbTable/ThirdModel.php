<?php
/**
 * @class Translation_Test_Traits_Model_DbTable_ThirdModel
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
require_once dirname(__FILE__) . '/SecondModel.php';
require_once dirname(__FILE__) . '/ThirdModel.php';
require_once dirname(__FILE__) . '/FourthModel.php';

class Translation_Test_Traits_Model_DbTable_ThirdModel
    extends Asset_Model_DbTable_Abstract
    implements Translation_Traits_Model_DbTable_Interface{

    protected $_primary = 'id';

    protected $_name = 'test_m_translation_third_model';

    protected $_meta = array('verboseName'   => 'test_translation_third',
                             'verbosePlural' => 'test_translations_third');

    protected $_rowClass = 'Translation_Test_Traits_Model_DbTable_Row_ThirdModel';

    protected $_referenceMap = array(
        'first'   =>  array(
            'columns'       => 'first_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Translation_Test_Traits_Model_DbTable_FirstModel',
            'onDelete'      => self::SET_NULL
        ),
    );

    /**
     * To change the behavior of the trait and form
     * @var bool
     */
    protected $_originalForcedToDefaultLanguage = true;

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
     * Overload for Test unit
     * @return bool
     */
    public function ifNotExistsGetDefault(){
        return true;
    }

    /**
     * To change the answer of isOriginalForcedToDefaultLanguage() and change the behavior of the trait translation
     * @param boolean $value
     */
    public function setOriginalForcedDefaultLanguage($value){
        $this->_originalForcedToDefaultLanguage = $value;
    }

    protected function _createTable()
    {
        $this->getDefaultAdapter()->query(<<<EOS
            CREATE TABLE IF NOT EXISTS `test_m_translation_third_model` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `original_id` int(11) DEFAULT NULL,
              `language_id` int(10) unsigned NOT NULL,
              `title` varchar(100) DEFAULT NULL,
              `content` text,
              `first_id` int(11) DEFAULT NULL,
              `is_active` int(1) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`),
              KEY `original_id` (`original_id`),
              KEY `language_id` (`language_id`),
              KEY `first_id` (`first_id`),
              KEY `is_active` (`is_active`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
EOS
        );
    }

    protected function _destructTable()
    {
        $this->getDefaultAdapter()->query('DROP TABLE IF EXISTS `{$this->_name}`;');
    }
}
