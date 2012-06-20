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

}
