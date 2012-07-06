<?php
require_once dirname(__FILE__) . '/../../../../../../../../../tests/TestHelper.php';

/**
 * @class Translation_Test_Traits_Model_DbTable_Row_OriginalTest
 * @package Tests
 * @subpackage Translation
 * @author Richard Déloge, rd@octaveoctave.com
 *
 * To check if a row object with the trait translation keep the same behavior with original rows
 */
class Translation_Test_Traits_Model_DbTable_Row_OriginalTest
    extends Translation_Test_Traits_Model_DbTable_Row_Abstract{

    public function setUp()
    {
        parent::setUp();
        $this->_switchLocale('en');
    }
    /**
     * Check if the trait Translation_Traits_Model_DbTable_Row has goodly initliazed the row
     */
    public function testRowTranslationReferenceForOriginalRow(){
        //Check the behavior of new relations on a original row
        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        $_newRow = $table->createRow();
        $this->assertNull($_newRow->original, 'Error, the specialget "original" is defined without ref in original_id');

        //The language was not set in the row, the relation must return null
        $this->assertNull($_newRow->language, 'Error, the relation "language" is defined without ref in language_id');
    }

    /**
     * Check if a translatable field returns the good value for an original row
     */
    public function testGetTranslatableContentFromOriginalRow(){
        $this->_switchLocale('fr');

        //Retrieve the original row
        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        $originalRow = $table
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $originalRow,
            'Error, the object returned is not a centurion row object when we want retrieve an original row'
        );

        //Check it return the original value of the original row
        $this->assertSame(
            'First 1 FR',
            $originalRow->title,
            'Error, the original row does not return the orignal value for a translatable field'
        );
    }

    /**
     * Check if a non-translatable field returns the good value for an original row
     */
    public function testGetNonTranslatableContentFromOriginalRow(){
        $this->_switchLocale('fr');

        //Retrieve the original row
        $table = new Translation_Test_Traits_Model_DbTable_SecondModel();
        $originalRow = $table
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $originalRow,
            'Error, the object returned is not a centurion row object when we want retrieve an original row'
        );

        //Check it return the original value of the original row
        $this->assertSame(
            'Second 1 - FR',
            $originalRow->title,
            'Error, the original row does not return the original value for a non translatable field'
        );
    }

    /**
     * Check if a set-null field returns the good value for an original row
     */
    public function testGetSetNullContentFromOriginalRow(){
        $this->_switchLocale('fr');

        //Retrieve the original row
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $originalRow = $table
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $originalRow,
            'Error, the object returned is not a centurion row object when we want retrieve an original row'
        );

        //Check it return the original value of the original row
        $this->assertSame(
            'Third 1 FR',
            $originalRow->title,
            'Error, the original row does not return the original value for a set-null field'
        );
    }

    /**
     * Test if the language parent row from the original row is good
     */
    public function testGetLanguageParentRowFromOriginalRow(){
        $this->_switchLocale('fr');

        //Retrieve the original row
        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        $originalRow = $table
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $originalRow,
            'Error, the object returned is not a centurion row object when we want retrieve an original row'
        );

        $languageParentRow = $originalRow->language;

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Translation_Model_DbTable_Row_Language',
            $languageParentRow,
            'Error, the language parent row from an original row is not an language row'
        );

        $this->assertSame(
            'fr',
            $languageParentRow->locale,
            'Error, the language parent row from an original row is not according to the language defined for this row'
        );
    }

    /**
     * Test if the original row return null when the dev want its original row
     */
    public function testGetOriginalParentRowFromOriginalRow(){
        $this->_switchLocale('fr');

        //Retrieve the original row
        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        $originalRow = $table
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $originalRow,
            'Error, the object returned is not a centurion row object when we want retrieve an original row'
        );

        $this->assertNull(
            $originalRow->original,
            'Error, the original row return a row when the dev call getOriginal() ...'
        );
    }

    /**
     * Check behavior of an original row to get a translatable parent row
     */
    public function testMethodGetTranslatableParentRowFromOriginalRow(){
        $this->_switchLocale('fr');

        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        //Retrieve the original row
        $originalRow = $table
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $originalRow,
            'Error, the object returned is not a centurion row object when we want retrieve an original row'
        );

        //Check if the parent row is good
        $second1 = $originalRow->second1;
        $this->assertInstanceOf(
            'Translation_Test_Traits_Model_DbTable_Row_SecondModel',
            $second1,
            'Error, the translatable parent row from an original row is not valid '
                .'row of type Translation_Test_Traits_Model_DbTable_Row_SecondModel'
        );

        $this->assertSame(
            $originalRow->second1_id,
            $second1->id,
            'Error, the translatable parent row from an original row is not the referenced row'
        );

        $this->assertEquals(
            1,
            $second1->id,
            'Error, the translatable parent row from an original row is not the expected row (must has the id : 1)'
        );
    }

    /**
     * Check behavior of an original row to get a non translatable parent row
     */
    public function testMethodGetNonTranslatableParentRowFromOriginalRow(){
        $this->_switchLocale('fr');

        //Retrieve the original row
        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        $originalRow = $table
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $originalRow,
            'Error, the object returned is not a centurion row object when we want retrieve an original row'
        );

        //Check if the parent row is good
        $second2 = $originalRow->second2;
        $this->assertInstanceOf(
            'Translation_Test_Traits_Model_DbTable_Row_SecondModel',
            $second2,
            'Error, the non-translatable parent row from an original row is not valid row '
                .'of type Translation_Test_Traits_Model_DbTable_Row_SecondModel'
        );

        $this->assertSame(
            $originalRow->second2_id,
            $second2->id,
            'Error, the non-translatable parent row from an original row is not the referenced row'
        );

        $this->assertEquals(
            2,
            $second2->id,
            'Error, the non-translatable parent row from an original row is not the expected row (must has the id : 2)'
        );
    }


    /**
     * Check behavior of an original row to get a translatable dependent rowset
     */
    public function testMethodGetTranslatableDependentRowFromOriginalRow(){
        $this->_switchLocale('fr');

        //Retrieve the original row
        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        $originalRow = $table
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $originalRow,
            'Error, the object returned is not a centurion row object when we want retrieve an original row'
        );

        //Retrieve the dependent rowset
        $fourths = $originalRow->fourths;

        //Check if the rowset is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Rowset_Abstract',
            $fourths,
            'Error, the translatable dependent rowset from the original row is not an instance '
                .'of Centurion_Db_Table_Rowset_Abstract'
        );

        $this->assertInstanceOf(
            'Translation_Test_Traits_Model_DbTable_FourthModel',
            $fourths->getTable(),
            'Error, the translatable dependent rowset from the original row was not retrieved from the good model'
        );

        //Check if the dependent rowset contains only excepted value
        $this->assertEquals(
            2,
            $fourths->count(),
            'Error, the translatable dependent rowset from the original row must contain only two elements'
        );

        $fourths->rewind();
        $firstDependent = $fourths->current();
        $fourths->next();
        $secondDependent = $fourths->current();

        $this->assertContains(
            $firstDependent->id,
            array(1,3),
            'Error, the translatable dependent row from the original row is not '
                .'the expected row (must has the id : 1 or 3)'
        );

        $this->assertContains(
            $secondDependent->id,
            array(1,3),
            'Error, the translatable dependent row from the original row is not '
                .'the expected row (must has the id : 1 or 3)'
        );
    }


    /**
     * Check behavior of an original row to get a non translatable dependent rowset
     */
    public function testMethodGetNonTranslatableDependentRowFromOriginalRow(){
        $this->_switchLocale('fr');

        //Retrieve the original row
        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        $originalRow = $table
            ->select(true)
            ->filter(array('id' => 1))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $originalRow,
            'Error, the object returned is not a centurion row object when we want retrieve an original row'
        );

        //Retrieve the dependent rowset
        $thirds = $originalRow->thirds;

        //Check if the rowset is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Rowset_Abstract',
            $thirds,
            'Error, the non-translatable dependent rowset from the original row is not an instance '
                .'of Centurion_Db_Table_Rowset_Abstract'
        );

        $this->assertInstanceOf(
            'Translation_Test_Traits_Model_DbTable_ThirdModel',
            $thirds->getTable(),
            'Error, the non-translatable dependent rowset from the original row was not retrieved from the good model'
        );

        //Check if the dependent rowset contains only excepted value
        $this->assertEquals(
            2,
            $thirds->count(),
            'Error, the non-translatable dependent rowset from the original row must contain only two elements'
        );

        $thirds->rewind();
        $firstDependent = $thirds->current();
        $thirds->next();
        $secondDependent = $thirds->current();

        $this->assertContains(
            $firstDependent->id,
            array(5,6),
            'Error, the non-translatable dependent row from the original row is not '
                .'the expected row (must has the id : 5 or 6)'
        );

        $this->assertContains(
            $secondDependent->id,
            array(5,6),
            'Error, the non-translatable dependent row from the original row is not '
                .'the expected row (must has the id : 5 or 6)'
        );
    }

    /**
     * Check if the method presave of trait translation on row do nothing on original row during in insert
     *  ( unless populate the field "language_id" if it is nos setted )
     */
    public function testPreSaveBehaviorForInsertWithoutLanguageIdFromOriginalRow(){
        $this->_switchLocale('fr');

        $firstModel = new Translation_Test_Traits_Model_DbTable_FirstModel();

        //To keep the compliance with trait configuration if its changes
        $languageField = Translation_Traits_Model_DbTable::LANGUAGE_FIELD;
        $originalField = Translation_Traits_Model_DbTable::ORIGINAL_FIELD;

        //Create a new row with specific data
        $_newRow = $firstModel->createRow(array(
            'title'     => 'test title original presave 1',
            'content'   => 'test content original presave 1',
            'second1_id'=> 1,
            'second2_id'=> 3,
            'is_active' => 1
        ));

        //save it (Centurion will be call _preSave of the trait translation)
        $_newRowId = $_newRow->save();

        //Disable all filters (and therefore the trait translation to get the raw data to check if its value are good
        Centurion_Db_Table::setFiltersStatus(false);
        $_checkingRow = $firstModel->select(true)->filter(array('id' => $_newRowId))->fetchRow();
        $_checkingRowData = $_checkingRow->toArray();
        Centurion_Db_Table::restoreFiltersStatus();

        //Build excepted data of this operation
        $_exceptedData = array(
            'id'            => $_newRowId,
            $originalField  => null,
            $languageField  => 1,
            'title'         => 'test title original presave 1',
            'content'       => 'test content original presave 1',
            'second1_id'    => 1,
            'second2_id'    => 3,
            'is_active'     => 1,
            'slug'          => null,
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
     * Check if the method presave of trait translation on row do nothing on original row during in insert
     *  ( unless populate the field "language_id" if it is nos setted )
     */
    public function testPreSaveBehaviorForInsertWithLanguageIdFromOriginalRow(){
        $this->_switchLocale('fr');

        $firstModel = new Translation_Test_Traits_Model_DbTable_FirstModel();

        $languageField = Translation_Traits_Model_DbTable::LANGUAGE_FIELD;
        $originalField = Translation_Traits_Model_DbTable::ORIGINAL_FIELD;

        //Create a new row with specific data
        $_newRow = $firstModel->createRow(array(
            $languageField  => 2, //select another language of the current locale
            'title'         => 'test title original presave 2',
            'content'       => 'test content original presave 2',
            'second1_id'    => 1,
            'second2_id'    => 3,
            'is_active'     => 1
        ));

        //save it (Centurion will be call _preSave of the trait translation)
        $_newRowId = $_newRow->save();

        //Disable all filters (and therefore the trait translation to get the raw data to check if its value are good
        Centurion_Db_Table::setFiltersStatus(false);
        $_checkingRow = $firstModel->select(true)->filter(array('id' => $_newRowId))->fetchRow();
        $_checkingRowData = $_checkingRow->toArray();
        Centurion_Db_Table::restoreFiltersStatus();

        //Build excepted data of this operation
        $_exceptedData = array(
            'id'            => $_newRowId,
            $originalField  => null,
            $languageField  => 2, //select another language of the current locale
            'title'         => 'test title original presave 2',
            'content'       => 'test content original presave 2',
            'second1_id'    => 1,
            'second2_id'    => 3,
            'is_active'     => 1,
            'slug'          => null,
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
     * Check if the method presave of trait translation on row do nothing on original row during in insert
     *  ( unless populate the field "language_id" if it is nos setted )
     */
    public function testPreSaveBehaviorForUpdateFromOriginalRow(){
        $this->_switchLocale('fr');

        $firstModel = new Translation_Test_Traits_Model_DbTable_FirstModel();

        //To keep the compliance with trait configuration if its changes
        $languageField = Translation_Traits_Model_DbTable::LANGUAGE_FIELD;
        $originalField = Translation_Traits_Model_DbTable::ORIGINAL_FIELD;

        //Get an existant origina row from DB
        Centurion_Db_Table::setFiltersStatus(false);
        $originalRow = $firstModel->select(true)->filter(array('id' => 3))->fetchRow();
        Centurion_Db_Table::restoreFiltersStatus();

        $originalRow->title = 'test title original update presave 1';
        $originalRow->content = 'test content original update presave 1';
        $originalRow->second1_id = 1;
        $originalRow->second2_id = null;

        //Build excepted data of this operation
        $_exceptedData = array(
            'id'            => $originalRow->id,
            $originalField  => null,
            $languageField  => 1,
            'title'         => 'test title original update presave 1',
            'content'       => 'test content original update presave 1',
            'second1_id'    => 1,
            'second2_id'    => null,
            'is_active'     => 1,
            'slug'          => null
        );

        $originalRow->save();

        //Disable all filters (and therefore the trait translation to get the raw data to check if its value are good
        Centurion_Db_Table::setFiltersStatus(false);
        $_checkingRow = $firstModel->select(true)->filter(array('id' => 3))->fetchRow();
        $_checkingRowData = $_checkingRow->toArray();
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
}
