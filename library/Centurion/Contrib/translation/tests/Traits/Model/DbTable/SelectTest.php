<?php
require_once dirname(__FILE__) . '/../../../../../../../../tests/TestHelper.php';

/**
 * @class Translation_Test_Traits_Model_DbTable_SelectTest
 * @package Tests
 * @subpackage Translation
 * @author Richard Déloge, rd@octaveoctave.com
 *
 * To check if the behavior of select request with the trait translation is the excepted behavior :
 *      - Add and prefix localized columns into list of fields to return.
 *      - The prefix must be defined by the model with the method getLocalizedColsPrefix()
 *      - Add a new join to join localized row with the original row
 *      - this join must be "outer" to return also original row is the localization does not exist
 *              (and if the application is configured to do it)
 */
class Translation_Test_Traits_Model_DbTable_SelectTest
        extends Translation_Test_Traits_Model_DbTable_Row_Abstract{

    /**
     * Check if the trait translation not on the select request the relation to selection also the translation
     * of a row even if we are in the original language.
     * (In the absolute, it is possible to translate a french row in drench)
     */
    public function testSelectJoinInDefaultLanguage(){
        $this->_switchLocale('fr');
        $firstModel = Centurion_Db::getSingleton('translatable/first_model');

        //Build an empty request
        $selectObject = $firstModel->select(true);
        $formParts = $selectObject->getPart(Zend_Db_Select::FROM);

        //Check if the trait has added the relation to select transaltions
        $this->assertEquals(
            2,
            count($formParts),
            'The "FromPart" of the select request must contains only two elements : '
                .'the original relation, its translations'
        );

        $tableName = $firstModel->info(Centurion_Db_Table::NAME);
        $childTableName = Translation_Traits_Model_DbTable_Select::CHILD_PREFIX.$tableName;

        //Check if the select has the original relation
        $this->assertArrayHasKey(
            $tableName,
            $formParts,
            'Error the request was not part of "FROM" associated with the current model : '.get_class($firstModel)
        );

        //Check if the select has the translation relation
        $this->assertArrayHasKey(
            $childTableName,
            $formParts,
            'Error the request was not part of "FROM" associated with the current model in "translation mode" : '
                    .get_class($firstModel)
        );

        $originalPart = $formParts[$tableName];
        $localizedPart = $formParts[$childTableName];

        //Check if the original relation is right
        $this->assertSame(
            $tableName,
            $originalPart['tableName'],
            'Error, the table of the original relation is not the original table'
        );

        $this->assertSame(
            Zend_Db_Select::INNER_JOIN,
            $originalPart['joinType'],
            'Error, the table of the original relation is not of good type'
        );

        $this->assertNull(
            $originalPart['joinCondition'],
            'Error, the original relation must not has a condition'
        );

        //Check if the translation relation is right
        $this->assertSame(
            $tableName,
            $localizedPart['tableName'],
            'Error, the table of the localized relation is not the original table'
        );

        $this->assertSame(
            Zend_Db_Select::LEFT_JOIN,
            $localizedPart['joinType'],
            'Error, the table of the localized relation must be an left join '
                .'(to allow DBMS to return original row when the translation not exist for a language)'
        );

        $localizedCondition = $childTableName.'.'.Translation_Traits_Model_DbTable::ORIGINAL_FIELD
            .' = '.$tableName.'.id AND '
            .$childTableName.'.'.Translation_Traits_Model_DbTable::LANGUAGE_FIELD.' = 1';

        $this->assertEquals(
            $localizedCondition,
            (string) $localizedPart['joinCondition'],
            'Error, relation for the localisation join must respect the pattern : '
                .'childTable.original_id = originalTable.id AND childTable.language_id = X'
        );
    }

    /**
     * Check if the trait translation add on the select request the relation to selection also the translation
     * of a row if it is available for a given language
     */
    public function testSelectJoinInAnotherLanguage(){
        $this->_switchLocale('en');
        $firstModel = Centurion_Db::getSingleton('translatable/first_model');

        //Build an empty request
        $selectObject = $firstModel->select(true);
        $formParts = $selectObject->getPart(Zend_Db_Select::FROM);

        //Check if the trait has added the relation to select transaltions
        $this->assertEquals(
            2,
            count($formParts),
            'The "FromPart" of the select request must contains only two elements : '
                .'the original relation, its translations'
        );

        $tableName = $firstModel->info(Centurion_Db_Table::NAME);
        $childTableName = Translation_Traits_Model_DbTable_Select::CHILD_PREFIX.$tableName;

        //Check if the select has the original relation
        $this->assertArrayHasKey(
            $tableName,
            $formParts,
            'Error the request was not part of "FROM" associated with the current model : '.get_class($firstModel)
        );

        //Check if the select has the translation relation
        $this->assertArrayHasKey(
            $childTableName,
            $formParts,
            'Error the request was not part of "FROM" associated with the current model in "translation mode" : '
                        .get_class($firstModel)
        );

        $originalPart = $formParts[$tableName];
        $localizedPart = $formParts[$childTableName];

        //Check if the original relation is right
        $this->assertSame(
            $tableName,
            $originalPart['tableName'],
            'Error, the table of the original relation is not the original table'
        );

        $this->assertSame(
            Zend_Db_Select::INNER_JOIN,
            $originalPart['joinType'],
            'Error, the table of the original relation is not of good type'
        );

        $this->assertNull(
            $originalPart['joinCondition'],
            'Error, the original relation must not has a condition'
        );

        //Check if the translation relation is right
        $this->assertSame(
            $tableName,
            $localizedPart['tableName'],
            'Error, the table of the localized relation is not the original table'
        );

        $this->assertSame(
            Zend_Db_Select::LEFT_JOIN,
            $localizedPart['joinType'],
            'Error, the table of the localized relation must be an left join '
                .'(to allow DBMS to return original row when the translation not exist for a language)'
        );

        $localizedCondition = $childTableName.'.'.Translation_Traits_Model_DbTable::ORIGINAL_FIELD
                                    .' = '.$tableName.'.id AND '
                                    .$childTableName.'.'.Translation_Traits_Model_DbTable::LANGUAGE_FIELD.' = 2';

        $this->assertEquals(
            $localizedCondition,
            (string) $localizedPart['joinCondition'],
            'Error, relation for the localisation join must respect the pattern : '
                .'childTable.original_id = originalTable.id AND childTable.language_id = X'
        );
    }

    /**
     * Check if the trait translation add on the request field to retrieve translated value for localized rows
     * (check if this behavior is also available in the original language)
     */
    public function testSelectTranslatableFieldsInDefaultLanguage(){
        $this->_testSelectTranslatableFields('fr');
    }

    /**
     * Check if the trait translation add on the request field to retrieve translated value for localized rows
     * (check if this behavior is also available in the another language)
     */
    public function testSelectTranslatableFieldsInAnotherLanguage(){
        $this->_testSelectTranslatableFields('en');
    }

    /**
     * To factorize the test code for testSelectTranslatableFieldsInDefaultLanguage and testSelectTranslatableFieldsInAnotherLanguage
     * (behaviors are identicals)
     * @param string $language
     */
    protected  function _testSelectTranslatableFields($language){
        $this->_switchLocale($language);
        $firstModel = Centurion_Db::getSingleton('translatable/first_model');

        //Build an empty request
        $selectObject = $firstModel->select(true);
        $colParts = $selectObject->getPart(Zend_Db_Select::COLUMNS);

        $tableName = $firstModel->info(Centurion_Db_Table::NAME);
        $childTableName = Translation_Traits_Model_DbTable_Select::CHILD_PREFIX.$tableName;

        $this->assertEquals(
            2,
            count($colParts),
            'The select must have two columns parts : "tableName.*" and "childTable.[TranslatableFields]"'
        );

        $this->assertSame(
            array(
                $tableName,
                '*',
                null
            ),
            $colParts[0],
            'Error, the first colPart is not valid, it must return all columns of the original relation'
        );

        //Generate manualy the cols part excepted here and compare it with the cols part built by the trait
        $transaltionSpec = $firstModel->getTranslationSpec();
        $_localizedPrefix = $firstModel->getLocalizedColsPrefix(); //Test in another testsuite
        $fields = array();
        $_translatedFields = array_intersect(
            array_merge(
                $transaltionSpec[Translation_Traits_Model_DbTable::TRANSLATED_FIELDS],
                $firstModel->info(Centurion_Db_Table_Abstract::PRIMARY),
                array(
                    Translation_Traits_Model_DbTable::ORIGINAL_FIELD,
                    Translation_Traits_Model_DbTable::LANGUAGE_FIELD
                )
            ),
            $firstModel->info(Centurion_Db_Table_Abstract::COLS)
        );
        foreach($_translatedFields as $col){
            $fields[] = $childTableName.'.'.$col.' AS '.$_localizedPrefix.$col;
        }

        $this->assertEquals(
            array(
                $childTableName,
                new Zend_Db_Expr(implode(', ', $fields)),
                null
            ),
            $colParts[1],
            'Error, the second colPart is not valid, it must return only translatable columns of the localized relation'
        );
    }

    public function testSelectTranslationSupportInWhereConditions(){
        $this->_switchLocale('fr');
        $firstModel = Centurion_Db::getSingleton('translatable/first_model');

        //Do the request SELECT * FROM first_mode WHERE second1_id = 1 (in FR)
        $selectObject = $firstModel->select(true);
        $resultRowset = $selectObject->filter(array('second1_id' => 1))->fetchAll();

        //THis request must return only one row
        $this->assertEquals(
                1,
                $resultRowset->count(),
                'Error, the result of SELECT * FROM second1_id = 1 in FR must return only one row'
            );

        //... of first_model
        $this->assertInstanceOf(
                get_class($firstModel),
                $resultRowset->getTable(),
                'Error, the rowset must keep the the model linked with the select request'
            );

        //... check if the result is good
        $this->assertEquals(
                1,
                $resultRowset->current()->id,
                'Error, "SELECT * FROM second1_id = 1" must return only the row id=1 of test_m_translation_first_model'
            );


        //Do the request SELECT * FROM first_mode WHERE second1_id = 2 (in FR)
        $selectObject = $firstModel->select(true);
        $resultRowset = $selectObject->filter(array('second1_id' => 2))->fetchAll();

        //THis request must return only one row
        $this->assertEquals(
            1,
            $resultRowset->count(),
            'Error, the result of SELECT * FROM second1_id = 2 in FR must return only one row'
        );

        //... of first_model
        $this->assertInstanceOf(
            get_class($firstModel),
            $resultRowset->getTable(),
            'Error, the rowset must keep the the model linked with the select request'
        );

        //... check if the result is good
        $this->assertEquals(
            2,
            $resultRowset->current()->id,
            'Error, "SELECT * FROM second1_id = 2" must return only the row id=2 of test_m_translation_first_model'
        );

        //Switch the result in another language
        $this->_switchLocale('en');


        //Do the request SELECT * FROM first_mode WHERE second1_id = 2 (in EN)
        $selectObject = $firstModel->select(true);
        $resultRowset = $selectObject->filter(array('second1_id' => 2))->fetchAll();

        //THis request must return only two row
        $this->assertEquals(
            2,
            $resultRowset->count(),
            'Error, the result of SELECT * FROM second1_id = 2 in EN must return only one row'
        );

        //... of first_model
        $this->assertInstanceOf(
            get_class($firstModel),
            $resultRowset->getTable(),
            'Error, the rowset must keep the the model linked with the select request'
        );

        $firstRow = $resultRowset->current();
        $resultRowset->next();
        $secondRow = $resultRowset->current();

        //... check if the result is good
        $this->assertContains(
            $firstRow->id,
            array(1, 6),
            'Error, "SELECT * FROM second1_id = 2" IN en must return only the row id=6 OR id=1 of first_model'
        );
        $this->assertContains(
            $secondRow->id,
            array(1, 6),
            'Error, "SELECT * FROM second1_id = 2" IN en must return only the row id=6 OR id=1 of first_model'
        );


        //Do the request SELECT * FROM first_mode WHERE second1_id = 6 (in EN, but there are no row in EN with this value
        // only a row FR)
        $selectObject = $firstModel->select(true);
        $resultRowset = $selectObject->filter(array('second1_id' => 6))->fetchAll();

        //THis request must return only one row
        $this->assertEquals(
            1,
            $resultRowset->count(),
            'Error, the result of SELECT * FROM second1_id = 6 in EN must return only one row'
        );

        //... of first_model
        $this->assertInstanceOf(
            get_class($firstModel),
            $resultRowset->getTable(),
            'Error, the rowset must keep the the model linked with the select request'
        );

        //... check if the result is good
        $this->assertEquals(
            3,
            $resultRowset->current()->id,
            'Error, "SELECT * FROM second1_id = 6" must return only the row id=6 of test_m_translation_first_model'
        );


    }
}