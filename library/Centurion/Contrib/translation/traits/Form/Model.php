<?php
/**
 * @class Translation_Traits_Form_Model
 * Trait to support translation in admin form
 *
 * @package Centurion
 * @subpackage Transaltion
 * @author Mathias Desloges, Laurent Chenay, Richard DELOGE, rd@octaveoctave.com
 * @copyright Octave & Octave
 */
class Translation_Traits_Form_Model
        extends Centurion_Traits_Form_Model_Abstract{

    /**
     * To select the behavior of the trait translation before display a translatable form :
     *      - for non-translatable fields, display feilds value as text (by a Centurion_FormElement_Info element)
     *      - or not display it
     * @var bool
     */
    protected $_displayModeOriginalAsText = false;
    
    const BUTTON_TRANSLATION = "button_lang";

    public function init()
    {
        Centurion_Signal::factory('post_generate')->connect(
            array($this, 'postGenerate'),
            $this->_form
        );

        Centurion_Signal::factory('on_populate_with_instance')->connect(
            array($this, 'populateWithInstance'),
            $this->_form
        );

        Centurion_Signal::factory('pre_populate')->connect(
            array($this, 'prePopulate'),
            $this->_form
        );

        Centurion_Signal::factory('post_form_pre_validate')->connect(
            array($this, 'prePopulate'),
            $this->_form
        );

        Centurion_Signal::factory('on_form_get_toolbar')->connect(
            array($this, 'onFormGetToolbar'),
            $this->_form
        );

        Centurion_Signal::factory('on_form_pre_save_reference_subform')->connect(
            array($this, '_preSaveReferenceSubForms'),
            $this->_form
        );
    }

    /**
     * Add a Centurion_Form_Element_Info in a the display group "Language group"
     * to manage the translation
     *
     * @return void
     * @todo: create the right signal
     */
    public function onFormGetToolbar()
    {
        $languageGroup = $this->_form->getDisplayGroup('language_group');
        if (!($languageGroup instanceof Zend_Form_DisplayGroup)) {
            return;
        }

        $langues = Centurion_Db::getSingleton('translation/language')->fetchAll();

        $tb = '<br />';
        $noDisplayManagementLanguages = false;
        foreach ($langues as $lang) {

            if (null === $this->_instance) {
                $id = $this->_form->getValue('original_id');
            } elseif (null === $this->_instance->original_id) {
                $id = $this->_instance->id;
            } else {
                $id = $this->_instance->original_id;
            }

            if(empty($id)){
                //To not add an empty line because user can not translate the sheet before save the original
                $noDisplayManagementLanguages = true;
                break;
            }
            if (null !== $this->getView()) {
                if ($lang->id == Translation_Traits_Common::getDefaultLanguage()->id) {
                    $url = $this->getView()->url(array('action' => 'get', 'id' => $id ,'lang' => null, 'from' => null));
                } else {
                    $url = $this->getView()->url(array('action' => 'translate', 'id' => null,'lang' => $lang->id, 'from' => $id));
                }
            } else {
                $url = '';
            }

            $tb .= sprintf('<a href="%s"><img src="%s" alt="Edit in %s" title="Edit in %s" /></a>&nbsp;',
                                $url,
                                $lang->flag,
                                ucfirst($lang->locale),
                                ucfirst($lang->locale));
        }

        if(false === $noDisplayManagementLanguages){
            //To not add an empty line because user can not translate the sheet before save the original
            $manageTranslationElement = $this->_generateInfoField('manage_translation',
                                                        $this->_translate('Manage translation'),
                                                        $tb,
                                                        false);


            //Add the button to the group
            $languageGroup->addElement($manageTranslationElement);
            $this->_form->cleanElement($manageTranslationElement);
        }
    }

    public function postGenerate()
    {
        $f = new Zend_Form_Element_Hidden(array('disableTranslator' => true, 'name' => Translation_Traits_Model_DbTable::ORIGINAL_FIELD));
        $f->setAttrib('hidden', true);
        $this->addElement($f, Translation_Traits_Model_DbTable::ORIGINAL_FIELD);

        $this->addElement($this->_generateInfoField(Translation_Traits_Model_DbTable::ORIGINAL_FIELD,
                                                    $this->_translate('Translated from'),
                                                    ''));

        $this->addElement($this->_generateInfoField(Translation_Traits_Model_DbTable::LANGUAGE_FIELD,
                                                    $this->_translate('Language'),
                                                    $this->_addFlag(Translation_Traits_Common::getDefaultLanguage()), false));

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
                                                    'label' => $this->_translate('Select a language: ')));

            $this->addElement($f, Translation_Traits_Model_DbTable::LANGUAGE_FIELD);
        }
    }

    public function prePopulate($signal, $sender, $values=array())
    {
        $original_id = null;
        if (isset($values[Translation_Traits_Model_DbTable::ORIGINAL_FIELD])){
            $original_id = $values[Translation_Traits_Model_DbTable::ORIGINAL_FIELD];
        }
        elseif($this->hasInstance() && null !== $this->getInstance()->{Translation_Traits_Model_DbTable::ORIGINAL_FIELD}){
            $original_id !== $this->getInstance()->{Translation_Traits_Model_DbTable::ORIGINAL_FIELD};
        }

        //@todo explain this condition and lint the function
        if (null !== $original_id) {
            try {
                //If the developper want to load an another row
                if(!empty($values['translation_id'])){
                    $original_id = $values['translation_id'];
                }

                $value = $this->getModel()->get(array('id' => $original_id));
            } catch (Centurion_Db_Table_Row_Exception $e) {
                return;
            }
            $spec = $this->_form->getModel()->getTranslationSpec();
            if (!$this->hasInstance()) {
                foreach ($spec[Translation_Traits_Model_DbTable::TRANSLATED_FIELDS] as $field) {
                    $element = $this->getElement($field);
                    if (null !== $element){
                        //Element Select accept only array
                        //and array_map('strval', (array) $value)
                        //is not compatible with a collection
                        if(!($element instanceof Zend_Form_Element_Select)
                            || !($value->{$field} instanceof Centurion_Db_Table_Rowset))
                            $element->setValue($value->{$field});
                        else{ 
                            //So, we transform the value to an array
                            $resArray = array();
                            foreach($value->{$field} as $row){
                                $resArray[] = $row->pk;
                            }
                            $element->setValue($resArray);
                        }
                    }
                }
            }

            if(null != $this->_form->getElement('_translating')){
                //To populate translated subform (only in translation mode)
                $parentReferenceMap = $this->getModel()->getReferenceMap();
                //Get the current instance of the form (if there are an instance, else return null)
                $_currentInstance = $this->_form->getInstance();

                foreach ($this->getSubForms() as $formName => $form) {
                    if (array_key_exists($form->getName(), $parentReferenceMap)){
                        //If the referenced subform is defined in this form
                        $refMap = $parentReferenceMap[$form->getName()];

                        if(in_array($refMap['columns'], $spec[Translation_Traits_Model_DbTable::TRANSLATED_FIELDS])){
                            //If the subform can be translated
                            if(!$this->_form->hasInstance()){
                                //If the form was not instance (new translation)
                                $form->setInstance($value->{$form->getName()});
                            }
                            elseif(empty($_currentInstance->{$refMap['columns']})){
                                //For existant translation, set the original subform if it is not overwrited
                                $_currentInstance->{$refMap['columns']} = $value->{$refMap['columns']};
                            }
                        }
                    }
                }

                //TO allows dev to custom action
                Centurion_Signal::factory('on_pre_populate_translated_form')->send($this->_form, array($value));
            }

            $f = $this->_getInfoField(Translation_Traits_Model_DbTable::ORIGINAL_FIELD);
            $f->setValue((string) $value);

            $value = Centurion_Db::getSingleton('translation/language')->get(array('id' => $values[Translation_Traits_Model_DbTable::LANGUAGE_FIELD]));
            $f = $this->_getInfoField(Translation_Traits_Model_DbTable::LANGUAGE_FIELD);
            $f->setValue($this->_addFlag($value) );

            $fields = array_merge($spec[Translation_Traits_Model_DbTable::DUPLICATED_FIELDS], $spec[Translation_Traits_Model_DbTable::SET_NULL_FIELDS]);

            $_modeOriginalAsText = $this->displayModeOriginalAsText();

            foreach($fields as $field) {
                if(!$_modeOriginalAsText){
                    $this->removeElement($field);
                }
                else{
                    $this->transformAsTextField($field);
                }

                $this->addExclude($field);
            }
        }
    }

    /**
     * Method to transform non-translatable field to a text to display
     * @param string $fieldName
     * @return bool
     */
    public function transformAsTextField($fieldName){
        $infoElement = null;

        //Display infos only if we are in a customization (not a translation)
        $_element = $this->getElement($fieldName);

        if($_element instanceof Centurion_Form_Element_Info){
            return false;
        }

        $type = null;
        if(!$_element){
            $_element = $this->getSubForm($fieldName);
            if($_element){
                $type = get_class($_element);
            }
            else{
                return false;
            }
        }
        else{
            $type = $_element->getType();
        }

        switch($type){
            case 'Media_Form_Element_MultiFile':
            case 'Media_Form_Model_Admin_File':
                $this->removeSubForm($fieldName);
                break;

            case 'Zend_Form_Element_Text':
            case 'Zend_Form_Element_Textarea':
                $infoElement = new Centurion_Form_Element_Info(
                    $fieldName
                    ,array(
                        'label'  => $_element->getLabel(),
                        'escape' => false
                    )
                );

                break;
            case 'Zend_Form_Element_Select':
            case 'Zend_Form_Element_Multiselect':
                $multiOptions = $this->_form->getElement($fieldName)->getMultiOptions();
                $infoElement = new Centurion_Form_Element_MultiInfo(
                    $fieldName,
                    array(
                        'label'         => $_element->getLabel(),
                        'escape'        => false,
                        'multiOptions'  => $multiOptions
                    )
                );

                break;
            case 'Zend_Form_Element_Checkbox':
            case 'Centurion_Form_Element_OnOff':
                $infoElement = new Centurion_Form_Element_OnOffInfo(
                    $fieldName,
                    array(
                        'label'  => $_element->getLabel(),
                        'escape' => false,
                        'no_transform' => 1
                    )
                );

                break;
        }

        $this->removeElement($fieldName);
        $this->removeSubForm($fieldName);
        if(null !== $infoElement){
            $this->_form->addElement($infoElement, $fieldName);
        }
    }

    public function populateWithInstance(){
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
                if ($el = $this->_getInfoField(Translation_Traits_Model_DbTable::ORIGINAL_FIELD))
                    $el->setValue('-');
            }
        }
    }

    protected function _getInfoField($baseName)
    {
        return $this->getElement(sprintf('info_%s', $baseName));
    }

    protected function _addFlag($language){
        if(!$language instanceof Translation_Model_DbTable_Row_Language){
            return (string) $language;
        }

        return sprintf( '<img src="%s" alt="Edit in %s" title="Edit in %s" />&nbsp;%s',
                        $language->flag,
                        ucfirst($language->locale),
                        ucfirst($language->locale),
                        (string) $language->name
                );
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

    public function displayModeOriginalAsText()
    {
        return $this->_form->delegateGet($this, '_displayModeOriginalAsText');
    }

    /**
     * Method to clear all reference to a translatable subform when it is updated or added
     * to prevent deletion of this subform in other versions
     * @param $signal
     * @param $sender
     * @param $form
     * @param $values
     */
    public function _preSaveReferenceSubForms($signal, $sender, $form, $values){
        $parentReferenceMap = $this->getModel()->getReferenceMap();
        $spec = $this->_form->getModel()->getTranslationSpec();

        foreach ($this->getReferenceSubForms() as $form) {
            if (false == $form->getValues()) {
                continue;
            }

            $referenceMap = $parentReferenceMap[$form->getName()];

            if(in_array($referenceMap['columns'], $spec[Translation_Traits_Model_DbTable::TRANSLATED_FIELDS])){
                $form->setInstance(null);
            }
        }
    }
}
