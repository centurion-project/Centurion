<?php
class Translation_Traits_Form_Model extends Centurion_Traits_Form_Model_Abstract
{

    protected $_isTranslation = false;

    public function init()
    {
        Centurion_Signal::factory('pre_generate')->connect(array($this, 'preGenerate'), $this->_form);
        Centurion_Signal::factory('on_populate_with_instance')->connect(array($this, 'populateWithInstance'), $this->_form);
        Centurion_Signal::factory('pre_populate')->connect(array($this, 'prePopulate'), $this->_form);
        Centurion_Signal::factory('on_form_get_toolbar')->connect(array($this, 'onFormGetToolbar'), $this->_form);

        $this->_form->enableElement('original_id');
        $this->_form->enableElement('language_id');
    }

    /**
     * Add a Centurion_Form_Element_Info in a the display group "Language group"
     * to manage the translation
     *
     * @return void
     * @todo: create the right signal
     * @todo: add the flag after "Translated from:" in the same display group
     */
    public function onFormGetToolbar()
    {
        $langues = Centurion_Db::getSingleton('translation/language')->fetchAll();

        $tb = '<br />';
        foreach ($langues as $lang) {

            if (null === $this->_instance) {
                $id = $this->_form->getValue('original_id');
            } elseif (null === $this->_instance->original_id) {
                $id = $this->_instance->id;
            } else {
                $id = $this->_instance->original_id;
            }

            if ($lang->id == Translation_Traits_Common::getDefaultLanguage()->id) {
                $url = $this->getView()->url(array('action' => 'translate', 'id' => $id ,'lang' => null, 'from' => null));
            } else {
                $url = $this->getView()->url(array('action' => 'translate', 'id' => null,'lang' => $lang->id, 'from' => $id));
            }

            $tb .= sprintf('<a href="%s"><img src="%s" alt="Edit in %s" title="Edit in %s" /></a>&nbsp;',
                                $url,
                                $lang->flag,
                                ucfirst($lang->locale),
                                ucfirst($lang->locale));
        }

        $manageTranslationElement = $this->_generateInfoField('manage_translation',
                                                    $this->getView()->translate('Manage translation'),
                                                    $tb,
                                                    false);

        $languageGroup = $this->_form->getDisplayGroup('language_group');
        if ($languageGroup) {
            $languageGroup->addElement($manageTranslationElement);
            $this->_form->cleanElement($manageTranslationElement);
        }
    }

    public function preGenerate()
    {
        $f = new Zend_Form_Element_Hidden(array('disableTranslator' => true, 'name' => Translation_Traits_Model_DbTable::ORIGINAL_FIELD));
        $f->setAttrib('hidden', true);
        $this->addElement($f, Translation_Traits_Model_DbTable::ORIGINAL_FIELD);

        $this->addElement($this->_generateInfoField(Translation_Traits_Model_DbTable::ORIGINAL_FIELD,
                                                    $this->getView()->translate('Translated from'),
                                                    ''));

        $this->addElement($this->_generateInfoField(Translation_Traits_Model_DbTable::LANGUAGE_FIELD,
                                                    $this->getView()->translate('Language'),
                                                    (string) Translation_Traits_Common::getDefaultLanguage()->name));


        if ($this->getModel()->isOriginalForcedToDefaultLanguage()) {

            $f = new Zend_Form_Element_Hidden(array('disableTranslator' => true, 'name' => Translation_Traits_Model_DbTable::LANGUAGE_FIELD,
                                                    'value' => Translation_Traits_Common::getDefaultLanguage()->id));
            $f->setAttrib('hidden', true);
            $this->addElement($f, Translation_Traits_Model_DbTable::LANGUAGE_FIELD);

        } else {
            $langues = Centurion_Db::getSingleton('translation/language')->fetchAll();
            foreach ($langues as $lang) {
                $languages[$lang->id] = $lang->name;
            }

            $f = new Zend_Form_Element_Select(array('disableTranslator' => true,
                                                    'name'              => Translation_Traits_Model_DbTable::LANGUAGE_FIELD,
                                                    'value'             => Translation_Traits_Common::getDefaultLanguage()->id,
                                                    'multiOptions'      => $languages,
                                                    'label' => $this->getView()->translate('Select a language: ')));

            $this->addElement($f, Translation_Traits_Model_DbTable::LANGUAGE_FIELD);
        }
    }

    public function prePopulate($signal, $sender, $values)
    {
        if (isset($values[Translation_Traits_Model_DbTable::ORIGINAL_FIELD]) || ($this->hasInstance() && null !== $this->getInstance()->{Translation_Traits_Model_DbTable::ORIGINAL_FIELD})) {
            try {
                $value = $this->getModel()->get(array('id' => $values[Translation_Traits_Model_DbTable::ORIGINAL_FIELD]));
            } catch (Centurion_Db_Table_Row_Exception $e) {
                return;
            }
            $spec = $this->_form->getModel()->getTranslationSpec();
            if (!$this->hasInstance()) {
                foreach ($spec[Translation_Traits_Model_DbTable::TRANSLATED_FIELDS] as $field) {
                    $element = $this->getElement($field);
                    if (null !== $element)
                        $element->setValue($value->{$field});
                }
            }

            $f = $this->_getInfoField(Translation_Traits_Model_DbTable::ORIGINAL_FIELD);
            $f->setValue((string) $value);

            $value = Centurion_Db::getSingleton('translation/language')->get(array('id' => $values[Translation_Traits_Model_DbTable::LANGUAGE_FIELD]));
            $f = $this->_getInfoField(Translation_Traits_Model_DbTable::LANGUAGE_FIELD);
            $f->setValue((string) $value->name);

            $fields = array_merge($spec[Translation_Traits_Model_DbTable::DUPLICATED_FIELDS], $spec[Translation_Traits_Model_DbTable::SET_NULL_FIELDS]);

            foreach($fields as $field) {
                $this->removeElement($field);
                $this->addExclude($field);
            }
        }
    }

    public function populateWithInstance()
    {
        if ($this->_model->isOriginalForcedToDefaultLanguage()) {
            $instance = $this->_form->getInstance();
            if ($instance->pk) {
                $this->removeElement(Translation_Traits_Model_DbTable::ORIGINAL_FIELD);
                if ($this->{Translation_Traits_Model_DbTable::ORIGINAL_FIELD}) {
                    $this->removeElement(Translation_Traits_Model_DbTable::LANGUAGE_FIELD);
                }
            }

            if (NULL !== $instance->{Translation_Traits_Model_DbTable::ORIGINAL_FIELD}) {
                $this->removeElement(Translation_Traits_Model_DbTable::LANGUAGE_FIELD);
            } else {
                $this->_getInfoField(Translation_Traits_Model_DbTable::ORIGINAL_FIELD)->setValue('-');
            }
        }
    }

    protected function _getInfoField($baseName)
    {
        return $this->getElement(sprintf('info_%s', $baseName));
    }

    protected function _generateInfoField($baseName, $label, $value, $escape = true)
    {
        $fInfo = new Centurion_Form_Element_Info(array(
                                                      'disableTranslator' => true,
                                                      'name'  => 'info_' . $baseName,
                                                      'label' => $label,
                                                      'value' => $value,
                                                      'escape' => $escape
                                                 ), 'info_' . $baseName);

        $fInfo->setAttrib('large', true);

        return $fInfo;
    }

}
