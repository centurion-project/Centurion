<?php
/**
 * @class Translation_Test_Traits_Model_DbTable_Row_Abstract
 * @package Tests
 * @subpackage Translation
 * @author Richard Déloge, rd@octaveoctave.com
 *
 * To factorize class of test in trait translation to use the same data set of test and allow these tests to change of
 * locale
 */
abstract class Translation_Test_Traits_Model_DbTable_Row_Abstract
        extends Translation_Test_Traits_Common_Abstract{

    /**
     * To initialize the DB of test with a db whom contains only two languages FR and EN
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet(){
        return $this->createXMLDataSet(
            dirname(__FILE__) . '/../_dataSet/RowTest.xml'
        );
    }
}