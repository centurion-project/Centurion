<?php
class Translation_Traits_Model_DbTable_Row extends Centurion_Traits_Model_DbTable_Row_Abstract
{
    protected $_localBeforeSave;
    protected $_prefix = null;

    public function init()
    {
        Centurion_Signal::factory('pre_save')->connect(array($this, 'preSave'), $this->_row, Centurion_Signal::BEHAVIOR_CAN_STOP);
        $this->_prefix = $this->getTable()->getLocalizedColsPrefix();
        $special = $this->_specialGets;
        $special['original'] = array($this, 'getOriginal');
        $special['permalink'] = array($this, 'getLocalizedAbsoluteUrl');
        $this->_specialGets = $special;
    }

    public function getLocalizedAbsoluteUrl() 
    {
        return $this->_getAbsoluteUrl($this->getAbsoluteUrl());
    }
    
    public function getOriginal()
    {
        Centurion_Db_Table_Abstract::setFiltersStatus(false);
        $row = null;
        $columnName = 'original';
        $referenceMap = $this->getTable()->info('referenceMap');
        if (isset($referenceMap[$columnName])) {
            $column = $referenceMap[$columnName]['columns'];
            $className = $referenceMap[$columnName]['refTableClass'];
            $row = $this->findParentRow($referenceMap[$columnName]['refTableClass'],
                                           $columnName);
        }
        Centurion_Db_Table_Abstract::restoreFiltersStatus();

        return $row;
    }

    public function _getRawData($col)
    {
        $spec = $this->getTable()->getTranslationSpec();
        if ($this->_data[Translation_Traits_Model_DbTable::ORIGINAL_FIELD]
            && in_array($col, $spec[Translation_Traits_Model_DbTable::SET_NULL_FIELDS])) {

            Centurion_Db_Table_Abstract::setFiltersStatus(false);
            $originalRow = $this->getTable()->get(array('id' => $this->_data[Translation_Traits_Model_DbTable::ORIGINAL_FIELD]));
            Centurion_Db_Table_Abstract::setFiltersStatus(true);

            return $originalRow->{$col};
        }

        if (!array_key_exists($this->_prefix.$col, $this->_data)
            || !in_array($col, $spec[Translation_Traits_Model_DbTable::TRANSLATED_FIELDS]))
            return $this->_data[$col];

        if (Centurion_Config_Manager::get(Translation_Traits_Common::GET_DEFAULT_CONFIG_KEY,
                                          Translation_Traits_Common::NOT_EXISTS_GET_DEFAULT)) {
            if (null == $this->_data[$this->_prefix.$col])
                return $this->_data[$col];
        }
        if (isset($this->_data[$this->_prefix.$col])) {
            return $this->_data[$this->_prefix.$col];
        } else {
            return $this->_data[$col];
        }
    }

    public function preSave()
    {
        $behavior = Centurion_Signal::BEHAVIOR_CONTINUE;
        $spec = $this->getTable()->getTranslationSpec();

        if ($this->{Translation_Traits_Model_DbTable::ORIGINAL_FIELD}) {

            $parent = $this->getTable()->find($this->{Translation_Traits_Model_DbTable::ORIGINAL_FIELD})->current();

            $this->_processSpecialFields($spec[Translation_Traits_Model_DbTable::DUPLICATED_FIELDS], $parent);

            $this->_processSpecialFields($spec[Translation_Traits_Model_DbTable::SET_NULL_FIELDS]);

            $behavior = Centurion_Signal::BEHAVIOR_STOP_PROPAGATION;

        }
        else {
            if (!$this->{Translation_Traits_Model_DbTable::LANGUAGE_FIELD})
                $this->{Translation_Traits_Model_DbTable::LANGUAGE_FIELD} = Translation_Traits_Common::getDefaultLanguage()->pk;

            $translations = $this->getTable()->filter(array(Translation_Traits_Model_DbTable::ORIGINAL_FIELD => $this->pk));

            foreach ($translations as $translation) {
                $this->_processSpecialFields($spec[Translation_Traits_Model_DbTable::DUPLICATED_FIELDS], $this, $translation);
                //@todo do we reset translated field if original data changes ?
                //$this->_processSpecialFields($spec[Translation_Traits_Model_DbTable::TRANSLATED_FIELDS], $this, $translation);
            }
        }

        return $behavior;
    }

    public function getMissingTranslation()
    {
        $row = $this;

        if (null !== $this->original_id) {
            $row = $this->original;
        }

        $name = $this->_row->getTable()->info(Centurion_Db_Table_Abstract::NAME);

        $select = Centurion_Db::getSingleton('translation/language')->select(true)->setIntegrityCheck(false);
        $select->joinLeft($name, $name . '.`language_id` = `translation_language`.`id` and (' . $name . '.`id` = ' . $row->id . ' or ' . $name . '.`original_id` = ' . $row->id . ')');
        $select->where($name . '.`id` is null');

        $languages = $select->fetchAll();

        $str = array();
        foreach ($languages as $language) {
            $str[] = '<img src="' . $language->flag . '" />';
        }

        return implode($str, ' ');
    }

    protected function _processSpecialFields($fieldList, $reference = null, $target = null)
    {
        if (null === $target)
            $target = $this;

        foreach ($fieldList as $field) {
            if (null !== $reference)
                $target->$field = $reference->{$field};
            else
                $target->$field = null;
        }
    }
}