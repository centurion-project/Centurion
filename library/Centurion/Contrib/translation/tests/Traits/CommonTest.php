<?php

require_once dirname(__FILE__) . '/../../../../../../tests/TestHelper.php';

/**
 * @class Translation_Test_Traits_CommonTest
 * @package Tests
 * @subpackage Translation
 * @author Richard Déloge, rd@octaveoctave.com
 *
 * Check the behavior of the class Translation_Test_Traits_CommonTest and of it is method getDefaultLanguage()
 */
class Translation_Test_Traits_CommonTest extends Translation_Test_Traits_Common_Abstract{

    /**
     * To initialize the DB of test with a db whom contains only two languages FR and EN
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet(){
        return $this->createXMLDataSet(
            dirname(__FILE__) . '/_dataSet/CommonTest.xml'
        );
    }

    /**
     * Checks if the DB test is good (else, next tests must throw false-positive)
     */
    public function testDbTestIsGood(){
        $languagesRowSet = Centurion_Db::getSingleton('translation/language')
                                                    ->fetchAll();

        $this->assertInstanceOf(    'Centurion_Db_Table_Rowset_Abstract',
                                    $languagesRowSet,
                                    'The model must return an array');

        $this->assertInstanceOf(    'Translation_Model_DbTable_Language',
                                    $languagesRowSet->getTable(),
                                    'The rowset is not a language rowset of module translation');

        $this->assertEquals(    2,
                                $languagesRowSet->count(),
                                'Error, the DB of test must contain only langage FR and EN');

        $frFound = false;
        $enFound = false;
        foreach($languagesRowSet as $languageRow){
            if('fr' == $languageRow->locale){
                $frFound = true;
            }
            elseif('en' == $languageRow->locale){
                $enFound = true;
            }
        }

        $this->assertTrue($frFound, 'Error, the language FR was not found in the DB of test');
        $this->assertTrue($enFound, 'Error, the language EN was not found in the DB of test');
    }

    /**
     * Test the normal behavior of the method : Translation_Traits_Common::getDefaultLanguage()
     *      - Return the language row defined in as default in the configuration     *
     */
    public function testGetDefaultLanguageNormalBehavior(){
        $_expectedLocale = 'fr';
        //Update manually the configuration to be sure of the expected default language
        //is an exesting language in db of test
        Centurion_Config_Manager::set(Translation_Traits_Common::DEFAULT_LOCALE_KEY, $_expectedLocale);

        //Execute the method
        $_defaultLanguageRow = Translation_Traits_Common::getDefaultLanguage();

        //Check if the method returns a language row
        $this->assertInstanceOf(    'Translation_Model_DbTable_Row_Language',
                                    $_defaultLanguageRow,
                                    'Error, getDefaultLanguage() must return a row whom implements the language'
                                );

        //Check if the method returns the good row
        $this->assertSame(  $_expectedLocale,
                            $_defaultLanguageRow->locale,
                            'Error, the locale returned is not the excepted language'
                        );

    }

    /**
     * Test the behavior of Translation_Traits_Common::getDefaultLanguage()
     *  when the language set in the configuration is wrong, the method must return null
     */
    public function testGetDefaultLanguageBehaviorForInvalidDefaultLocale(){
        $_expectedLocale = 'es';
        //Define a non existant language in the DB
        Centurion_Config_Manager::set(Translation_Traits_Common::DEFAULT_LOCALE_KEY, $_expectedLocale);
        //Execute the fonction
        $this->assertNull(  Translation_Traits_Common::getDefaultLanguage(),
                            'Error, the method must return null when the default language is a bad (is an integer)'
            );
    }

    /**
     * Test the behavior of the method when Translation_Traits_Common::getDefaultLanguage()
     *  when the set language is an object
     */
    public function testGetDefaultLanguageBehaviorForInvalidObjectDefaultLocale(){
        try{
            $_expectedLocale = new stdClass();
            Centurion_Config_Manager::set(Translation_Traits_Common::DEFAULT_LOCALE_KEY, $_expectedLocale);
            $_defaultLanguageRow = Translation_Traits_Common::getDefaultLanguage();
        }
        catch(Exception $e){
            return;
        }

        $this->fail('Error, the method did not throw an exception when the default language is a bad (is an object)');
    }

    /**
     * Test the behavior of the method Translation_Traits_Common::getDefaultLanguage()
     *  when there are no language defined in the configuration
     */
    public function testGetDefaultLanguageBehaviorForNonDefaultLocale(){
        try{
            Centurion_Config_Manager::set(Translation_Traits_Common::DEFAULT_LOCALE_KEY, null);
            $_defaultLanguageRow = Translation_Traits_Common::getDefaultLanguage();
        }
        catch(Exception $e){
            return;
        }

        $this->fail('Error, the method did not throw an exception when the default language is a bad (is null)');
    }

    /**
     * Test the behavior of the method Translation_Traits_Common::getDefaultLanguage()
     *  when there are no language defined in the configuration
     */
    public function testGetDefaultLanguageBehaviorForNonConfiguration(){
        try{
            Centurion_Config_Manager::clear();
            $_defaultLanguageRow = Translation_Traits_Common::getDefaultLanguage();
        }
        catch(Exception $e){
            return;
        }

        $this->fail('Error, the method did not throw an exception when there are no configuration');
    }

}