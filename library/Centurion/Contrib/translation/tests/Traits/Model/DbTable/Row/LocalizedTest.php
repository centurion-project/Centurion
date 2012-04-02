<?php
/**
 * Author : Richard Déloge, rd@octaveoctave.com
 *
 * Test for the class Translation_Traits_Common
 */
require_once dirname(__FILE__) . '/../../../../../../../../../tests/TestHelper.php';

/**
 * @class Translation_Test_Traits_Model_DbTable_Row_LocalizedTest
 * @package Tests
 * @subpackage Translation
 * @author Richard Déloge, rd@octaveoctave.com
 *
 * To check if the behavior or localized row is the excepted behavior :
 *      - return localized value for translatable value
 *      - if the localized value is empty, the row return emptry
 *      - return always original values for non-translatable fields (duplicated and set-null fields)
 *      - this behavior must be applied on referenced row (by the reference map)
 */
class Translation_Test_Traits_Model_DbTable_Row_LocalizedTest
    extends Translation_Test_Traits_Model_DbTable_Row_Abstract{

    /**
     * Check if the trait Translation_Traits_Model_DbTable_Row has goodly initliazed the row
     */
    public function testRowTranslationReferenceForATranslatedRow(){
        //Check the behavior of a customized row
        $_newRow = Centurion_Db::getSingleton('translatable/first_model')->createRow(array(
            Translation_Traits_Model_DbTable::ORIGINAL_FIELD => 1,
            Translation_Traits_Model_DbTable::LANGUAGE_FIELD => 2
        ));

        //Reference fields are now set, row must return parents rows
        $this->assertNotEmpty($_newRow->original, 'Error, the specialget "original" is not defined');
        $this->assertNotEmpty($_newRow->language, 'Error, the relation "language" is not defined');
    }


    /**
     * Check if a translatable field returns the good value (aka translated content
     * if there has been translated) for an localized row
     */
    public function testGetTranslatableContentFromLocalizedRow(){
        $this->_switchLocale('en');

        //Retrieve the localized row
        $localizedRow = Centurion_Db::getSingleton('translatable/first_model')
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $localizedRow,
            'Error, the object returned is not a centurion row object when we want retrieve an localized row'
        );

        //Check if the row return the translated content for this row
        $this->assertSame(
            'First 1 EN',
            $localizedRow->title,
            'Error, the localized row does not return the translated value for a translatable field'
        );

        //Check if the field is not translated, the row return the original row
        $this->assertSame(
            'content FR',
            $localizedRow->content,
            'Error, the localized row does not return the original value for a '.
                'translatable field when it is not translated'
        );

        //Check for a row, a translatable field is set empty (but not null), the row return an empty value and not
        //the original value
        $localizedRow = Centurion_Db::getSingleton('translatable/first_model')
            ->select(true)
            ->filter(array('id' => 2))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $localizedRow,
            'Error, the object returned is not a centurion row object when we want retrieve an localized row'
        );

        $this->assertSame(
            '',
            $localizedRow->content,
            'Error, the localized row does not return the translated empty value '.
                'for a translatable field set to empty value'
        );
    }

    /**
     * Check if a non-translatable field returns the good value (aka originals values) for an localized row
     */
    public function testGetNonTranslatableContentFromLocalizedRow(){
        $this->_switchLocale('en');

        //Switch to another language
        Zend_Registry::get('Zend_Translate')->setLocale('en');
        Zend_Locale::setDefault('en');
        Zend_Registry::set('Zend_Locale', 'en');

        //Retrieve the localized row
        $localizedRow = Centurion_Db::getSingleton('translatable/second_model')
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $localizedRow,
            'Error, the object returned is not a centurion row object when we want retrieve an localized row'
        );

        //Check that the localized row return the original content
        $this->assertSame(
            'Second 1 - FR',
            $localizedRow->title,
            'Error, the localized row does not return the original value for a non-translatable field'
        );

    }

    /**
     * Check if a set-null field returns the good value (aka the original value) for an original row
     */
    public function testGetSetNullContentFromLocalizedRow(){
        $this->_switchLocale('en');

        //Switch to another language
        Zend_Registry::get('Zend_Translate')->setLocale('en');
        Zend_Locale::setDefault('en');
        Zend_Registry::set('Zend_Locale', 'en');

        //$localizedRow the localized row
        $localizedRow = Centurion_Db::getSingleton('translatable/third_model')
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $localizedRow,
            'Error, the object returned is not a centurion row object when we want retrieve an localized row'
        );

        //Check that the localized row return the original content
        $this->assertSame(
            'Third 1 FR',
            $localizedRow->title,
            'Error, the localized row does not return the original value for a set-null field'
        );
    }

    /**
     * Test if the language parent row from the localized row is good and it is according to the localized
     */
    public function testGetLanguageParentRowFromLocalizedRow(){
        $this->_switchLocale('en');

        //Retrieve the original row
        $localizedRow = Centurion_Db::getSingleton('translatable/first_model')
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $localizedRow,
            'Error, the object returned is not a centurion row object when we want retrieve an localized row'
        );

        $languageParentRow = $localizedRow->language;

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Translation_Model_DbTable_Row_Language',
            $languageParentRow,
            'Error, the language parent row from an localized row is not an language row'
        );

        $this->assertSame(
            'en',
            $languageParentRow->locale,
            'Error, the language parent row from an localized row is not according to the language defined for this row'
        );
    }

    /**
     * Test if the original row return null when the dev want its original row
     */
    public function testGetOriginalParentRowFromLocalizedRow(){
        $this->_switchLocale('en');

        //Retrieve the original row
        $localizedRow = Centurion_Db::getSingleton('translatable/first_model')
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $localizedRow,
            'Error, the object returned is not a centurion row object when we want retrieve an localized row'
        );

        $originalRow = $localizedRow->original;
        $this->assertInstanceOf(
            get_class($localizedRow),
            $originalRow,
            'Error, the original row is not the same class of the localized row'
        );

        $this->assertSame(
            $localizedRow->id,
            $originalRow->id,
            'Error, the original row and the localized row share not the same id '
                .'(in DB, ids are different, but, in PHP, this two rows must return the same id'
        );
    }

    /**
     * Check behavior of an localized row to get a translatable parent row
     */
    public function testMethodGetTranslatableParentRowFromLocalizedRow(){
        $this->_switchLocale('en');

        //Retrieve the localized row
        $localizedRow = Centurion_Db::getSingleton('translatable/first_model')
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $localizedRow,
            'Error, the object returned is not a centurion row object when we want retrieve a localized row'
        );

        //Check if the parent row is good
        $second1 = $localizedRow->second1;
        $this->assertInstanceOf(
            'Translatable_Model_DbTable_Row_SecondModel',
            $second1,
            'Error, the translated parent row from an localized row is not valid row '
                .'of type Translatable_Model_DbTable_Row_SecondModel'
        );

        $this->assertSame(
            $localizedRow->second1_id,
            $second1->id,
            'Error, the translated parent row from an localized row is not the referenced row'
        );

        $this->assertEquals(
            2,
            $second1->id,
            'Error, the translated parent row from the localized row is not the expected row (must has the id : 2)'
        );
    }

    /**
     * Check behavior of an localized row to get a non translatable parent row
     */
    public function testMethodGetNonTranslatableParentRowFromLocalizedRow(){
        $this->_switchLocale('en');

        //Retrieve the localizerd row
        $localizedRow = Centurion_Db::getSingleton('translatable/first_model')
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $localizedRow,
            'Error, the object returned is not a centurion row object when we want retrieve an localized row'
        );

        //Check if the parent row is good
        $second2 = $localizedRow->second2;
        $this->assertInstanceOf(
            'Translatable_Model_DbTable_Row_SecondModel',
            $second2,
            'Error, the non-translatable parent row from a localized row is not valid row '
                .'of type Translatable_Model_DbTable_Row_SecondModel'
        );

        $this->assertSame(
            $localizedRow->second2_id,
            $second2->id,
            'Error, the non-translatable parent row from a localized row is not the referenced row'
        );

        $this->assertEquals(
            2,
            $second2->id,
            'Error, the non-translatable parent row from a localized row is not the expected row (must has the id : 2)'
        );
    }


    /**
     * Check if the method presave clean data before saving when dev update a localized row for non translatable fields
     */
    public function testPreSaveBehaviorForInsertWithDuplicationFromLocalizedRow(){
        $this->_switchLocale('fr');

        $secondModel = Centurion_Db::getSingleton('translatable/second_model');

        //To keep the compliance with trait configuration if its changes
        $languageField = Translation_Traits_Model_DbTable::LANGUAGE_FIELD;
        $originalField = Translation_Traits_Model_DbTable::ORIGINAL_FIELD;

        //Create a new row with specific data
        $_newRow = $secondModel->createRow(array(
            $originalField  => 2,
            $languageField  => 2,
            'title'     => 'test title localized presave 1',
            'content'   => 'test content localized presave 1',
            'is_active' => 0
        ));

        //save it (Centurion will be call _preSave of the trait translation)
        $_newRowId = $_newRow->save();

        //Disable all filters (and therefore the trait translation to get the raw data to check if its value are good
        Centurion_Db_Table::setFiltersStatus(false);
        $_checkingRow = $secondModel->select(true)->filter(array('id' => $_newRowId))->fetchRow();
        $_checkingRowData = $_checkingRow->toArray();
        Centurion_Db_Table::restoreFiltersStatus();

        //Build excepted data of this operation
        $_exceptedData = array(
            'id'            => $_newRowId,
            $originalField  => 2,
            $languageField  => 2,
            'title'         => 'Second 2 - FR', //this field is not translatable (it is a duplicated field)
            'content'       => 'test content localized presave 1',
            'is_active'     => 0
        );

        //sort them (because PHPUnit considere as not equals two array in differents orders
        ksort($_checkingRowData);
        ksort($_exceptedData);

        $this->assertEquals(
            $_exceptedData,
            $_checkingRowData,
            'Error, the row not contains excepted data'
        );
    }

    /**
     * Check if the method presave clean data before saving when dev insert a new localized row for set-null fields
     */
    public function testPreSaveBehaviorForInsertWithSetNullFromLocalizedRow(){
        $this->_switchLocale('fr');

        $thirdModel = Centurion_Db::getSingleton('translatable/third_model');

        //To keep the compliance with trait configuration if its changes
        $languageField = Translation_Traits_Model_DbTable::LANGUAGE_FIELD;
        $originalField = Translation_Traits_Model_DbTable::ORIGINAL_FIELD;

        //Create a new row with specific data
        $_newRow = $thirdModel->createRow(array(
            $originalField  => 2,
            $languageField  => 2,
            'title'     => 'test title localized presave 2',
            'content'   => 'test content localized presave 2',
            'first_id'=> 2,
            'is_active' => 0
        ));

        //save it (Centurion will be call _preSave of the trait translation)
        $_newRowId = $_newRow->save();

        //Disable all filters (and therefore the trait translation to get the raw data to check if its value are good
        Centurion_Db_Table::setFiltersStatus(false);
        $_checkingStmt = $thirdModel->select(true)->filter(array('id' => $_newRowId))->query(Zend_Db::FETCH_ASSOC);
        $_checkingRowData = $_checkingStmt->fetch(); //not pass by the model to not change the fetch mode and return
                                                     // directly an array
                                                     //Because the trait overload all field in set-null with the original value
        Centurion_Db_Table::restoreFiltersStatus();

        //Build excepted data of this operation
        $_exceptedData = array(
            'id'            => $_newRowId,
            $originalField  => 2,
            $languageField  => 2,
            'title'         => null, //this field is not translatable (it is a 'set null' field)
            'content'       => 'Content FR',  //this field is not translatable (it is a duplicated field)
            'first_id'      => '2',
            'is_active'     => 0
        );

        //sort them (because PHPUnit considere as not equals two array in differents orders
        ksort($_checkingRowData);
        ksort($_exceptedData);

        $this->assertEquals(
            $_exceptedData,
            $_checkingRowData,
            'Error, the row not contains excepted data'
        );
    }

    /**
     * Check if the method presave clean data before saving when dev update a localized row
     */
    public function testPreSaveBehaviorForUpdateFromlocalizedRow(){
        $this->_switchLocale('fr');

        $thirdModel = Centurion_Db::getSingleton('translatable/third_model');

        //To keep the compliance with trait configuration if its changes
        $languageField = Translation_Traits_Model_DbTable::LANGUAGE_FIELD;
        $originalField = Translation_Traits_Model_DbTable::ORIGINAL_FIELD;

        //Get an existant origina row from DB
        Centurion_Db_Table::setFiltersStatus(false);
        $localizedRow = $thirdModel->select(true)->filter(array('id' => 4))->fetchRow();
        Centurion_Db_Table::restoreFiltersStatus();

        $localizedRow->title = 'test title localized update presave 1';
        $localizedRow->content = 'test content localized update presave 1';
        $localizedRow->first_id = 5;

        //Build excepted data of this operation
        $_exceptedData = array(
            'id'            => $localizedRow->id,
            $originalField  => 1,
            $languageField  => 2,
            'title'         => null,
            'content'       => 'Content FR',
            'first_id'      => 5,
            'is_active'     => 0
        );

        $localizedRow->save();

        //Disable all filters (and therefore the trait translation to get the raw data to check if its value are good
        Centurion_Db_Table::setFiltersStatus(false);
        $_checkingStmt = $thirdModel->select(true)->filter(array('id' => 4))->query(Zend_Db::FETCH_ASSOC);
        $_checkingRowData = $_checkingStmt->fetch(); //not pass by the model to not change the fetch mode and return
                                                    // directly an array
                                                    //Because the trait overload all field in set-null with the original value
        Centurion_Db_Table::restoreFiltersStatus();

        //sort them (because PHPUnit considere as not equals two array in differents orders
        ksort($_checkingRowData);
        ksort($_exceptedData);

        $this->assertEquals(
            $_exceptedData,
            $_checkingRowData,
            'Error, the row not contains excepted data'
        );
    }

    /**
     * Check the behavior of the trait translation when the original row is updated, the trait must
     * update non-translatable localized rows
     */
    public function testUpdatingLocalizedRowOnUpdateOfOriginalRow(){
        $this->_switchLocale('fr');

        $secondModel = Centurion_Db::getSingleton('translatable/second_model');

        //Disable all filters (and therefore the trait translation to get the raw data to check if its value are good)
        Centurion_Db_Table::setFiltersStatus(false);
        $localizedRow = $secondModel->select(true)->filter(array('id' => 4))->fetchRow();
        Centurion_Db_Table::restoreFiltersStatus();

        $this->assertSame(
            'Second 1 - EN',
            $localizedRow->title,
            'Error, the localized row has not a good value for the non translatable field "title"'
        );

        $this->assertSame(
            'Content EN',
            $localizedRow->content,
            'Error, the localized row has not a good value for the translatable field "content"'
        );

        Centurion_Db_Table::setFiltersStatus(false);
        $originalRow = $secondModel->select(true)->filter(array('id' => 1))->fetchRow();
        Centurion_Db_Table::restoreFiltersStatus();
        $originalRow->title = 'New Second 1 - FR';
        $originalRow->content = 'New Content 1 - FR';
        $originalRow->save();

        //Disable all filters (and therefore the trait translation to get the raw data to check if its value are good)
        Centurion_Db_Table::setFiltersStatus(false);
        $localizedRow = $secondModel->select(true)->filter(array('id' => 4))->fetchRow();
        Centurion_Db_Table::restoreFiltersStatus();
        $this->assertSame(
            'New Second 1 - FR',
            $localizedRow->title,
            'Error, the localized row was not updated by the original row when non-translatable field was been updated'
        );

        $this->assertSame(
            'Content EN',
            $localizedRow->content,
            'Error, the localized row was updated by the original row when translatable field was been updated'
        );
    }
}