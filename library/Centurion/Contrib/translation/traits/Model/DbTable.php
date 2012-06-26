<?php
/**
 * @class Translation_Traits_Model_DbTable
 * Trait to support translation versionning
 *
 * @package Centurion
 * @subpackage Transaltion
 * @author Mathias Desloges, Laurent Chenay, Richard DELOGE, rd@octaveoctave.com
 * @copyright Octave & Octave
 */
class Translation_Traits_Model_DbTable
    extends Core_Traits_Version_Model_DbTable{

    /**
     * To store the SQL name of the table defined in the model
     * @var string
     */
    protected $_modelName = null;

    /**
     * Constant used to define Translation Spec in each model
     */
    const TRANSLATED_FIELDS = 'translated_fields';
    const DUPLICATED_FIELDS = 'duplicated_fields';
    const SET_NULL_FIELDS   = 'set_null_fields';

    /**
     * Name of the field to add in each table to store the langugage of each row
     */
    const LANGUAGE_FIELD = 'language_id';

    /**
     * prefix used to name localized field in request
     * @var string
     */
    protected $_localizedColsPrefix = 'translation_localized_';

    /**
     * Name of the rule in this model to define the relation with the language
     * @var string
     */
    protected $_languageRefRule;

    /**
     * List of required columns for this trait translation
     * @var string[]
     */
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

    protected $_originalForcedToDefaultLanguage = true;

    public function __construct($model)
    {
        $this->_modelName = $model->info('name');
        parent::__construct($model);

    }

    /**
     * Method to know if the select request must take original row when the customized row does not exist.
     * Each model can overload this method to customize its behavior.
     *
     * By default the method check the configuration defined by the key Translation_Traits_Common::GET_DEFAULT_CONFIG_KEY
     * If this behavior is not defined, the trait apply the default behavior Translation_Traits_Common::NOT_EXISTS_GET_DEFAULT
     *                                                                          (it false)
     * @return boolean
     */
    public function ifNotExistsGetDefault()
    {
        // maintain backward compatibility
        if (null == $this->_notExistsGetDefault)
            $this->_notExistsGetDefault = $this->_originalNotExistsGetDefault = (bool) Centurion_Config_Manager::get(Translation_Traits_Common::GET_DEFAULT_CONFIG_KEY, Translation_Traits_Common::NOT_EXISTS_GET_DEFAULT);
            
        return $this->_model->delegateGet($this, '_notExistsGetDefault');
    }

    /**
     * To know if we must display a selector to change of language and clean
     * @return boolean
     */
    public function isOriginalForcedToDefaultLanguage()
    {
        return $this->_model->delegateGet($this, '_originalForcedToDefaultLanguage');
    }

    /**
     * Get the reference map rule for the language linked with this model (automatiquelly aded)
     * @return string
     */
    public function getLanguageRefRule()
    {
        return $this->_languageRefRule;
    }

    /**
     * Add the reference map for language and original
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

        $this->_referenceMap = $referenceMap;
        
        Centurion_Signal::factory('on_dbTable_select')->connect(array($this, 'onSelect'), $this->_model);
        Centurion_Signal::factory('on_select_joinInner')->connect(array($this, 'onJoinInner'));
    }


    /**
     * Get the prefix to rename localized fields in request
     * @return string
     */
    public function getLocalizedColsPrefix()
    {
        return $this->_localizedColsPrefix;
    }

    /**
     * add a trait to the select object
     * @see Centurion/Contrib/core/traits/Version/Model/Core_Traits_Version_Model_DbTable::onSelect()
     */
    public function onSelect($signal, $sender, $select, $applyDefaultFilters)
    {
        Centurion_Traits_Common::injectTraitIn($select, 'Translation_Traits_Model_DbTable_Select');
    }

    /**
     * add filters to the default select query
     * @see Centurion/Contrib/core/traits/Version/Model/Core_Traits_Version_Model_DbTable::onSelect()
     */
    public function onJoinInner($signal, $sender, $select, $name)
    {
        if (!$select instanceof Centurion_Db_Table_Select) {
            return;
        }

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

        
        if (in_array($childName, $select->getPart(Centurion_Db_Table_Select::FROM))) {
            echo '<pre>';
            $e = new Exception();
            print_r($e->getTraceAsString());
            die();
        }

        try {
            $select->$method(array($childName => $this->_modelInfo[Centurion_Db_Table_Abstract::NAME]),
                             new Zend_Db_Expr(sprintf($childName . '.original_id = %s.id AND ' . $childName . '.language_id = %s', $alias, $currentLanguage['id'])),
                             new Zend_Db_Expr(implode(', ', $childCols)));
                             
        }catch (Exception $e) {
//                        var_dump(array_key_exists($childName, $select->getPart(Centurion_Db_Table_Select::FROM)));
//                        var_dump($select->getPart(Centurion_Db_Table_Select::FROM));
            echo '<pre>';
            echo $e->getMessage();
            echo "\n";
            echo $e->getTraceAsString();
            echo $select->__toString()."\n";
            die();
        }
        if (!$this->_model->ifNotExistsGetDefault()) {
            $select->where(new Zend_Db_Expr(sprintf($childName . '.language_id = %u OR %s.language_id = %u', $currentLanguage['id'], $alias, $currentLanguage['id'])));
        }
    }
}
