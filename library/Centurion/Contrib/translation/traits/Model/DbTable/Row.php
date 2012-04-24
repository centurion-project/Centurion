<?php
class Translation_Traits_Model_DbTable_Row extends Centurion_Traits_Model_DbTable_Row_Abstract
{
    protected $_localBeforeSave;
    protected $_prefix = null;

    public function init()
    {
        Centurion_Signal::factory('pre_save')->connect(
            array($this, 'preSave'),
            $this->_row,
            Centurion_Signal::BEHAVIOR_CAN_STOP
        );

        //To duplicate value of non-translatable field on all localized rows
        Centurion_Signal::factory('post_save')->connect(
            array($this, 'postSave'),
            $this->_row,
            Centurion_Signal::BEHAVIOR_STOP_PROPAGATION
        );

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
        if (!empty($this->_data[Translation_Traits_Model_DbTable::ORIGINAL_FIELD])
            && in_array($col, $spec[Translation_Traits_Model_DbTable::SET_NULL_FIELDS])) {

            //For all set null field, we return the original value
            $this->getOriginal()->{$col};
        }

        //Add to translated field, the original id and the language id to return value of the localized value
        //instaed of the original value
        $_translatableCols = array_merge(
            $spec[Translation_Traits_Model_DbTable::TRANSLATED_FIELDS],
            array(
                Translation_Traits_Model_DbTable::LANGUAGE_FIELD,
                Translation_Traits_Model_DbTable::ORIGINAL_FIELD
            )
        );

        if (!array_key_exists($this->_prefix.$col, $this->_data)
            || !in_array($col, $_translatableCols)){
            return $this->_data[$col];
        }

        //Return the localized value if exist, else return the original value
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
            if (!$this->{Translation_Traits_Model_DbTable::LANGUAGE_FIELD}){
                $this->{Translation_Traits_Model_DbTable::LANGUAGE_FIELD} = Translation_Traits_Common::getDefaultLanguage()->pk;
            }
        }

        return $behavior;
    }

    /**
     * Called to update duplicated field of localized rows when the original row is updated
     */
    public function postSave(){
        if (empty($this->{Translation_Traits_Model_DbTable::ORIGINAL_FIELD})) {
            //If it is an original row

            //Get all localized value
            Centurion_Db_Table::setFiltersStatus(false);
            $translations = $this->getTable()->filter(array(Translation_Traits_Model_DbTable::ORIGINAL_FIELD => $this->pk));
            Centurion_Db_Table::restoreFiltersStatus();

            //Update each loaclized velu
            $spec = $this->getTable()->getTranslationSpec();
            foreach ($translations as $translation) {
                $this->_processSpecialFields(
                    $spec[Translation_Traits_Model_DbTable::DUPLICATED_FIELDS],
                    $this,
                    $translation,
                    false
                );

                //@todo do we reset translated field if original data changes ?
                //$this->_processSpecialFields($spec[Translation_Traits_Model_DbTable::TRANSLATED_FIELDS], $this, $translation);

                $translation->save();
            }
        }
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