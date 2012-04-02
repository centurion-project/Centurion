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
        $this->getConnection()->getConnection()->query('SET FOREIGN_KEY_CHECKS=0;');
        return $this->createXMLDataSet(
            dirname(__FILE__) . '/../_dataSet/RowTest.xml'
        );
    }

    /**
     * Method to change of locale in follow tests to test the translation mechanism
     * @param string $locale
     */
    protected function _switchLocale($locale){
        //Switch to required language
        Zend_Registry::get('Zend_Translate')->setLocale($locale);
        Zend_Locale::setDefault($locale);
        Zend_Registry::set('Zend_Locale', $locale);
        Centurion_Config_Manager::set(Translation_Traits_Common::DEFAULT_LOCALE_KEY, $locale);
    }
}