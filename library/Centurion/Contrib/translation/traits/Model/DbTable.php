<?php
class Translation_Traits_Model_DbTable extends Core_Traits_Version_Model_DbTable
{
//    $spec tamplate
//    array(
//        Translation_Traits_Model_DbTable::TRANSLATED_FIELDS => array(),
//        Translation_Traits_Model_DbTable::DUPLICATED_FIELDS => array(),
//        Translation_Traits_Model_DbTable::SET_NULL_FIELDS => array()
//    )

    protected $_modelName = null;

    const TRANSLATED_FIELDS = 'translated_fields';
    const DUPLICATED_FIELDS = 'duplicated_fields';
    const SET_NULL_FIELDS   = 'set_null_fields';

    const LANGUAGE_FIELD = 'language_id';

    protected $_localizedColsPrefix = 'translation_localized_';

    protected $_languageRefRule;

    protected $_requiredColumns = array(parent::ORIGINAL_FIELD, self::LANGUAGE_FIELD);

    /**
     * @deprecated
     * use $_notExistsGetDefault instead
     * @var bool
     */
    protected $_originalNotExistsGetDefault;

    /**
     * should get default (original) value if null is found as translation
     * @var bool
     */
    protected $_notExistsGetDefault;

    public function __construct($model)
    {
        $this->_modelName = $model->info('name');
        parent::__construct($model);

    }
    public function ifNotExistsGetDefault()
    {
        // maintain backward compatibility
        if (null == $this->_notExistsGetDefault)
            $this->_notExistsGetDefault = $this->_originalNotExistsGetDefault = (bool) Centurion_Config_Manager::get(Translation_Traits_Common::GET_DEFAULT_CONFIG_KEY, Translation_Traits_Common::NOT_EXISTS_GET_DEFAULT);
            
        return $this->_model->delegateGet($this, '_notExistsGetDefault');
    }

    protected $_originalForcedToDefaultLanguage = true;

    public function isOriginalForcedToDefaultLanguage()
    {
        return $this->_model->delegateGet($this, '_originalForcedToDefaultLanguage');
    }

    public function getLanguageRefRule()
    {
        return $this->_languageRefRule;
    }

    /**
     * (non-PHPdoc)
     * @see Centurion/Contrib/core/traits/Version/Model/Core_Traits_Version_Model_DbTable::init()
     */
    public function init()
    {
        parent::init();
        $this->_localizedColsPrefix .= $this->_modelName . '_';

        if (false === Centurion_Config_Manager::get('translation.default_language', false))
            throw new Centurion_Traits_Exception('no default language have been set in the configuration. Please add a \'translation.default_language\' entry');

        $this->_languageRefRule = $this->_addReferenceMapRule('language', 'language_id', 'Translation_Model_DbTable_Language');

        $referenceMap = $this->_referenceMap;
        $referenceMap['original'] = array(
                'columns' => 'original_id',
                'refColumns' => 'id',
                'refTableClass' => get_class($this->_model),
                'onDelete' => Zend_Db_Table_Abstract::CASCADE,
                'onUpdate' => Zend_Db_Table_Abstract::CASCADE,
            );

        $referenceMap['language'] = array(
                'columns' => 'language_id',
                'refColumns' => 'id',
                'refTableClass' => 'Translation_Model_DbTable_Language',
            	'onDelete' => Zend_Db_Table_Abstract::CASCADE,
                'onUpdate' => Zend_Db_Table_Abstract::CASCADE,
            );

        Centurion_Signal::factory('on_select_joinInner')->connect(array($this, 'onJoinInner'), $this->_model);
    }

    public function getLocalizedColsPrefix()
    {
        return $this->_localizedColsPrefix;
    }

    /**
     * add filters to the default select query
     * @see Centurion/Contrib/core/traits/Version/Model/Core_Traits_Version_Model_DbTable::onSelect()
     */
    public function onJoinInner($signal, $sender, $select, $name)
    {
        if (!$select instanceof Centurion_Db_Table_Select)
            return;

        $corellationName = 0;
        if (is_array($name)) {
            $corellationName = key($name);
            $name = current($name);
        }
        
        if (0 === $corellationName) {
            $corellationName = $name;
        }
        

        if ($name !== $this->_modelName) {
            return;
        }
            
        if (!Centurion_Db_Table_Abstract::getFiltersStatus()) {
            return;
        }

        $childName = 'child_' . $corellationName;//$this->_modelName;

        if (array_key_exists($childName, $select->getPart(Centurion_Db_Table_Select::FROM))) {
            return;
        }

        $select->setIntegrityCheck(false);
        
        
        $currentLanguage = Translation_Model_DbTable_Language::getCurrentLanguageInfo();
        
        /*
        $currentLocale = Zend_Registry::get('Zend_Translate')->getLocale();
        $session = new Zend_Session_Namespace('translation_current');

        if (!isset($session->language) || $session->language['locale'] != $currentLocale) {
            try {
                $languageRow = Centurion_Db::getSingleton('translation/language')->get(array('locale' => $currentLocale));
            } catch (Centurion_Db_Table_Row_Exception_DoesNotExist $e) {
                $languageRow = Translation_Traits_Common::getDefaultLanguage();
            }

            $session->language = $languageRow->toArray();
        }
        */

//        if ($this->_model->ifNotExistsGetDefault()) {
//            if ($session->language['locale'] == Centurion_Config_Manager::get('translation.default_language', false)) {
//                $select->filter(array('language_id' => $session->language['id']));
//                return;
//            }
//        }

        $originalCols = array();
        $childCols = array();

        $spec = $this->getTranslationSpec();
        //foreach ($this->_modelInfo[Centurion_Db_Table_Abstract::COLS] as $col) {
        foreach ($spec[Translation_Traits_Model_DbTable::TRANSLATED_FIELDS] as $col) {
            array_push($childCols, sprintf('%s.%s AS %s%s', $childName, $col, $this->_localizedColsPrefix, $col));
            array_push($originalCols, sprintf('%s.%s', $this->_modelInfo[Centurion_Db_Table_Abstract::NAME], $col));
        }

        $tableName = $this->_modelInfo[Centurion_Db_Table_Abstract::NAME];
        $joined = false;
        foreach ($select->getPart(Zend_Db_Select::FROM) as $key => $val) {
            if (strcmp($val['tableName'], $tableName) === 0) {
                $joined = true;
                $alias = $key;
            }
        }
        
        if (!$joined) {
            $select->from($this->_modelInfo[Centurion_Db_Table_Abstract::NAME], new Zend_Db_Expr(implode(', ', $originalCols)));
            $alias = $tableName;
        }

        $select->where(sprintf('%s.original_id IS NULL', $alias));

//        if ($this->_model->ifNotExistsGetDefault())
            $method = 'joinLeft';
//        else
//            $method = 'joinInner';
            
            $select->$method(sprintf('%s AS ' . $childName, $this->_modelInfo[Centurion_Db_Table_Abstract::NAME]),
                             new Zend_Db_Expr(sprintf($childName . '.original_id = %s.id AND ' . $childName . '.language_id = %s', $alias, $currentLanguage['id'])),
                             new Zend_Db_Expr(implode(', ', $childCols)));
                             
        if (!$this->_model->ifNotExistsGetDefault()) {
            $select->where(new Zend_Db_Expr(sprintf($childName . '.language_id = %u OR %s.language_id = %u', $currentLanguage['id'], $alias, $currentLanguage['id'])));
        }
    }

    /**
     * add filters to the default select query
     * @see Centurion/Contrib/core/traits/Version/Model/Core_Traits_Version_Model_DbTable::onSelect()
     */
    public function onSelect($signal, $sender, $select, $applyDefaultFilters)
    {

    }

}
