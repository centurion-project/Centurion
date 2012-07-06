<?php
/**
 * Author : Richard Déloge, rd@octaveoctave.com
 *
 * Test for the class Translation_Traits_Common
 */
require_once dirname(__FILE__) . '/../../../../../../../tests/TestHelper.php';

/**
 * @class Translation_Test_Traits_Form_ModelGenerateTest
 * @package Tests
 * @subpackage Translation
 * @author Richard Déloge, rd@octaveoctave.com
 *
 */
class Translation_Test_Traits_Form_ModelGenerateTest
        extends Translation_Test_Traits_Common_Abstract{

    public function setUp()
    {
        parent::setUp();
        $this->_switchLocale('en');
    }
    /**
     * To initialize the DB of test with a db whom contains only two languages FR and EN
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet(){
        return $this->createXMLDataSet(
            dirname(__FILE__) . '/_dataSet/FormTest.xml'
        );
    }

    /**
     * Method to build a specific form to test it in the followed methods.
     * The first argument take the class name of the form to build
     * The second accept the id of the instance to load into the form
     * If the second id is empty, the form is loaded without instance.
     * The third argument allow developper to load and edit a translated row
     *
     * @param string $form Form's class name
     * @param int $instance : To load the form with a specific instance
     * @param array $options : to set some options
     * @return Centurion_Form_Model_Abstract
     */
    protected function _getForm($form, $instance=null, $options=array()){
        if (!class_exists($form)){
            throw new Exception('Error, the form "'.$form.'" does not exist');
        }

        //Build the form
        $_form = new $form(array_merge(array('method' => Centurion_Form::METHOD_POST), $options));
        $_form->cleanForm();

        if (null !== $instance) {
            //Load the required instance
            Centurion_Db_Table_Abstract::setFiltersStatus(false);
            $object = $_form->getModel()->find($instance)->current();
            Centurion_Db_Table_Abstract::restoreFiltersStatus();

            if (!$_form->hasInstance()) {
                $_form->setInstance($object);
            }
        }

        return $_form;
    }

    /**
     * Method to check if the trait translation for form build buttons to switch of language (a <a> with image).
     * It must build one button by language into the info element "info_manage_translation" and add it into the
     * display group "language_group".
     * This method do nothing ig the group "language_group" does not exist
     */
    public function testMethodOnFormGetToolbar(){
        //First check if language group does not exist
        $form = $this->_getForm('Translation_Test_Traits_Form_Model_FirstModel', 1);
        $form->getToolbar();

        $elements = $form->getElements();
        $this->assertArrayNotHasKey(
                'info_manage_translation',
                $elements,
                'Error, the display group "language_group does not exist for this form, '
                        .'the method onFormGetToolbar must do nothing'
            );

        //Recreate a form, but, we add the group "language_group", the trait must build the button
        $form = $this->_getForm('Translation_Test_Traits_Form_Model_FirstModel', 1);
        $form->addDisplayGroup(array('info_'.Translation_Traits_Model_DbTable::LANGUAGE_FIELD), 'language_group');
        $form->getToolbar();
        $language_group = $form->getDisplayGroup('language_group');
        $elements = $language_group->getElements(); //The button is only in the display group

        $this->assertArrayHasKey(
                'info_manage_translation',
                $elements,
                'Error, the display group "language_group exist for this form, '
                    .'the method onFormGetToolbar must create list of button to switch of language'
            );

        $this->assertArrayNotHasKey(
                'info_manage_translation',
                $form->getElements(),
                'Error, buttons in info_manage_translation must appear only in the goup language_group'
            );

        //Check if all buttons are here
        $button = $language_group->getElement('info_manage_translation');

        $this->assertFalse(
                $button->getAttrib('escape'),
                'Error, the element must not escape the value (because it is a html value printed directly into the form'
            );


        $languageRowset = Centurion_Db::getSingleton('translation/language')->fetchAll();
        $this->assertEquals(
                substr_count($button->getValue(), '</a>'),
                $languageRowset->count(),
                'Error, there must be as many languages ​​as button to switch to another language'
            );

        //Check if each langage as its button
        foreach($languageRowset as $languageRow){
            $this->assertNotEmpty(
                    strpos($button->getValue(), $languageRow->flag),
                    'Error, the button '.$languageRow->locale.' is not defined'
                );
        }
    }

    /**
     * common tests for testBehaviorOfMethodPreGenerateWithOriginalForcedDfaultLanguageAtTrue() and
     *          testBehaviorOfMethodPreGenerateWithOriginalFOrcedDfaultLanguageAtFalse()
     *
     * (only the field "language_id" must change)
     *
     * @param Centurion_Form_Model_Abstract $form
     */
    protected function _commonTestsForMethodPreGenerated($form){
        //Check if field original_id was added
        $elementOriginal = $form->getElement(Translation_Traits_Model_DbTable::ORIGINAL_FIELD);
        $this->assertNotNull(
                $elementOriginal,
                'Error, the trait translation must add the field "original_id" on the form'
            );

        $this->assertInstanceOf(
                'Zend_Form_Element_Hidden',
                $elementOriginal,
                'Error, the field "original_id" must be hidden'
            );

        //Check if the field info_original_id was added (used to display the original of the version,
        //it must be empty here)
        $elementInfoOriginal = $form->getElement('info_'.Translation_Traits_Model_DbTable::ORIGINAL_FIELD);
        $this->assertNotNull(
                $elementInfoOriginal,
                'Error, the trait translation must add the field "info_original_id" on the form '
                    .'to display the origin of the displayed version'
            );

        $this->assertInstanceOf(
                'Centurion_Form_Element_Info',
                $elementInfoOriginal,
                'Error, the field "info_original_id" must be an info to display the title of the original row'
            );

        //Check if the field info_language_id was added (used to display the original of the version,
        //it must be empty here)
        $elementInfoLanguage = $form->getElement('info_'.Translation_Traits_Model_DbTable::LANGUAGE_FIELD);
        $this->assertNotNull(
                $elementInfoLanguage,
                'Error, the trait translation must add the field "info_language_id" on the form '
                    .'to display the origin of the displayed version'
            );

        $this->assertInstanceOf(
                'Centurion_Form_Element_Info',
                $elementInfoLanguage,
                'Error, the field "info_language_id" must be an info to display the lang of the current row'
            );
    }

    /**
     * Test the method "preGenerate" of the form
     */
    public function testBehaviorOfMethodPreGenerateWithOriginalForcedDfaultLanguageAtTrue(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        $form = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel');
        $this->_commonTestsForMethodPreGenerated($form);

        //Check if field language_id was added
        $elementLanguage = $form->getElement(Translation_Traits_Model_DbTable::LANGUAGE_FIELD);
        $this->assertNotNull(
                $elementLanguage,
                'Error, the trait translation must add the field "language_id" on the form'
            );

        $this->assertInstanceOf(
                'Zend_Form_Element_Hidden',
                $elementLanguage,
                'Error, the field "language_id" must be hidden'
            );

        $this->assertEquals(
                Translation_Traits_Common::getDefaultLanguage()->id,
                $elementLanguage->getValue(),
                'Error, the field "language_id" must be initialized with the current language value'
            );
    }

    /**
     * Test the method "preGenerate" of the form when the model is originalForcedDefaultLanguage at false
     */
    public function testBehaviorOfMethodPreGenerateWithOriginalForcedDfaultLanguageAtFalse(){
        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        $table->setOriginalForcedDefaultLanguage(false);
        $form = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel', null, array('model' => $table));
        $this->_commonTestsForMethodPreGenerated($form);

        //Check if field language_id was added
        $elementLanguage = $form->getElement(Translation_Traits_Model_DbTable::LANGUAGE_FIELD);
        $this->assertNotNull(
                $elementLanguage,
                'Error, the trait translation must add the field "language_id" on the form'
            );

        $this->assertInstanceOf(
                'Zend_Form_Element_Select',
                $elementLanguage,
                'Error, the field "language_id" must be select'
            );

        $this->assertEquals(
                Translation_Traits_Common::getDefaultLanguage()->id,
                $elementLanguage->getValue(),
                'Error, the field "language_id" must be initialized with the current language value'
            );

        //Check if the content of the element is valid
        $languagesRowset = Centurion_Db::getSingleton('translation/language')->fetchAll();
        $this->assertEquals(
                $languagesRowset->count(),
                count($elementLanguage->getMultiOptions()),
                'Error, all languages defined in the application must be in the select "language"id"'
            );

        $exceptedLanguagesList = array();
        foreach($languagesRowset as $language){
            $exceptedLanguagesList[] = $language->id;
        }

        sort($exceptedLanguagesList);
        $languageList = array_keys($elementLanguage->getMultiOptions());
        sort($languageList);

        $this->assertEquals(
                $exceptedLanguagesList,
                $languageList,
                'Error, the select list of language must contains only language defined in the application'
            );
    }

    /**
     * Test the behavior of the form with the mode defined by displayModeOriginalAsText()
     */
    public function testFormDisplayModeOriginalAsText(){
        $thirdForm = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel');

        $this->assertFalse(
            $thirdForm->displayModeOriginalAsText(),
            'Error, the default mode of if originalAsText is false'
        );

        $thirdForm->setDisplayModeOriginalAsText(true);

        $this->assertTrue(
            $thirdForm->displayModeOriginalAsText(),
            'Error, the default mode of if originalAsText must be now is true'
        );
    }

    /**
     * Common tests for the method prePopulate of the trait translation for form when the form use a new original row
     * @param Centurion_Form_Model_Abstract $form to test
     * @param array $dataSet if we must repopulate the form during these test
     * @param boolean $hasInstance if the form has an instance or not (to run additional tests)
     */
    protected function _commonTestForPrepropulateAnOriginalRow($form, $dataSet=array(), $hasInstance=false, $originalAsTest=false){
        if(!empty($dataSet)){
            //Execute only the method of the trait prePopulate (Normally, the trait must do nothing)
            $form->prePopulate(null, null, $dataSet);
        }

        //Check if the list of element is valid (no deleted fields)
        $elementsList = $form->getElements();
        $elementsNameList = array_keys($elementsList);
        $exceptedElementNameList = array(
            'info_'.Translation_Traits_Model_DbTable::LANGUAGE_FIELD,
            'info_'.Translation_Traits_Model_DbTable::ORIGINAL_FIELD,
            'language_id',
            'title',
            'content',
            'first_id',
            'is_active'
        );

        if(!$hasInstance){
            $exceptedElementNameList[] = 'original_id';
        }

        sort($elementsNameList);
        sort($exceptedElementNameList);

        $this->assertEquals(
            $exceptedElementNameList,
            $elementsNameList,
            'Error, the list of element in this form after prePopulate trait is not valid'
        );

        if($originalAsTest){
            $this->assertInstanceOf(
                'Zend_Form_Element_Text',
                $form->getElement('title'),
               'Error, to edit an original row, the mode OriginalAsTextx must do nothing'
            );

            $this->assertInstanceOf(
                'Zend_Form_Element_Textarea',
                $form->getElement('content'),
                'Error, to edit an original row, the mode OriginalAsTextx must do nothing'
            );


            $this->assertInstanceOf(
                'Zend_Form_Element_Select',
                $form->getElement('first_id'),
                'Error, to edit an original row, the mode OriginalAsTextx must do nothing'
            );
        }

        if($hasInstance){
            $this->assertNotNull(
                $form->getInstance(),
                'Error, the form must has an instance'
            );

            $_instance = $form->getInstance();
            //Check if the method was not overwritten by the trait (prePopulate alter nothing)
            foreach($form->getElements() as $name=>$element){
                if(substr($name, 0, 5) != 'info_' && isset($form->{$name})){
                    $this->assertEquals(
                        $_instance->{$name},
                        $element->getValue(),
                        'Error, the trait update the value of the field "'.$name.'" in auto-populating from instance'
                    );
                }
            }
        }
        else{
            //Check if the method was not overwritten by the trait
            $this->assertEquals(
                '',
                $form->getElement(Translation_Traits_Model_DbTable::ORIGINAL_FIELD)->getValue(),
                'Error, the field Original_id must be null for an original row'
            );

            $this->assertEquals(
                Translation_Traits_Common::getDefaultLanguage()->id,
                $form->getElement(Translation_Traits_Model_DbTable::LANGUAGE_FIELD)->getValue(),
                'Error, the field "language_id" must be initialized with the current language value'
            );

        }
    }

    /**
     * Check the behavior of the method prePopulate for new original row (called when the submission of the form failed)
     */
    public function testBehaviorOfMethodPrePopulateForANewOriginalRow(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        $thirdForm = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel');
        $thirdForm->setDisplayModeOriginalAsText(false);

        $this->_commonTestForPrepropulateAnOriginalRow(
            $thirdForm,
            array(
                'title'     => 'test title prepopulateform',
                'content'   => 'test content prepopulateform',
                'first_id'  => 1,
                'is_active' => 1
            ),
            false
        );
    }

    /***
     * Check if the trait translation does not alter the behavior of the Centurion Form Model for original row
     */
    public function testBehaviorOfMethodPrePopulateToEditAnOriginalRow(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        $thirdForm = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel', 1);
        $thirdForm->setDisplayModeOriginalAsText(false);

        $this->_commonTestForPrepropulateAnOriginalRow(
            $thirdForm,
            array(),
            true
        );
    }

    /**
     * Check if the trait translation not alter the standard behavior of Centurion Form Model for an original row
     * and when we recall populate after setInstance
     */
    public function testBehaviorOfMethodPrePopulateToEditAnOriginalRowIfWeRePopulate(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        //Here check only the result of prePopulate called by setInstance of the form (by passing in populate() method)
        $thirdForm = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel', 1);
        $thirdForm->setDisplayModeOriginalAsText(false);

        $this->_commonTestForPrepropulateAnOriginalRow(
            $thirdForm,
            array(
                'title'     => 'test title prepopulateform',
                'content'   => 'test content prepopulateform',
                'first_id'  => 1,
                'is_active' => 1
            ),
            true
        );
    }

    /**
     * Common tests for the method prePopulate of the trait translation for form when the form use an localized row
     * @param Centurion_Form_Model_Abstract $form to test
     * @param array $dataSet if we must repopulate the form during these test
     * @param boolean $hasInstance if the form has an instance or not (to run additional tests)
     * @param boolean $originalAsTest to know if the form is in mode OriginalAsText and if we must test this features
     */
    protected function _commonTestForPrepropulateALocalizedRow($form, $dataSet=array(), $hasInstance=false, $originalAsTest=false){
        if(!empty($dataSet)){
            //Execute only the method of the trait prePopulate (Normally, the trait must do nothing)
            $form->populate($dataSet);
        }

        //Check if the list of element is valid (no deleted fields)
        $elementsList = $form->getElements();
        $elementsNameList = array_keys($elementsList);
        $exceptedElementNameList = array();

        if($originalAsTest){
            $exceptedElementNameList = array(
                'info_'.Translation_Traits_Model_DbTable::LANGUAGE_FIELD,
                'info_'.Translation_Traits_Model_DbTable::ORIGINAL_FIELD,
                'title',
                'content',
                'first_id',
                'is_active'
            );
        }
        else{
            $exceptedElementNameList = array(
                'info_'.Translation_Traits_Model_DbTable::LANGUAGE_FIELD,
                'info_'.Translation_Traits_Model_DbTable::ORIGINAL_FIELD,
                'first_id', //it is the only translatable field
            );
        }

        if(!$hasInstance){
            $exceptedElementNameList[] = Translation_Traits_Model_DbTable::LANGUAGE_FIELD;
        }

        sort($elementsNameList);
        sort($exceptedElementNameList);

        $this->assertEquals(
            $exceptedElementNameList,
            $elementsNameList,
            'Error, the list of element in this form after prePopulate trait is not valid'
        );

        if($originalAsTest){
            $_originalFields = array('content', 'is_active', 'title');
            //In originalAsTest, all non translatable field must be Centurion_Form_Element_Info fields
            $this->assertContainsOnly(
                'Centurion_Form_Element_Info',
                array_intersect_key($elementsList, $_originalFields),
                false,
                'Error, non-translatable fields must be Centurion_Form_Element_Info fields'
            );

            //Check if all non-translatable fields are excluded
            $listExcludedFields = array_intersect(
                $form->getExcludes(),
                $_originalFields
            );

            asort($listExcludedFields);

            $this->assertEquals(
                array_values($_originalFields),
                array_values($listExcludedFields),
                'Error, all non-translatable fields are not excluded'
            );
        }

        if($hasInstance){
            $this->assertNotNull(
                $form->getInstance(),
                'Error, the form must has an instance'
            );

            $_instance = $form->getInstance();
            $_goodValue = array_merge(
                $_instance->toArray(),
                $dataSet
            );

            //Check if the method was not overwritten by the trait (prePopulate alter nothing)
            foreach($form->getElements() as $name=>$element){
                if(substr($name, 0, 5) != 'info_' && isset($form->{$name})){
                    if($element instanceof Centurion_Form_Element_OnOffInfo){
                        $_val = 'Off';
                        if(1 == $_goodValue[$name]){
                            $_val = 'On';
                        }

                        $this->assertEquals(
                            $_val,
                            $element->getValue(),
                            'Error, the trait update the value of the field "'.$name.'" in auto-populating from instance'
                        );
                    }
                    else{
                        $this->assertEquals(
                            $_goodValue[$name],
                            $element->getValue(),
                            'Error, the trait update the value of the field "'.$name.'" in auto-populating from instance'
                        );
                    }
                }
            }
        }
        else{
            if(!empty($dataSet[Translation_Traits_Model_DbTable::LANGUAGE_FIELD])){
                $this->assertEquals(
                    $dataSet[Translation_Traits_Model_DbTable::LANGUAGE_FIELD],
                    $form->getElement(Translation_Traits_Model_DbTable::LANGUAGE_FIELD)->getValue(),
                    'Error, the field "language_id" must has the value '
                                        .$dataSet[Translation_Traits_Model_DbTable::LANGUAGE_FIELD]
                );
            }
        }
    }

    /**
     * Check the behavior of the trait translation when the user create a new localized version of a row.
     * The trait must populate with original value all translatable field and remove all other fields
     */
    public function testBehaviorOfMethodPrePopulateToEditANewTranslation(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        //Here check only the result of prePopulate called by setInstance of the form (by passing in populate() method)
        $thirdForm = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel', 1);
        $thirdForm->setDisplayModeOriginalAsText(false);

        $_testSet = array(
            Translation_Traits_Model_DbTable::ORIGINAL_FIELD => 3,
            Translation_Traits_Model_DbTable::LANGUAGE_FIELD => 2
        );

        $this->_commonTestForPrepropulateALocalizedRow($thirdForm, $_testSet, false);
    }

    /**
     * Check the behavior of the trait translation when the user create a new localized version of a row,
     * but the language of the localized row is the same language of the original row, but the trait must keep the
     * same behavior for tow differents languages
     */
    public function testBehaviorOfMethodPrePopulateToEditANewTranslationInSameLanguage(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        //Here check only the result of prePopulate called by setInstance of the form (by passing in populate() method)
        $thirdForm = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel', 1);
        $thirdForm->setDisplayModeOriginalAsText(false);

        $_testSet = array(
            Translation_Traits_Model_DbTable::ORIGINAL_FIELD => 3,
            Translation_Traits_Model_DbTable::LANGUAGE_FIELD => 1
        );

        $this->_commonTestForPrepropulateALocalizedRow($thirdForm, $_testSet, false);
    }

    /**
     * Check the behavior of the method when we load a localized row into the form.
     * Only, it must display only translatable field and info field (not language_id because it is not translatable)
     */
    public function testBehaviorOfMethodPrePopulateToEditAnExistantTranslation(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        //Here check only the result of prePopulate called by setInstance of the form (by passing in populate() method)
        //(Normally, the trait must do nothing)
        $thirdForm = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel', 4);
        $thirdForm->setDisplayModeOriginalAsText(false);

        $this->_commonTestForPrepropulateALocalizedRow($thirdForm, array(), true);
    }

    /**
     * Check the behavior of the method when we load a localized row into the form.
     * Only, it must display only translatable field and info field (not language_id because it is not translatable).
     * Check if this behavior not change is we recall populate after
     */
    public function testBehaviorOfMethodPrePopulateToEditAnExistantTranslationIfWeRepopulate(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        //Here check only the result of prePopulate called by setInstance of the form (by passing in populate() method)
        //(Normally, the trait must do nothing)
        $thirdForm = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel', 4);
        $thirdForm->setDisplayModeOriginalAsText(false);

        $_testSet = array('first_id' => 3);
        $this->_commonTestForPrepropulateALocalizedRow($thirdForm, $_testSet, true);
    }

    /**
     * Check the behavior of the method prePopulate for new original row (called when the submission of the form failed)
     */
    public function testBehaviorOfMethodPrePopulateForANewOriginalRowInOriginalAsText(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        $thirdForm = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel');
        $thirdForm->setDisplayModeOriginalAsText(true);

        $this->_commonTestForPrepropulateAnOriginalRow(
            $thirdForm,
            array(
                'title'     => 'test title prepopulateform',
                'content'   => 'test content prepopulateform',
                'first_id'  => 1,
                'is_active' => 1
            ),
            false,
            true
        );
    }

    /***
     * Check if the trait translation does not alter the behavior of the Centurion Form Model for original row
     */
    public function testBehaviorOfMethodPrePopulateToEditAnOriginalRowInOriginalAsText(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        $thirdForm = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel', 1);
        $thirdForm->setDisplayModeOriginalAsText(true);

        $this->_commonTestForPrepropulateAnOriginalRow(
            $thirdForm,
            array(),
            true,
            true
        );
    }

    /**
     * Check if the trait translation not alter the standard behavior of Centurion Form Model for an original row
     * and when we recall populate after setInstance
     */
    public function testBehaviorOfMethodPrePopulateToEditAnOriginalRowIfWeRePopulateInOriginalAsText(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        //Here check only the result of prePopulate called by setInstance of the form (by passing in populate() method)
        $thirdForm = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel', 1);
        $thirdForm->setDisplayModeOriginalAsText(true);

        $this->_commonTestForPrepropulateAnOriginalRow(
            $thirdForm,
            array(
                'title'     => 'test title prepopulateform',
                'content'   => 'test content prepopulateform',
                'first_id'  => 1,
                'is_active' => 1
            ),
            true,
            true
        );
    }

    /**
     * Check the behavior of the trait translation when the user create a new localized version of a row.
     * The trait must populate with original value all translatable field and remove all other fields
     */
    public function testBehaviorOfMethodPrePopulateToEditANewTranslationInOriginalAsText(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        //Here check only the result of prePopulate called by setInstance of the form (by passing in populate() method)
        $thirdForm = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel', 1);
        $thirdForm->setDisplayModeOriginalAsText(true);

        $_testSet = array(
            Translation_Traits_Model_DbTable::ORIGINAL_FIELD => 3,
            Translation_Traits_Model_DbTable::LANGUAGE_FIELD => 1
        );

        $this->_commonTestForPrepropulateALocalizedRow($thirdForm, $_testSet, false, true);
    }

    /**
     * Check the behavior of the trait translation when the user create a new localized version of a row,
     * but the language of the localized row is the same language of the original row, but the trait must keep the
     * same behavior for tow differents languages
     */
    public function testBehaviorOfMethodPrePopulateToEditANewTranslationInSameLanguageInOriginalAsText(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        //Here check only the result of prePopulate called by setInstance of the form (by passing in populate() method)
        $thirdForm = $this->_getForm('Translation_Test_Traits_Form_Model_ThirdModel', 1);
        $thirdForm->setDisplayModeOriginalAsText(true);

        $_testSet = array(
            Translation_Traits_Model_DbTable::ORIGINAL_FIELD => 3,
            Translation_Traits_Model_DbTable::LANGUAGE_FIELD => 1
        );

        $this->_commonTestForPrepropulateALocalizedRow($thirdForm, $_testSet, false, true);
    }

    /**
     * Check the behavior of the method when we load a localized row into the form.
     * Only, it must display only translatable field and info field (not language_id because it is not translatable)
     */
    public function testBehaviorOfMethodPrePopulateToEditAnExistantTranslationInOriginalAsText(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        //Here check only the result of prePopulate called by setInstance of the form (by passing in populate() method)
        //(Normally, the trait must do nothing)
        $thirdForm = $this->_getForm(
            'Translation_Test_Traits_Form_Model_ThirdModel',
            4,
            array('displayModeOriginalAsText' => true)
        );

        $this->_commonTestForPrepropulateALocalizedRow($thirdForm, array(), true, true);
    }

    /**
     * Check the behavior of the method when we load a localized row into the form.
     * Only, it must display only translatable field and info field (not language_id because it is not translatable).
     * Check if this behavior not change is we recall populate after
     */
    public function testBehaviorOfMethodPrePopulateToEditAnExistantTranslationIfWeRepopulateInOriginalAsText(){
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $table->setOriginalForcedDefaultLanguage(true);
        //Here check only the result of prePopulate called by setInstance of the form (by passing in populate() method)
        //(Normally, the trait must do nothing)
        $thirdForm = $this->_getForm(
                'Translation_Test_Traits_Form_Model_ThirdModel',
                4,
                array('displayModeOriginalAsText' => true)
            );

        $_testSet = array('first_id' => 3);
        $this->_commonTestForPrepropulateALocalizedRow($thirdForm, $_testSet, true, true);
    }
}
