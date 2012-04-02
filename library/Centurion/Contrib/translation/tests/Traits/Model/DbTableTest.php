<?php

require_once dirname(__FILE__) . '/../../../../../../../tests/TestHelper.php';

/**
 * @class Translation_Test_Traits_Model_DbTableTest
 * @package Tests
 * @subpackage Translation
 * @author Richard Déloge, rd@octaveoctave.com
 *
 * To check the trait Translation_Traits_Model_DbTable to support translations on a model.
 *      - Check if the trait initialize the model with good value
 *      - Check methods provided by this trait :
 *          - ifNotExistsGetDefault() : if the trait must return the original row when the localized row was not found
 *          - isOriginalForcedToDefaultLanguage() : if the original row is forced to get the default language or not
 *          - getLocalizedColsPrefix() : to get the prefix of localized column in sql request and fow fetched
 */
class Translation_Test_Traits_Model_DbTableTest
        extends Translation_Test_Traits_Common_Abstract{

    /**
     * To initialize the DB of test with a db whom contains only two languages FR and EN
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet(){
        return $this->createXMLDataSet(
            dirname(__FILE__) . '/_dataSet/DbTableTest.xml'
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
     * Check if the DbModel trait's initialization is good and add references map used in this trait
     */
    public function testMethodModelInitialization(){
        //Get a new model...
        $model = new Translatable_Model_DbTable_FirstModel();

        //And check if the trait has added new reference map in this model
        $language = $model->getReferenceMap('language', false);
        $this->assertNotEmpty(
                $language,
                'Error, the reference map "language" is not automaticaly added during trait initialisation'
            );



        //Check if reference map "langage" is valid :
        //Presence of columns key
        $this->assertArrayHasKey('columns', $language, 'Error, the reference map "language" has not defined local coluùn');
        $this->assertSame(
            Translation_Traits_Model_DbTable::LANGUAGE_FIELD,
            $language['columns'],
            'Error, the reference column of the reference map "language" is not the field defined in '
                                            .Translation_Traits_Model_DbTable::LANGUAGE_FIELD
        );

        //Presence of refColumns key
        $this->assertArrayHasKey('refColumns', $language, 'Error, the reference map "language" has not defined foreign column');
        $this->assertSame(
            'id',
            $language['refColumns'],
            'Error, the foreign column of the reference map "language" is not "id"'
        );

        //Presence of refTableClass
        $this->assertArrayHasKey('refTableClass', $language, 'Error, the reference map "language" has not defined foreign model');
        $this->assertSame(
            'Translation_Model_DbTable_Language',
            $language['refTableClass'],
            'Error, the foreign model of the reference map "language" is not "Translation_Model_DbTable_Language"'
        );


        $original = $model->getReferenceMap('original', false);
        $this->assertNotEmpty(
                $original,
                'Error, the reference map "original" is not automaticaly added during trait initialisation'
            );

        //Check if reference map "original" is valid :
        //Presence of columns key
        $this->assertArrayHasKey('columns', $original, 'Error, the reference map "original" has not defined local coluùn');
        $this->assertSame(
            Translation_Traits_Model_DbTable::ORIGINAL_FIELD,
            $original['columns'],
            'Error, the reference column of the reference map "original" is not the field defined in '
                .Translation_Traits_Model_DbTable::ORIGINAL_FIELD
        );

        //Presence of refColumns key
        $this->assertArrayHasKey('refColumns', $original, 'Error, the reference map "original" has not defined foreign column');
        $this->assertSame(
            'id',
            $original['refColumns'],
            'Error, the foreign column of the reference map "original" is not "id"'
        );

        //Presence of refTableClass
        $this->assertArrayHasKey('refTableClass', $original, 'Error, the reference map "original" has not defined foreign model');
        $this->assertSame(
            get_class($model),
            $original['refTableClass'],
            'Error, the foreign model of the reference map "original" is not the same model ('.get_class($model).')'
        );
    }

    /**
     * Check if the method ifNotExistsGetDefault has the good behavior :
     *      if the model not overload the method ifNotExistsGetDefault, the trait must return the value in
     *                                          Translation_Traits_Common::GET_DEFAULT_CONFIG_KEY
     *
     *      else, must return the value from the model
     */
    public function testMethodIfNotExistsGetDefaultBehavior(){
        $_originalConfigValue = (bool) Centurion_Config_Manager::get(
                                                Translation_Traits_Common::GET_DEFAULT_CONFIG_KEY,
                                                Translation_Traits_Common::NOT_EXISTS_GET_DEFAULT
                                        );

        //Overwrite the configuration manually to be sure of the result
        Centurion_Config_Manager::set(Translation_Traits_Common::GET_DEFAULT_CONFIG_KEY, true);
        $model = new Translatable_Model_DbTable_FirstModel();

        $this->assertTrue(
                $model->ifNotExistsGetDefault(),
                'Error, the trait translation/DbTable not respect the configuration value (must be true)'
            );

        unset($model);

        //Overwrite the configuration manually to be sure of the result
        Centurion_Config_Manager::set(Translation_Traits_Common::GET_DEFAULT_CONFIG_KEY, false);
        $model = new Translatable_Model_DbTable_FirstModel();

        $this->assertFalse(
            $model->ifNotExistsGetDefault(),
            'Error, the trait translation/DbTable not respect the configuration value (must be false)'
        );

        //Restore original value
        Centurion_Config_Manager::set(Translation_Traits_Common::GET_DEFAULT_CONFIG_KEY, $_originalConfigValue);

        //Check with a model whom overload the method ifNotExistsGetDefault
        unset($model);
        $model = new Translatable_Model_DbTable_ThirdModel();

        $this->assertTrue(
            $model->ifNotExistsGetDefault(),
            'Error, the trait translation/DbTable not respect the configuration value (must be false)'
        );

        unset($model);
        $model = new Translatable_Model_DbTable_FourthModel();

        $this->assertFalse(
            $model->ifNotExistsGetDefault(),
            'Error, the trait translation/DbTable not respect the configuration value (must be true)'
        );
    }

    /**
     * Check the behavior of ifNotExistsGetDefault when the value in the configuration is not seted.
     * (Must return the default value : false)
     */
    public function testMethodIfNotExistsGetDefaultBehaviorForInvalid(){
        $_originalConfigValue = (bool) Centurion_Config_Manager::get(
            Translation_Traits_Common::GET_DEFAULT_CONFIG_KEY,
            Translation_Traits_Common::NOT_EXISTS_GET_DEFAULT
        );

        //Overwrite the configuration manually to be sure of the result
        Centurion_Config_Manager::set(Translation_Traits_Common::GET_DEFAULT_CONFIG_KEY, null);
        $model = new Translatable_Model_DbTable_FirstModel();

        $this->assertFalse(
            $model->ifNotExistsGetDefault(),
            'Error, the trait translation/DbTable not respect the configuration value (must be false)'
        );

        //Restore original value
        Centurion_Config_Manager::set(Translation_Traits_Common::GET_DEFAULT_CONFIG_KEY, $_originalConfigValue);
    }

    /**
     * Check if the method isOriginalForcedToDefaultLanguage return a boolean
     */
    public function testMethodIsOriginalForcedToDefaultLanguageBehavior(){
        $model = new Translatable_Model_DbTable_FirstModel();
        $this->assertInternalType('bool', $model->isOriginalForcedToDefaultLanguage());
    }

    /**
     * Check if the method getLanguageRefRule of the trait
     * THis method must return the reference map for language
     */
    public function testMethodGetLanguageRefRuleBehavior(){
        //Get a new model...
        $model = new Translatable_Model_DbTable_FirstModel();

        //And check if the trait has added new reference map in this model
        $language = $model->getLanguageRefRule();
        $this->assertSame(
                'language',
                $language,
                'Error, the rule of the reference map for translation is not valid (must called "language")'
            );
    }

    /**
     * Check if the getLocalizedColsPrefix() method of the trait return the good prefix according to model :
     *
     *  translation_localized_[table name]
     */
    public function testMethodGetLocalizedColsPrefixBehavior(){
        //Get a new model...
        $model = new Translatable_Model_DbTable_FirstModel();

        $_goodPrefix = 'translation_localized_'.$model->info(Centurion_Db_Table::NAME).'_';
        $this->assertSame(
            $_goodPrefix,
                $model->getLocalizedColsPrefix(),
                'Error, the prefix for localized cols is not valid, it must be "'.$_goodPrefix.'"'
            );

    }
}