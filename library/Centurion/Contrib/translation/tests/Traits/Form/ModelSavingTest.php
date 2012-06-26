<?php
/**
 * Author : Richard Déloge, rd@octaveoctave.com
 *
 * Test for the class Translation_Traits_Common
 */
require_once dirname(__FILE__) . '/../../../../../../../tests/TestHelper.php';

/**
 * @class Translation_Test_Traits_Form_ModelSavingTest
 * @package Tests
 * @subpackage Translation
 * @author Richard Déloge, rd@octaveoctave.com
 *
 */
class Translation_Test_Traits_Form_ModelSavingTest
        extends Translation_Test_Traits_Common_Abstract{

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
        if(!class_exists($form)){
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
     * To check if the behavior of form is not altered with original row when the form implement the trait translation
     */
    public function testSavingNewOriginalRow(){
        $this->_switchLocale('fr');
        $form = $this->_getForm('Translatable_Form_Model_ThirdModel');

        //Value for a new instnace
        $_dataSet = array(
            'title'     => 'test saving original 1 title',
            'content'   => 'test saving original 1 content',
            'first_id'  => 1,
            'is_active' => 1
        );

        //Maje and save the instance
        $form->isValid($_dataSet);
        $instance = $form->save();

        $this->assertInstanceOf(
                'Translatable_Model_DbTable_Row_ThirdModel',
                $instance,
                'The default behavior of Centurion Form is broken when we must save a new original row'
            );

        //Check if values in the isntance are good
        Centurion_Db_Table::setFiltersStatus(false);
        $_resultRow = $form->getModel()->select(true)->filter(array('id' => $instance->id))->fetchRow();
        Centurion_Db_Table::restoreFiltersStatus();

        $this->assertNotNull(
                $_resultRow,
                'The default behavior of Centurion Form is broken when we must save a new original row (row not found)'
            );

        $_dataSet['original_id'] = null;
        $_dataSet['language_id'] = 1;
        $_dataSet['id'] = $instance->id;

        ksort($_dataSet);
        $_rawResultRow = $_resultRow->toArray();
        ksort($_rawResultRow);

        $this->assertEquals(
            $_dataSet,
            $_rawResultRow,
            'Error, data of the row is not the excepted data when we save a new original row'
        );
    }

    /**
     * To check if the behavior of form is not altered when we edit an original row
     * when the form implement the trait translation
     */
    public function testSavingExistantOriginalRow(){
        $this->_switchLocale('fr');
        //Load a form with an instance
        $form = $this->_getForm('Translatable_Form_Model_ThirdModel', 2);

        $_dataSet = array(
            'title'     => 'test saving original 2 title',
            'first_id'  => 3,
        );

        //Change some value (not use isValid because we reset not all fields in the form like a normal request)
        $form->isValidPartial($_dataSet);
        $instance = $form->save();

        $this->assertInstanceOf(
            'Translatable_Model_DbTable_Row_ThirdModel',
            $instance,
            'The default behavior of Centurion Form is broken when we must save an existant original row'
        );

        //Check if the result is good
        Centurion_Db_Table::setFiltersStatus(false);
        $_resultRow = $form->getModel()->select(true)->filter(array('id' => $instance->id))->fetchRow();
        Centurion_Db_Table::restoreFiltersStatus();

        $this->assertNotNull(
            $_resultRow,
            'The default behavior of Centurion Form is broken when we must save an existant original row (row not found)'
        );

        $_dataSet['original_id'] = null;
        $_dataSet['language_id'] = 1;
        $_dataSet['id'] = 2;
        $_dataSet['is_active'] = 0;
        $_dataSet['content'] = 'Content FR';

        ksort($_dataSet);
        $_rawResultRow = $_resultRow->toArray();
        ksort($_rawResultRow);

        $this->assertEquals(
            $_dataSet,
            $_rawResultRow,
            'Error, data of the row is not the excepted data when we save an existant original row'
        );
    }

    /**
     * Check if the behavior of translatable form is the excepted behavior when we must add a new localized row for an
     * original row
     */
    public function testSavingNewLocalizedRow(){
        $this->_switchLocale('fr');
        //Initialize the form like the CRUD for a new localized row
        $form = $this->_getForm('Translatable_Form_Model_ThirdModel');
        $form->populate(array(
            Translation_Traits_Model_DbTable::ORIGINAL_FIELD => 3,
            Translation_Traits_Model_DbTable::LANGUAGE_FIELD => 2
        ));

        //Update value of the form like an user (not use isValid because we not reset all fields like a normal request)
        $form->isValidPartial(array('title' => 'test saving localized row 1', 'first_id' => 3));
        $instance = $form->save();

        $this->assertInstanceOf(
            'Translatable_Model_DbTable_Row_ThirdModel',
            $instance,
            'Error, the saving failed and not return a row instance when we save a new localized row'
        );

        //Check if the result is good
        Centurion_Db_Table::setFiltersStatus(false);
        $_resultRow = $form->getModel()->select(true)->filter(array('id' => $instance->id))->fetchRow();
        Centurion_Db_Table::restoreFiltersStatus();
        $this->assertNotNull(
            $_resultRow,
            'Error, the new saved row was not found when we save a new localized row'
        );

        $_dataSet[Translation_Traits_Model_DbTable::ORIGINAL_FIELD] = 3;
        $_dataSet[Translation_Traits_Model_DbTable::LANGUAGE_FIELD] = 2;
        $_dataSet['id'] = $instance->id;
        $_dataSet['title'] = null; //It is a set null field
        $_dataSet['content'] = 'Content FR'; //it is a duplicated field
        $_dataSet['is_active'] = 0;
        $_dataSet['first_id'] = 3;

        ksort($_dataSet);
        $_rawResultRow = $_resultRow->toArray();
        ksort($_rawResultRow);

        $this->assertEquals(
            $_dataSet,
            $_rawResultRow,
            'Error, data of the row is not the excepted data when we save a new localized row'
        );
    }

    /**
     * Check if the behavior of translatable form is the excepted behavior when we must edit a localized row for an
     * original row
     */
    public function testSavingExistantLocalizedRow(){
        $this->_switchLocale('fr');
        //Initialize the form with an existant localized row like the CRUD for a new localized row
        $form = $this->_getForm('Translatable_Form_Model_ThirdModel', 4);

        $form->isValidPartial(array('title' => 'test saving localized row 1', 'first_id' => 3));
        $instance = $form->save();

        //Update value of the form like an user (not use isValid because we not reset all fields like a normal request)
        $this->assertInstanceOf(
            'Translatable_Model_DbTable_Row_ThirdModel',
            $instance,
            'Error, the saving failed and not return a row instance when we save an existant localized row'
        );

        Centurion_Db_Table::setFiltersStatus(false);
        $_resultRow = $form->getModel()->select(true)->filter(array('id' => $instance->id))->fetchRow();
        Centurion_Db_Table::restoreFiltersStatus();
        //Check if the result if the excepted result
        $this->assertNotNull(
            $_resultRow,
            'Error, the new saved row was not found when we save an existant localized row'
        );

        $_exceptedResult = array(
            Translation_Traits_Model_DbTable::ORIGINAL_FIELD => 1,
            Translation_Traits_Model_DbTable::LANGUAGE_FIELD => 2,
            'id' => 4,
            'title' => null, //It is a set null field
            'content' => 'Content FR', //it is a duplicated field
            'is_active' => 0,
            'first_id' => 3
        );

        ksort($_exceptedResult);
        $_rawResultRow = $_resultRow->toArray();
        ksort($_rawResultRow);

        $this->assertEquals(
            $_exceptedResult,
            $_rawResultRow,
            'Error, data of the row is not the excepted data when we save an existant localized row'
        );

    }

    /**
     * Check the behavior of the form if when we want save a new original row with some translatable subform,
     *  it is not changed by the developper
     */
    public function testSavingNewOriginalRowWithSubForm(){
        $this->_switchLocale('fr');
        $form = $this->_getForm('Translatable_Form_Model_FirstModel');
        Translatable_Model_DbTable_Row_FirstModel::cleanLocalReferenceCache();

        //Value for a new instnace
        $_dataSet = array(
            'title'     => 'test saving original 1 title',
            'content'   => 'test saving original 1 content',
            'is_active' => 1,
            'second1'   => array(
                                'title' => 'test saving second 1 title',
                                'content' => 'test saving second 1 content',
                                'is_active' => 0,
                            ),
            'second2'   => array(
                                'title' => 'test saving second 2 title',
                                'content' => 'test saving second 2 content',
                                'is_active' => 0,
                            )
        );

        //Update and save the instance
        $form->isValid($_dataSet);
        $instance = $form->save();

        $this->assertInstanceOf(
            'Translatable_Model_DbTable_Row_FirstModel',
            $instance,
            'Error, the saving failed and not return a row instance when we save a new original row'
        );

        //Check if values in the isntance are good
        Centurion_Db_Table::setFiltersStatus(false);
        $_resultRow = $form->getModel()->select(true)->filter(array('id' => $instance->id))->fetchRow();
        Centurion_Db_Table::restoreFiltersStatus();
        $this->assertNotNull(
            $_resultRow,
            'Error, the new saved row was not found when we save a new original row'
        );

        $_exceptedResult = array(
            'original_id'   => null,
            'language_id'   => 1,
            'second1_id'    => $instance->second1_id,
            'second2_id'    => $instance->second2_id,
            'id'            => $instance->id,
            'title'     => 'test saving original 1 title',
            'content'   => 'test saving original 1 content',
            'is_active' => 1,
        );

        ksort($_exceptedResult);
        $_rawResultRow = $_resultRow->toArray();
        ksort($_rawResultRow);

        //Check if new subform are goodly saved
        $this->assertEquals(
            $_exceptedResult,
            $_rawResultRow,
            'Error, data of the row is not the excepted data when we save a new original row'
        );

        $second1 = $_resultRow->second1;
        $this->assertInstanceOf(
                'Translatable_Model_DbTable_Row_SecondModel',
                $second1,
                'Error, the dependant row of the original row after the save is not good'
            );

        $this->assertSame(
                'test saving second 1 title',
                $second1->title,
                'Error, the dependant row after of the original row the save has not good values'
            );

        $second2 = $_resultRow->second2;
        $this->assertInstanceOf(
                'Translatable_Model_DbTable_Row_SecondModel',
                $second2,
                'Error, the non translatable dependant row after the save is not good'
            );

        $this->assertSame(
                'test saving second 2 title',
                $second2->title,
                'Error, the non translatabl dependant row after the save has not good values'
            );
    }

    /**
     * Check when we update an original row with translatable subform,
     * if this subform was updated, we save a new subinstance without delete the previous subinstance
     * to prevent deletion if this subinstance is used in some localized rows
     */
    public function testSavingExistantOriginalRowWithSubForm(){
        $this->_switchLocale('fr');
        $form = $this->_getForm('Translatable_Form_Model_FirstModel', 1);
        Translatable_Model_DbTable_Row_FirstModel::cleanLocalReferenceCache();

        //Value for a new instnace
        $_dataSet = array(
            'title'     => 'test saving original 2 title',
            'second1'   => array(
                'title' => 'test saving second 3 title',
            )
        );

        //Update and save the instance
        $form->isValidPartial($_dataSet);
        $instance = $form->save();

        $this->assertInstanceOf(
            'Translatable_Model_DbTable_Row_FirstModel',
            $instance,
            'Error, the saving failed and not return a row instance when we edit an original row'
        );

        //Check if values in the instance are good
        Centurion_Db_Table::setFiltersStatus(false);
        $_resultRow = $form->getModel()->select(true)->filter(array('id' => 1))->fetchRow();
        Centurion_Db_Table::restoreFiltersStatus();

        $this->assertNotNull(
            $_resultRow,
            'Error, the new saved row was not found when we edit an original row'
        );

        //Check if the result is the excepted result and if the new subinstance was created
        $_exceptedResult = array(
            'original_id'   => null,
            'language_id'   => 1,
            'second1_id'    => $instance->second1_id,
            'second2_id'    => 2, //The second instance is not translatable
            'id'            => 1,
            'title'     => 'test saving original 2 title',
            'content'   => 'content FR',
            'is_active' => 1,
        );

        ksort($_exceptedResult);
        $_rawResultRow = $_resultRow->toArray();
        ksort($_rawResultRow);

        //Check if the subinstance is valid
        $this->assertEquals(
            $_exceptedResult,
            $_rawResultRow,
            'Error, data of the row is not the excepted data when we edit a original row'
        );

        $second1 = $_resultRow->second1;
        $this->assertInstanceOf(
            'Translatable_Model_DbTable_Row_SecondModel',
            $second1,
            'Error, the dependant row after the save is not good'
        );

        $this->assertSame(
            'test saving second 3 title',
            $second1->title,
            'Error, the dependant row after the save has not good values'
        );

        $second2 = $_resultRow->second2;
        $this->assertInstanceOf(
            'Translatable_Model_DbTable_Row_SecondModel',
            $second2,
            'Error, the dependant row after the save is not good'
        );

        $this->assertSame(
            'Second 2 - FR',
            $second2->title,
            'Error, the dependant row after the save has not good values'
        );

        //Check is the previous subinstance already exist
        $_second1Row = Centurion_Db::getSingleton('translatable/second_model')
                            ->select(true)
                            ->filter(array('id' => 1))
                            ->fetchRow();

        $this->assertInstanceOf(
                'Translatable_Model_DbTable_Row_SecondModel',
                $_second1Row,
                'Error, the previous subinstance must was not updated'
            );

        $this->assertEquals(
                    'Second 1 - FR',
                    $_second1Row->title,
                    'Error, the previous subinstance must was not updated'
            );
    }

    /**
     * Check when we add a new localized row with translatable subform,
     * if this subform was updated, we save a new subinstance without delete the previous subinstance
     * to prevent deletion if this subinstance is used in original rows
     */
    public function testSavingNewLocalizedRowWithSubForm(){
        $this->_switchLocale('fr');
        //Initialize the form like the CRUD for a new localized row
        $form = $this->_getForm('Translatable_Form_Model_FirstModel');
        Translatable_Model_DbTable_Row_FirstModel::cleanLocalReferenceCache();

        $form->populate(array(
            Translation_Traits_Model_DbTable::ORIGINAL_FIELD => 3,
            Translation_Traits_Model_DbTable::LANGUAGE_FIELD => 2
        ));

        //Update value of the form like an user (not use isValid because we not reset all fields like a normal request)
        $form->isValidPartial(array(
            'title' => 'test saving localized row 1',
            'second1'   => array(
                'title' => 'test saving second 4 title',
                'content' => 'test saving second 4 content',
                'is_active' => 0,
            ),
            'second2'   => array(
                'title' => 'test saving second 5 title',
                'content' => 'test saving second 5 content',
                'is_active' => 0,
            )
        ));
        $instance = $form->save();

        $this->assertInstanceOf(
            'Translatable_Model_DbTable_Row_FirstModel',
            $instance,
            'Error, the saving failed and not return a row instance when we save a new localized row'
        );

        //Check if the result is good
        $this->_switchLocale('en');
        $_resultRow = $form->getModel()->select(true)->filter(array('id' => 3))->fetchRow();

        $this->assertNotNull(
            $_resultRow,
            'Error, the new saved row was not found when we save a new localized row'
        );

        $_exceptedResult = array(
            'id'            => 3,
            'original_id'   => 3,
            'language_id'   => 2,
            'title'         => 'test saving localized row 1',
            'content'       => 'content 3 FR',
            'second1_id'    => $instance->second1_id,
            'second2_id'    => 7,
            'is_active'     => 1,
            'translation_localized_test_m_translation_first_model_title'        => 'test saving localized row 1',
            'translation_localized_test_m_translation_first_model_content'      => 'content 3 FR',
            'translation_localized_test_m_translation_first_model_second1_id'   => $instance->second1_id,
            'translation_localized_test_m_translation_first_model_id'           => $instance->id,
            'translation_localized_test_m_translation_first_model_original_id'  => 3,
            'translation_localized_test_m_translation_first_model_language_id'  => 2
        );

        ksort($_exceptedResult);
        $_rawResultRow = $_resultRow->toArray();
        ksort($_rawResultRow);

        $this->assertEquals(
            $_exceptedResult,
            $_rawResultRow,
            'Error, data of the row is not the excepted data when we save a new localized row'
        );

        //Check if the original row is intact
        $this->_switchLocale('fr');
        $_resultOriginalRow = $form->getModel()->select(true)->filter(array('id' => 3))->fetchRow();

        $this->assertNotNull(
            $_resultOriginalRow,
            'Error, the original row was not found after the creation of a new localized row'
        );

        $_exceptedResult = array(
            'id'            => 3,
            'original_id'   => null,
            'language_id'   => 1,
            'title'         => 'First 3 FR',
            'content'       => 'content 3 FR',
            'second1_id'    => 6,
            'second2_id'    => 7,
            'is_active'     => 1,
            'translation_localized_test_m_translation_first_model_title'        => null,
            'translation_localized_test_m_translation_first_model_content'      => null,
            'translation_localized_test_m_translation_first_model_second1_id'   => null,
            'translation_localized_test_m_translation_first_model_id'           => null,
            'translation_localized_test_m_translation_first_model_original_id'  => null,
            'translation_localized_test_m_translation_first_model_language_id'  => null
        );

        ksort($_exceptedResult);
        $_rawResultRow = $_resultOriginalRow->toArray();
        ksort($_rawResultRow);

        $this->assertEquals(
            $_exceptedResult,
            $_rawResultRow,
            'Error, data of the original row is not the excepted data when we save a new localized row'
        );

        //Check if the original subinstance is intact
        $this->assertSame(
            'Second 6 - FR',
            $_resultOriginalRow->second1->title,
            'Error, the original subinstance was updated during the saving of localized instance'
        );

        //but the localized row is updated
        $this->assertSame(
            'test saving second 4 title',
            $_resultRow->second1->title,
            'Error, the localized subinstance was not updated during the saving of localized instance'
        );
    }

    /**
     * Check when we update a localized row with translatable subform,
     * if this subform was updated, we save a new subinstance without delete the previous subinstance
     * to prevent deletion if this subinstance is used in original rows
     */
    public function testSavingExistantLocalizedRowWithSubForm(){
        $this->_switchLocale('fr');
        $form = $this->_getForm('Translatable_Form_Model_FirstModel', 4);
        Translatable_Model_DbTable_Row_FirstModel::cleanLocalReferenceCache();

        //Value for a new instnace
        $_dataSet = array(
            'title'     => 'test saving localized 5 title',
            'second1'   => array(
                'title' => 'test saving second 7 title',
            )
        );

        //Update and save the instance
        $form->isValidPartial($_dataSet);
        $instance = $form->save();

        $this->assertInstanceOf(
            'Translatable_Model_DbTable_Row_FirstModel',
            $instance,
            'Error, the saving failed and not return a row instance when we save a new original row'
        );

        //Check if the result is good
        $this->_switchLocale('en');
        $_resultRow = $form->getModel()->select(true)->filter(array('id' => 1))->fetchRow();

        $this->assertNotNull(
            $_resultRow,
            'Error, the new saved row was not found when we save a new localized row'
        );

        $_exceptedResult = array(
            'id'            => 1,
            'original_id'   => 1,
            'language_id'   => 2,
            'title'         => 'test saving localized 5 title',
            'content'       => 'content FR',
            'second1_id'    => $instance->second1_id,
            'second2_id'    => 2,
            'is_active'     => 1,
            'translation_localized_test_m_translation_first_model_title'        => 'test saving localized 5 title',
            'translation_localized_test_m_translation_first_model_content'      => '',
            'translation_localized_test_m_translation_first_model_second1_id'   => $instance->second1_id,
            'translation_localized_test_m_translation_first_model_id'           => $instance->id,
            'translation_localized_test_m_translation_first_model_original_id'  => 1,
            'translation_localized_test_m_translation_first_model_language_id'  => 2
        );

        ksort($_exceptedResult);
        $_rawResultRow = $_resultRow->toArray();
        ksort($_rawResultRow);

        $this->assertEquals(
            $_exceptedResult,
            $_rawResultRow,
            'Error, data of the row is not the excepted data when we save a new localized row'
        );

        //Check if the original row is intact
        $this->_switchLocale('fr');
        $_resultOriginalRow = $form->getModel()->select(true)->filter(array('id' => 1))->fetchRow();

        $this->assertNotNull(
            $_resultOriginalRow,
            'Error, the original row was not found after the creation of a new localized row'
        );

        $_exceptedResult = array(
            'id'            => 1,
            'original_id'   => null,
            'language_id'   => 1,
            'title'         => 'First 1 FR',
            'content'       => 'content FR',
            'second1_id'    => 1,
            'second2_id'    => 2,
            'is_active'     => 1,
            'translation_localized_test_m_translation_first_model_title'        => null,
            'translation_localized_test_m_translation_first_model_content'      => null,
            'translation_localized_test_m_translation_first_model_second1_id'   => null,
            'translation_localized_test_m_translation_first_model_id'           => null,
            'translation_localized_test_m_translation_first_model_original_id'  => null,
            'translation_localized_test_m_translation_first_model_language_id'  => null
        );

        ksort($_exceptedResult);
        $_rawResultRow = $_resultOriginalRow->toArray();
        ksort($_rawResultRow);

        $this->assertEquals(
            $_exceptedResult,
            $_rawResultRow,
            'Error, data of the row is not the excepted data when we save a new localized row'
        );

        //Check if the original subinstance is intact
        $this->assertSame(
            'Second 1 - FR',
            $_resultOriginalRow->second1->title,
            'Error, the subinstance was updated during the saving of localized instance'
        );

        //but the localized row is updated
        $this->assertSame(
            'test saving second 7 title',
            $_resultRow->second1->title,
            'Error, the localized subinstance was not updated during the saving of localized instance'
        );
    }
}