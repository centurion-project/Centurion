<?php
require_once dirname(__FILE__) . '/../../../../../../../../../tests/TestHelper.php';

/**
 * @class Translation_Test_Traits_Model_DbTable_Row_NonExistantLocalizedTest
 * @package Tests
 * @subpackage Translation
 * @author Richard Déloge, rd@octaveoctave.com
 *
 * To check if a row object with the trait translation
 *      has the same behavior with non-translated rows than with original rows
 */
class Translation_Test_Traits_Model_DbTable_Row_NonExistantLocalizedTest
    extends Translation_Test_Traits_Model_DbTable_Row_Abstract{


    public function setUp()
    {
        parent::setUp();
        $this->_switchLocale('en');
    }
    /**
     * Check if a translatable field returns the good value (aka translated content
     * if there has been translated) for an localized row
     */
    public function testGetTranslatableContentFromNonExistantLocalizedRow(){
        $this->_switchLocale('en');

        //Retrieve the localized row
        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        $localizedRow = $table
            ->select(true)
            ->filter(array('id' => 3))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $localizedRow,
            'Error, the object returned is not a centurion row object when we want retrieve a non-existant localized row'
        );

        //Check if the row return the translated content for this row
        $this->assertSame(
            'First 3 FR',
            $localizedRow->title,
            'Error, the non-existant localized row does not return the original value for a transaltable field'
        );
    }

    /**
     * Check if a non-translatable field returns the good value (aka originals values) for an localized row
     */
    public function testGetNonTranslatableContentFromNonExistantLocalizedRow(){
        $this->_switchLocale('en');

        //Switch to another language
        Zend_Registry::get('Zend_Translate')->setLocale('en');
        Zend_Locale::setDefault('en');
        Zend_Registry::set('Zend_Locale', 'en');

        //Retrieve the localized row
        $table = new Translation_Test_Traits_Model_DbTable_SecondModel();
        $localizedRow = $table
            ->select(true)
            ->filter(array('id' => 3))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $localizedRow,
            'Error, the object returned is not a centurion row object when we want retrieve a non-existant localized row'
        );

        //Check that the localized row return the original content
        $this->assertSame(
            'Second 3 - FR',
            $localizedRow->title,
            'Error, the non-existant localized row does not return the original value for a non-transaltable field'
        );

    }

    /**
     * Check if a set-null field returns the good value (aka the original value) for an original row
     */
    public function testGetSetNullContentFromNonExistantLocalizedRow(){
        $this->_switchLocale('en');

        //Switch to another language
        Zend_Registry::get('Zend_Translate')->setLocale('en');
        Zend_Locale::setDefault('en');
        Zend_Registry::set('Zend_Locale', 'en');

        //$localizedRow the localized row
        $table = new Translation_Test_Traits_Model_DbTable_ThirdModel();
        $localizedRow = $table
            ->select(true)
            ->filter(array('id' => 3))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $localizedRow,
            'Error, the object returned is not a centurion row object when we want retrieve a non-existant localized row'
        );

        //Check that the localized row return the original content
        $this->assertSame(
            'Third 3 FR',
            $localizedRow->title,
            'Error, the non-existant localized row does not return the original value for a set-null field'
        );
    }

    /**
     * Test if the language parent row from the localized row is good and it is according to the original
     */
    public function testGetLanguageParentRowFromNonExistantLocalizedRow(){
        $this->_switchLocale('en');

        //Retrieve the original row
        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        $localizedRow = $table
            ->select(true)
            ->filter(array('id' => 3))
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
            'Error, the language parent row from an non-existant localized row is not an language row'
        );

        $this->assertSame(
            'fr',
            $languageParentRow->locale,
            'Error, the language parent row from an non-existant localized row is not according to'
                .' the language defined for the original row'
        );
    }

    /**
     * Check behavior of an non existant localized row to get a translatable parent row
     */
    public function testMethodGetTranslatableParentRowFromNonExistantLocalizedRow(){
        $this->_switchLocale('en');

        //Retrieve the localized row
        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        $localizedRow = $table
            ->select(true)
            ->filter(array('id' => 3))
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
            'Translation_Test_Traits_Model_DbTable_Row_SecondModel',
            $second1,
            'Error, the translated parent row from an localized row is not valid row '
                .'of type Translation_Test_Traits_Model_DbTable_Row_SecondModel'
        );

        $this->assertSame(
            $localizedRow->second1_id,
            $second1->id,
            'Error, the translated parent row from an localized row is not the referenced row'
        );

        $this->assertEquals(
            6,
            $second1->id,
            'Error, the translated parent row from the localized row is not the expected row (must has the id : 2)'
        );
    }

    /**
     * Check behavior of an non existant localized row to get a non translatable parent row
     */
    public function testMethodGetNonTranslatableParentRowFromNonExistantLocalizedRow(){
        $this->_switchLocale('en');

        //Retrieve the localizerd row
        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        $localizedRow = $table
            ->select(true)
            ->filter(array('id' => 3))
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
            'Translation_Test_Traits_Model_DbTable_Row_SecondModel',
            $second2,
            'Error, the non-translatable parent row from a localized row is not valid row '
                .'of type Translation_Test_Traits_Model_DbTable_Row_SecondModel'
        );

        $this->assertSame(
            $localizedRow->second2_id,
            $second2->id,
            'Error, the non-translatable parent row from a localized row is not the referenced row'
        );

        $this->assertEquals(
            7,
            $second2->id,
            'Error, the non-translatable parent row from a localized row is not the expected row (must has the id : 2)'
        );
    }


    /**
     * Check behavior of an non existant localized row to get a translatable dependent rowset
     */
    public function testMethodGetTranslatedDependentRowFromNonExistantLocalizedRow(){
        $this->_switchLocale('en');
        Centurion_Db_Table_Abstract::setFiltersStatus(true);
        
        //Retrieve the original row
        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        $localizedRow = $table
            ->select(true)
            ->filter(array('id' => 3))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $localizedRow,
            'Error, the object returned is not a centurion row object when we want retrieve an non-existant localized row'
        );

        //Retrieve the dependent rowset
        $fourths = $localizedRow->fourths;

        //Check if the rowset is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Rowset_Abstract',
            $fourths,
            'Error, the translated dependent rowset from the non-existant localized row is not an '
                .'instance of Centurion_Db_Table_Rowset_Abstract'
        );

        $this->assertInstanceOf(
            'Translation_Test_Traits_Model_DbTable_FourthModel',
            $fourths->getTable(),
            'Error, the translated dependent rowset from the non-existant localized row '
                .'was not retrieved from the good model'
        );

        //Check if the dependent rowset contains only excepted value
        $this->assertEquals(
            1,
            $fourths->count(),
            'Error, the translated dependent rowset from the non-existant localized row must contain only two elements'
        );

        $fourths->rewind();
        $firstDependent = $fourths->current();

        $this->assertContains(
            $firstDependent->id,
            array(5),
            'Error, the translated dependent row from the non-existant localized row '
                .'is not the expected row (must has the id : 5)'
        );
    }


    /**
     * Check behavior of an localized row to get a non translatable dependent rowset
     */
    public function testMethodGetNonTranslatableDependentRowFromNonExistantLocalizedRow(){
        $this->_switchLocale('fr');

        //Retrieve the localized row
        $table = new Translation_Test_Traits_Model_DbTable_FirstModel();
        $localizedRow = $table
            ->select(true)
            ->filter(array('id' => 3))
            ->fetchRow();

        //Check the type of the object returned is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Row_Abstract',
            $localizedRow,
            'Error, the object returned is not a centurion row object when we want retrieve an non-existant localized row'
        );

        //Retrieve the dependent rowset
        $thirds = $localizedRow->thirds;

        //Check if the rowset is valid
        $this->assertInstanceOf(
            'Centurion_Db_Table_Rowset_Abstract',
            $thirds,
            'Error, the non-translatable dependent rowset from the non-existant localized row is not an '
                .'instance of Centurion_Db_Table_Rowset_Abstract'
        );

        $this->assertInstanceOf(
            'Translation_Test_Traits_Model_DbTable_ThirdModel',
            $thirds->getTable(),
            'Error, the non-translatable dependent rowset from the non-existant localized row was '
                .'not retrieved from the good model'
        );

        //Check if the dependent rowset contains only excepted value
        $this->assertEquals(
            1,
            $thirds->count(),
            'Error, the non-translatable dependent rowset from the non-existant localized row '
                .'must contain only two elements'
        );

        $thirds->rewind();
        $firstDependent = $thirds->current();

        $this->assertEquals(
            7,
            $firstDependent->id,
            'Error, the non-translatable dependent row from the loclized row is not '
                .'the expected row (must has the id : 6)'
        );
    }
}
